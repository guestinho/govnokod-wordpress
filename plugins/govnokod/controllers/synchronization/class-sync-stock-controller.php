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

        $last_synced_comment_id = file_exists(__DIR__ . '/lock/last_synced_comment_id') ? (int) file_get_contents(__DIR__ . '/lock/last_synced_comment_id') : 0;
        if ($gk_comments) {
            file_put_contents(__DIR__ . '/lock/last_synced_comment_id', $gk_comments[0]->id);
        }

        foreach ($gk_comments as $comment) {
            if ($comment->id <= $last_synced_comment_id) {
                break;
            }
            global $gk_sync;
            $gk_sync->syncPost($comment->post_id);
            $changed++;
        }
        return $changed;
    }
}

global $gk_sync_stock;
$gk_sync_stock = new SyncStockController(new GovnokodRuStockParser());