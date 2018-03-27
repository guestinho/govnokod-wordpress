<?php
/*
Plugin Name: Govnokod
Plugin URI: https://todo
Description: Govnokod core functionality
Text Domain: gk
Version: 0.0.2
Author: guestinho
Author URI: https://todo
*/
defined('ABSPATH') or die;

$gk_plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));

define('GK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GK_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('GK_PLUGIN_VERSION', $gk_plugin_data['Version']);

require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/includes/class-gk-language-taxonomy.php';
require_once dirname(__FILE__) . '/includes/class-gk-shortcodes.php';
require_once dirname(__FILE__) . '/includes/settings.php';

require_once dirname(__FILE__) . '/controllers/class-unread-comments-controller.php';
require_once dirname(__FILE__) . '/controllers/class-voting-controller.php';
require_once dirname(__FILE__) . '/controllers/class-post-title-controller.php';
require_once dirname(__FILE__) . '/controllers/class-ignore-list-controller.php';
require_once dirname(__FILE__) . '/ajax-controllers/class-ajax-controller-base.php';
require_once dirname(__FILE__) . '/ajax-controllers/class-add-comment-ajax-controller.php';
require_once dirname(__FILE__) . '/ajax-controllers/class-voting-ajax-controller.php';
require_once dirname(__FILE__) . '/ajax-controllers/class-comments-ajax-controller.php';
require_once dirname(__FILE__) . '/ajax-controllers/class-all-comments-ajax-controller.php';
require_once dirname(__FILE__) . '/ajax-controllers/class-ignore-list-ajax-controller.php';

require_once dirname(__FILE__) . '/controllers/synchronization/class-sync-post-controller.php';
require_once dirname(__FILE__) . '/controllers/synchronization/class-sync-stock-controller.php';
require_once dirname(__FILE__) . '/controllers/synchronization/class-sync-main-controller.php';
require_once dirname(__FILE__) . '/controllers/synchronization/class-sync-users-controller.php';
require_once dirname(__FILE__) . '/controllers/synchronization/class-sync-posts-controller.php';
require_once dirname(__FILE__) . '/controllers/synchronization/scheduling.php';

function gk_init_db() {
    do_action('gk_init_db');
}
register_activation_hook(__FILE__, 'gk_init_db');

function gk_first_init() {
    if (!get_option('gk_installed_1')) {
        gk_add_default_languages();
        gk_reset_site_settings();
        update_option('gk_installed_1', 1);
    }
}
add_action('init', 'gk_first_init');