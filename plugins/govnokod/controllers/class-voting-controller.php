<?php

class VotingController {

    public function __construct() {
        global $wpdb;
        $wpdb->gk_posts_votes = $wpdb->prefix . 'gk_posts_votes';
        $wpdb->gk_comments_votes = $wpdb->prefix . 'gk_comments_votes';
    }

    public function initTables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        if (!$wpdb->get_row("SHOW TABLES FROM `" . DB_NAME . "` LIKE '$wpdb->gk_posts_votes'")) {

            dbDelta("CREATE TABLE $wpdb->gk_posts_votes (
              ID bigint(20) NOT NULL AUTO_INCREMENT,
              post_id bigint(20) NOT NULL,
              user_id bigint(20) NOT NULL,
              vote tinyint(1) NOT NULL,
              PRIMARY KEY  (ID) 
            ) $charset_collate;");

            $wpdb->query("CREATE INDEX post_id ON $wpdb->gk_posts_votes (post_id);");
        }

        if (!$wpdb->get_row("SHOW TABLES FROM `" . DB_NAME . "` LIKE '$wpdb->gk_comments_votes'")) {
            dbDelta("CREATE TABLE $wpdb->gk_comments_votes (
              ID bigint(20) NOT NULL AUTO_INCREMENT,
              post_id bigint(20) NOT NULL,
              user_id bigint(20) NOT NULL,
              vote tinyint(1) NOT NULL,
              PRIMARY KEY  (ID) 
            ) $charset_collate;");

            $wpdb->query("CREATE INDEX post_id ON $wpdb->gk_comments_votes (post_id);");
        }
    }

    private function _vote($post_id, $user_id, $type, $vote) {
        global $wpdb;
        $table_name = $wpdb->{'gk_' . $type . 's_votes'};
        $current_vote = $wpdb->get_var($wpdb->prepare("SELECT vote FROM $table_name WHERE post_id=%s AND user_id=%s", $post_id, $user_id));
        $current_vote = $current_vote === null ? null : (int) $current_vote;

        // если голос не поменялся, то ничего делать не нужно
        if ($current_vote === $vote) {
            return;
        }

        $on = (int) call_user_func("get_{$type}_meta", $post_id, 'gk_rating_on', true);
        $against = (int) call_user_func("get_{$type}_meta", $post_id, 'gk_rating_against', true);

        // отменяем предыдущее голосование, если оно было
        if ($current_vote === 1) {
            $on--;
        } else if ($current_vote === 0) {
            $against--;
        }

        // голосуем
        if ($vote === 1) {
            $on++;
        } else if ($vote === 0) {
            $against++;
        }

        call_user_func("update_{$type}_meta", $post_id, 'gk_rating_on', $on);
        call_user_func("update_{$type}_meta", $post_id, 'gk_rating_against', $against);

        if ($current_vote === null) {
            $wpdb->query($wpdb->prepare("INSERT INTO $table_name (post_id, user_id, vote) VALUES (%s, %s, %s)", $post_id, $user_id, $vote));
        } else {
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET vote=%s WHERE post_id=%s AND user_id=%s", $vote, $post_id, $user_id));
        }
    }

    public function voteCommentUp($comment_id, $user_id) {
        $this->_vote($comment_id, $user_id, 'comment', 1);
    }

    public function voteCommentDown($comment_id, $user_id) {
        $this->_vote($comment_id, $user_id, 'comment', 0);
    }

    public function votePostUp($post_id, $user_id) {
        $this->_vote($post_id, $user_id, 'post', 1);
    }

    public function votePostDown($post_id, $user_id) {
        $this->_vote($post_id, $user_id, 'post', 0);
    }

    private $_commentsVotesCache = array();
    private $_postsVotesCache = array();

    public function _updateVoteCache($type, $user_id, $post_ids) {
        global $wpdb;
        $cache_array = &$this->{"_{$type}sVotesCache"};

        if (!isset($cache_array[$user_id])) {
            $cache_array[$user_id] = array();
        }
        $post_ids = array_filter($post_ids, function ($post_id) use(&$cache_array, $user_id) { return !isset($cache_array[$user_id][$post_id]); });
        if (empty($post_ids)) {
            return;
        }
        foreach ($post_ids as $post_id) {
            $cache_array[$user_id][$post_id] = false;
        }

        $post_id_in = implode(', ', array_map('intval', $post_ids));
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->{"gk_{$type}s_votes"} . " WHERE user_id=%s AND post_id IN ($post_id_in)", $user_id));

        foreach ($results as $row) {
            $cache_array[$user_id][$row->post_id] = $row;
        }
    }

    /**
     * Вызывает прогрев кеша данных из gk_comments_votes
     *
     * @param WP_Comment[] $comments_array
     *
     * @return WP_Comment[]
     */
    public function commentsArrayListener($comments_array) {
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return $comments_array;
        }

        $ids = array();
        foreach ($comments_array as $comment) {
            $ids[] = $comment->comment_ID;
        }
        $this->_updateVoteCache('comment', $user->ID, $ids);

        return $comments_array;
    }

    /**
     * Вызывает прогрев кеша данных из gk_posts_votes
     *
     * @param WP_Post[] $posts
     *
     * @return WP_Post[]
     */
    public function postsArrayListener($posts) {
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
        $this->_updateVoteCache('post', $user->ID, $ids);

        return $posts;
    }

    public function getCommentVote($post_id, $user_id) {
        if (!$user_id) {
            return null;
        }
        if (!isset($this->_commentsVotesCache[$user_id][$post_id])) {
            $this->_updateVoteCache('comment', $user_id, array($post_id));
        }
        $result = $this->_commentsVotesCache[$user_id][$post_id];
        return $result ? $result : null;
    }

    public function getPostVote($post_id, $user_id) {
        if (!$user_id) {
            return null;
        }
        if (!isset($this->_postsVotesCache[$user_id][$post_id])) {
            $this->_updateVoteCache('post', $user_id, array($post_id));
        }
        $result = $this->_postsVotesCache[$user_id][$post_id];
        return $result ? $result : null;
    }
}

global $gk_vote;
$gk_vote = new VotingController();

add_action('gk_init_db', array($gk_vote, 'initTables'));
add_filter('comments_array', array($gk_vote, 'commentsArrayListener'));
add_filter('the_posts', array($gk_vote, 'postsArrayListener'));


function gk_get_comment_votes($comment=null) {
    $comment = get_comment($comment);

    $against = (int) get_comment_meta($comment->comment_ID, 'gk_rating_against', true);
    $on = (int) get_comment_meta($comment->comment_ID, 'gk_rating_on', true);

    return array(
        'against' => $against,
        'on' => $on,
        'rating' => $on - $against
    );
}

function gk_get_user_comment_vote($comment=null, $user=null) {
    if (!$user) {
        $user = wp_get_current_user();
    }
    $comment = get_comment($comment);

    global $gk_vote;
    $current_vote = $gk_vote->getCommentVote($comment->comment_ID, $user->ID);

    if ($current_vote === null) {
        return null;
    }
    return $current_vote->vote ? 1 : -1;
}

function gk_get_post_votes($post=null) {
    $post = get_post($post);

    $against = (int) get_post_meta($post->ID, 'gk_rating_against', true);
    $on = (int) get_post_meta($post->ID, 'gk_rating_on', true);

    return array(
        'against' => $against,
        'on' => $on,
        'rating' => $on - $against
    );
}

function gk_get_user_post_vote($post=null, $user=null) {
    if (!$user) {
        $user = wp_get_current_user();
    }
    $post = get_post($post);

    global $gk_vote;
    $current_vote = $gk_vote->getPostVote($post->ID, $user->ID);

    if ($current_vote === null) {
        return null;
    }
    return $current_vote->vote ? 1 : -1;
}