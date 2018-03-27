<?php

require_once dirname(__FILE__) . '/class-govnokod-ru-main-parser.php';

class SyncMainController {

    /**
     * @var SyncLogger
     */
    public $logger;

    /**
     * SyncStockController constructor.
     * @param GovnokodRuMainParser $source
     */
    public function __construct($source) {
        $this->source = $source;
        $this->logger = new SyncLogger();
    }

    public function syncMain() {
        $lock_path = __DIR__ . '/lock/gk_sync_main.lock';

        if (SyncLockHelper::skipIfLocked($lock_path)) {
            $this->logger->log("Attempt to sync locked main");
            return null;
        }

        $changed = $this->_syncMain();

        unlink($lock_path);

        $this->logger->log("Sync main complete, $changed changed");
    }


    private function _syncMain() {
        $changed = 0;
        set_time_limit(10 * 60);

        $gk_posts = $this->source->loadPosts();

        if (is_wp_error($gk_posts)) {
            $this->logger->error($gk_posts);
            return 0;
        }

        foreach ($gk_posts as $gk_post) {
            if ($this->_needSync($gk_post)) {
                global $gk_sync;
                $gk_sync->syncPost($gk_post->id);
                $changed++;
            }
        }
        return $changed;
    }

    private function _needSync($gk_post) {
        $post = get_page_by_path(GK_LEGACY_POST_NAME_PREFIX . $gk_post->id, OBJECT, 'post');
        return !$post;
    }
}

global $gk_sync_main;
$gk_sync_main = new SyncMainController(new GovnokodRuMainParser());