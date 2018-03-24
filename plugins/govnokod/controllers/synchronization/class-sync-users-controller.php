<?php

require_once dirname(__FILE__) . '/class-govnokod-ru-users-source.php';

class SyncUsersController {

    /**
     * @var IGovnokodUsersSource
     */
    public $source;

    /**
     * @var SyncLogger
     */
    public $logger;

    /**
     * SyncUsersController constructor.
     * @param IGovnokodUsersSource $source
     */
    public function __construct($source) {
        $this->source = $source;
        $this->logger = new SyncLogger();

        global $wpdb;
        $wpdb->gk_legacy_users = $wpdb->prefix . 'gk_legacy_users';
    }

    public function syncUsers($max_count=1) {
        $lock_path = __DIR__ . '/lock/gk_sync_users.lock';

        if (SyncLockHelper::skipIfLocked($lock_path)) {
            $this->logger->log("Attempt to sync locked users");
            return null;
        }

        $changed = $this->_syncUsers($max_count);

        unlink($lock_path);

        $this->logger->log("Sync users complete, $changed changed");
    }

    public function getMaxId() {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT ID FROM $wpdb->gk_legacy_users ORDER BY ID DESC LIMIT 1");
    }

    private function _syncUsers($max_count) {
        global $wpdb;
        $cur_id = $this->getMaxId() + 1;
        $changed = 0;

        while ($max_count--) {
            $gk_user = $this->source->loadUser($cur_id);

            if (is_wp_error($gk_user)) {
                $this->logger->error($gk_user);
                break;
            }

            if (!$gk_user) {
                break;
            }

            $wpdb->query($wpdb->prepare("INSERT INTO $wpdb->gk_legacy_users (ID, name, gravatar) VALUES (%s, %s, %s)", $gk_user->id, $gk_user->name, $gk_user->avatar));
            $changed++;
            $cur_id++;
        }
        return $changed;
    }

    public function isUserExists($user_name) {
        global $wpdb;
        $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->gk_legacy_users WHERE name=%s", $user_name));
        return !!$id;
    }

    public function initTables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        if (!$wpdb->get_row("SHOW TABLES FROM `" . DB_NAME . "` LIKE '$wpdb->gk_legacy_users'")) {
            dbDelta("CREATE TABLE $wpdb->gk_legacy_users (
              ID mediumint(9) NOT NULL,
              name char(22) NOT NULL,
              gravatar char(33) NOT NULL,
              PRIMARY KEY  (ID) 
            ) $charset_collate;");

            $wpdb->query("CREATE INDEX name ON $wpdb->gk_legacy_users (name);");

            if (file_exists(__DIR__ . '/data/users_init.json')) {
                $json = json_decode(file_get_contents(__DIR__ . '/data/users_init.json'), true);
                foreach ($json as $gk_user) {
                    $wpdb->query($wpdb->prepare("INSERT INTO $wpdb->gk_legacy_users (ID, name, gravatar) VALUES (%s, %s, %s)", $gk_user["user_id"], $gk_user["user_name"], $gk_user["user_avatar"]));
                }
            }
        }
    }
}

global $gk_sync_users;
$gk_sync_users = new SyncUsersController(new GovnokodRuUsersSource());
add_action('gk_init_db', array($gk_sync_users, 'initTables'));