<?php

require_once dirname(__FILE__) . '/class-govnokod-ru-users-source.php';

class SyncPostsController {

    /**
     * @var SyncLogger
     */
    public $logger;

    /**
     * SyncUsersController constructor.
     */
    public function __construct() {
        $this->logger = new SyncLogger();
    }

    public function syncPosts($max_count=1) {
        $lock_path = __DIR__ . '/lock/gk_sync_posts.lock';

        if (SyncLockHelper::skipIfLocked($lock_path)) {
            $this->logger->log("Attempt to sync locked posts");
            return null;
        }

        $changed = $this->_syncPosts($max_count);

        unlink($lock_path);

        $this->logger->log("Sync posts complete, $changed changed");
    }

    private function _syncPosts($max_count) {
        $cur_id = (int) gk_get_option('gk_initial_posts_sync_from');
        $to_id = (int) gk_get_option('gk_initial_posts_sync_to');
        $changed = 0;

        while ($max_count-- && $cur_id <= $to_id) {
            global $gk_sync;

            if (!$gk_sync->syncPost($cur_id)) {
                $this->logger->error("Initial sync failed: $cur_id");
            } else {
                $changed++;
            }
            $cur_id++;
            update_option('gk_initial_posts_sync_from', $cur_id);
        }
        return $changed;
    }
}

global $gk_sync_posts;
$gk_sync_posts = new SyncPostsController();