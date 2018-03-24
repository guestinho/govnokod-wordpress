<?php

require_once dirname(__FILE__) . '/class-govnokod-ru-data-source.php';
require_once dirname(__FILE__) . '/class-sync-logger.php';

class SyncPostController {

    /**
     * @var IGovnokodDataSource
     */
    public $source;

    /**
     * @var SyncLogger
     */
    public $logger;

    /**
     * SyncPostController constructor.
     * @param IGovnokodDataSource $source
     */
    public function __construct($source) {
        $this->source = $source;
        $this->logger = new SyncLogger();
    }


    public function syncPost($post_id) {
        $post_id = (int) $post_id;
        $lock_path = __DIR__ . '/lock/gk_sync_' . $post_id . '.lock';

        if (SyncLockHelper::skipIfLocked($lock_path)) {
            $this->logger->log("Attempt to sync locked post $post_id");
            return false;
        }

        $result = $this->_syncPost($post_id);

        unlink($lock_path);
        return $result;
    }

    private function _syncPost($post_id) {
        $gk_post = $this->source->loadPost($post_id);

        if (is_wp_error($gk_post)) {
            if (in_array('code404', $gk_post->get_error_codes())) {
                return true;
            }
            $this->logger->error($gk_post);
            return false;
        }

        if ($gk_post === null) {
            $this->logger->error("gk_post $post_id bad parsed");
            return false;
        }

        $post_name = GK_LEGACY_POST_NAME_PREFIX . $post_id;

        $post = get_page_by_path($post_name, OBJECT, 'post');

        if ($post === null) {
            $new_post_id = wp_insert_post(array(
                'post_name' => $post_name,
                'post_content' => '1',
                'post_status' => 'publish',
            ));
            if (is_wp_error($new_post_id)) {
                $this->logger->error($new_post_id);
                return false;
            }
            $post = get_post($new_post_id);
        }

        global $wp_filter;
        kses_remove_filters();
        $comment_content_filters = $wp_filter['pre_comment_content'];
        $wp_filter['pre_comment_content'] = null;
        $changed = $this->_updatePostIfChanged($gk_post, $post);
        $wp_filter['pre_comment_content'] = $comment_content_filters;
        kses_init();

        if ($changed) {
            $this->logger->log("Sync $post_id complete with changes");
        } else {
            $this->logger->log("Sync $post_id complete");
        }
        return true;
    }

    /**
     * @param GovnokodRuPostModel $gk_post
     * @param WP_Post $post
     *
     * @return bool If post or comments was changed
     */
    private function _updatePostIfChanged($gk_post, $post) {
        $changed = false;
        $postarr = array(
            'ID' => $post->ID,
            'post_content' => $gk_post->code,
            'post_excerpt' => $gk_post->description,
            'post_author' => '0',
            'post_date' => $gk_post->date_gmt,
            'post_date_gmt' => $gk_post->date_gmt,
            //'post_modified_gmt' => $gk_post->date_gmt,
            'post_status' => 'publish',
        );

        foreach ($postarr as $k => $v) {
            if ($v !== $post->$k) {
                wp_update_post(wp_slash($postarr));
                $changed = true;
                break;
            }
        }

        $lang = gk_get_post_language_slug($post);
        $new_lang = $gk_post->language;
        $lang_term = get_term_by('slug', $new_lang, 'language');
        if (!$lang_term) {
            $new_lang = GK_DEFAULT_LANGUAGE_SLUG;
            $lang_term = get_term_by('slug', $new_lang, 'language');
        }
        if ($gk_post->language !== $lang) {
            $r = wp_set_post_terms($post->ID, array($lang_term->term_id), 'language');
            $changed = true;
        }

        $post_author_url = get_post_meta($post->ID, 'gk_legacy_author_url', true);
        if ($post_author_url !== GK_LEGACY_SITE_USER_URL . $gk_post->author->id) {
            update_post_meta($post->ID, 'gk_legacy_author_url', GK_LEGACY_SITE_USER_URL . $gk_post->author->id);
            $changed = true;
        }

        $post_author_name = get_post_meta($post->ID, 'gk_legacy_author_name', true);
        if ($post_author_name !== $gk_post->author->name) {
            update_post_meta($post->ID, 'gk_legacy_author_name', $gk_post->author->name);
            $changed = true;
        }

        $post_author_avatar = get_post_meta($post->ID, 'gk_legacy_author_avatar', true);
        if ($post_author_avatar !== $gk_post->author->avatar) {
            update_post_meta($post->ID, 'gk_legacy_author_avatar', $gk_post->author->avatar);
            $changed = true;
        }

        $post_url = get_post_meta($post->ID, 'gk_legacy_url', true);
        if ($post_url !== $gk_post->url) {
            update_post_meta($post->ID, 'gk_legacy_url', $gk_post->url);
            $changed = true;
        }

        update_post_meta($post->ID, 'gk_legacy_sync_time', strtotime(current_time('mysql')));

        return $this->_syncComments($gk_post, $post) || $changed;
    }

    /**
     * @param GovnokodRuPostModel $gk_post
     * @param WP_Post $post
     *
     * @return bool
     */
    private function _syncComments($gk_post, $post) {
        $changed = false;

        $exists_comments = get_comments(array(
            'post_id' => $post->ID,
        ));

        $raw_gk_comments = array();
        $this->_rawComments($gk_post, $raw_gk_comments);

        // comment_agent        - comment id on govnokod.ru
        // comment_author       - author name
        // comment_author_email - author gravatar hash
        // comment_author_url   - author url

        $gk_id_to_comment = array();

        foreach ($exists_comments as $comment) {
            $gk_id_to_comment[$comment->comment_agent] = $comment;
        }

        foreach ($raw_gk_comments as $gk_comment) {
            $comment_arr = array(
                'comment_post_ID' => $post->ID . '',
                'comment_author' => $gk_comment->author->name,
                'comment_author_email' => $gk_comment->author->avatar ? $gk_comment->author->avatar . GK_LEGACY_COMMENT_EMAIL_SUFFIX : '',
                'comment_author_url' => GK_LEGACY_SITE_USER_URL . $gk_comment->author->id,
                'comment_content' => $gk_comment->text,
                'comment_type' => '',
                'comment_parent' => (empty($gk_comment->parent_id) ? 0 : $gk_id_to_comment[$gk_comment->parent_id]->comment_ID) . '',
                'user_id' => '0',
                'comment_author_IP' => GK_LEGACY_COMMENT_IP,
                'comment_agent' => $gk_comment->id . '',
                'comment_date' => $gk_comment->date_gmt,
                'comment_approved' => '1',
            );

            if (!isset($gk_id_to_comment[$gk_comment->id])) {
                $new_comment = wp_insert_comment(wp_slash($comment_arr));
                $changed = true;
                if (!is_int($new_comment) || $new_comment === 0) {
                    throw new Exception("Insert comment error");
                }
                $gk_id_to_comment[$gk_comment->id] = get_comment($new_comment);
            } else {
                $comment = $gk_id_to_comment[$gk_comment->id];
                $comment_arr['comment_ID'] = $comment->comment_ID;

                foreach ($comment_arr as $k => $v) {
                    if ($v !== $comment->$k) {
                        $r = wp_update_comment(wp_slash($comment_arr));
                        $changed = true;
                        break;
                    }
                }
            }
        }
        return $changed;
    }

    /**
     * @param GovnokodRuPostModel|GovnokodRuCommentModel $struct
     * @param GovnokodRuCommentModel[]
     */
    private function _rawComments($struct, &$result) {
        foreach ($struct->comments as $comment) {
            $result[] = $comment;
            $this->_rawComments($comment, $result);
        }
    }
}

global $gk_sync;
$gk_sync = new SyncPostController(new GovnokodRuDataSource());


class SyncLockHelper {
    /**
     * @param string $lock_path lockfile path
     *
     * @return int 1 if need to slip this action because of lock, 0 otherwise
     */
    public static function skipIfLocked($lock_path) {
        $fp = fopen($lock_path, 'a+');

        if (!$fp) {
            return 1;
        }

        $return = 0;

        if (flock($fp, LOCK_EX)) {
            $size = filesize($lock_path);

            if ($size > 0) { // check is file exists and not empty
                $return = 1;
            } else {
                ftruncate($fp, 0);
                fwrite($fp, current_time('mysql'));
                fflush($fp);
            }

            flock($fp, LOCK_UN);
        } else {
            // SOME ERROR
            return 1;
        }

        fclose($fp);

        return $return;
    }
}
