<?php

require_once dirname(__FILE__) . '/class-govnokod-ru-stock-parser.php';

class SyncStockController {

    /**
     * @var SyncLogger
     */
    public $logger;

    /**
     * SyncStockController constructor.
     * @param GovnokodRuStockParser $source
     */
    public function __construct($source) {
        $this->source = $source;
        $this->logger = new SyncLogger();
    }

    public function syncStock() {
        $lock_path = __DIR__ . '/lock/gk_sync_stock.lock';

        if (SyncLockHelper::skipIfLocked($lock_path)) {
            $this->logger->log("Attempt to sync locked stock");
            return null;
        }

        $changed = $this->_syncStock();

        unlink($lock_path);

        $this->logger->log("Sync stock complete, $changed changed");
    }


    private function _syncStock() {
        $changed = 0;
        set_time_limit(20 * 60);

        $gk_comments = $this->source->loadComments();

        if (is_wp_error($gk_comments)) {
            $this->logger->error($gk_comments);
            return 0;
        }

        foreach ($gk_comments as $comment) {
            if ($this->_needSync($comment)) {
                global $gk_sync;
                $gk_sync->syncPost($comment->post_id);
                $changed++;
            }
        }
        return $changed;
    }

    private function _needSync($gk_comment) {
        $post = get_page_by_path(GK_LEGACY_POST_NAME_PREFIX . $gk_comment->post_id, OBJECT, 'post');
        if (!$post) {
            return true;
        }

        $comment_time = strtotime($gk_comment->date_gmt);
        $comment_end_time = strtotime("+6 minutes", $comment_time);

        $last_sync = (int) get_post_meta($post->ID, 'gk_legacy_sync_time', true);

        if ($comment_time >= $last_sync) {
            // comment not exists in our database
            return true;
        }

        if ($last_sync <= $comment_end_time && time() >= $comment_end_time) {
            // comment exists in our database, but may be edited, and edit is disabled now
            return true;
        }
        return false;
    }
}

global $gk_sync_stock;
$gk_sync_stock = new SyncStockController(new GovnokodRuStockParser());