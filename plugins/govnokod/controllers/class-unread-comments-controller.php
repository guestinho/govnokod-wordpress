<?php


class UnreadCommentsController {

    public function __construct() {
        global $wpdb;
        $wpdb->gk_unread_comments = $wpdb->prefix . 'gk_unread_comments';
    }

    public function initTables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        if (!$wpdb->get_row("SHOW TABLES FROM `" . DB_NAME . "` LIKE '$wpdb->gk_unread_comments'")) {

            dbDelta("CREATE TABLE $wpdb->gk_unread_comments (
              ID bigint(20) NOT NULL AUTO_INCREMENT,
              post_id bigint(20) NOT NULL,
              user_id bigint(20) NOT NULL,
              num_read mediumint(9) NOT NULL,
              read_date datetime NOT NULL,
              PRIMARY KEY  (ID) 
            ) $charset_collate;");

            $wpdb->query("CREATE INDEX post_id ON $wpdb->gk_unread_comments (post_id, user_id);");
        }
    }


    private $_latestVisitCache = array();


    private function _updateLatestVisitCache($user_id, $post_ids) {
        global $wpdb;
        if (!isset($this->_latestVisitCache[$user_id])) {
            $this->_latestVisitCache[$user_id] = array();
        }
        $post_ids = array_filter($post_ids, function ($post_id) use($user_id) { return !isset($this->_latestVisitCache[$user_id][$post_id]); });
        if (empty($post_ids)) {
            return;
        }
        foreach ($post_ids as $post_id) {
            $this->_latestVisitCache[$user_id][$post_id] = false;
        }

        $post_id_in = implode(', ', array_map('intval', $post_ids));
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->gk_unread_comments WHERE user_id=%s AND post_id IN ($post_id_in)", $user_id));

        foreach ($results as $row) {
            $this->_latestVisitCache[$user_id][$row->post_id] = $row;
        }
    }

    public function getLatestVisit($user_id, $post_id) {
        if (!isset($this->_latestVisitCache[$user_id][$post_id])) {
            $this->_updateLatestVisitCache($user_id, array($post_id));
        }
        $result = $this->_latestVisitCache[$user_id][$post_id];
        return $result ? $result : null;
    }

    private function _setLatestVisit($data) {
        global $wpdb;
        if ($data->ID) {
            $wpdb->query($wpdb->prepare(
                "UPDATE $wpdb->gk_unread_comments SET num_read=%s, read_date=%s WHERE ID=%s",
                $data->num_read, $data->read_date, $data->ID
            ));
        } else {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $wpdb->gk_unread_comments (post_id, user_id, num_read, read_date) VALUES (%s, %s, %s, %s)",
                $data->post_id, $data->user_id, $data->num_read, $data->read_date
            ));
        }
    }


    /**
     * Вызывает прогрев кеша данных из gk_unread_comments
     *
     * @param WP_Post[] $posts
     *
     * @return WP_Post[]
     */
    public function prepareCacheListener($posts) {
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return $posts;
        }

        $ids = array();
        foreach ($posts as $post) {
            if ($post->post_type === 'post') {
                $ids[] = $post->ID;
            }
        }
        $this->_updateLatestVisitCache($user->ID, $ids);

        return $posts;
    }


    private $_latestVisit;
    private $_do = false;

    /**
     * Если запрашивают comments_template, значит комментарии станут прочитанными
     *
     * @param $x
     *
     * @return mixed
     */
    public function commentsTemplateListener($x) {
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return $x;
        }

        if (($post = get_post())) {
            $this->_latestVisit = $this->getLatestVisit($user->ID, $post->ID);
            $this->_do = true;

            $this->_setLatestVisit((object) array(
                'ID' => empty($this->_latestVisit) ? 0 : $this->_latestVisit->ID,
                'post_id' => $post->ID,
                'user_id' => $user->ID,
                'num_read' => get_comments_number($post),
                'read_date' => current_time('mysql'),
            ));
        }
        return $x;
    }

    /**
     * Добавляет класс для непрочитанного комментария
     *
     * @param string[] $classes
     *
     * @return array
     */
    public function commentClass($classes) {
        if ($this->_do) {
            $comment_time = strtotime(get_comment_date('Y-m-d G:i:s'));
            if (!$this->_latestVisit || strtotime($this->_latestVisit->read_date) <= $comment_time) {
                $classes[] = 'new-comment';
            }
        }

        return $classes;
    }
}

global $gk_unread_comments;
$gk_unread_comments = new UnreadCommentsController();
add_action('gk_init_db', array($gk_unread_comments, 'initTables'));
add_filter('the_posts', array($gk_unread_comments, 'prepareCacheListener'));
add_filter('comments_template', array($gk_unread_comments, 'commentsTemplateListener'));
add_filter('comment_class', array($gk_unread_comments, 'commentClass'));

function gk_get_user_read_comments_number($post=null, $user=null) {
    $post = get_post($post);
    if (!$user) {
        $user = wp_get_current_user();
    }
    global $gk_unread_comments;
    $latestVisit = $gk_unread_comments->getLatestVisit($user->ID, $post->ID);
    if (!$latestVisit) {
        return 0;
    }
    return $latestVisit->num_read;
}