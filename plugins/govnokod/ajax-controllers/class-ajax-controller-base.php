<?php
defined('ABSPATH') or die;

class AjaxControllerBase {

    public static $controllers = array();

    public function actionFunc() {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

        if (null !== $action && isset(self::$controllers[$action]) && is_callable(array(self::$controllers[$action], 'main'))) {
            call_user_func(array(self::$controllers[$action], 'checkReferer'));

            $result = call_user_func(array(self::$controllers[$action], 'main'));
            die(json_encode($result));
        }
        die(json_encode(array('status' => 'error', 'message' => 'INVALID_ACTION')));
    }

    public function noprivActionFunc() {
        die(json_encode(array('status' => 'error', 'message' => 'INVALID_SESSION')));
    }

    public static function checkReferer() {
        check_ajax_referer('gk-action');
    }

    public static function add($action, $instance) {
        if (is_callable(array($instance, 'main'))) {
            add_action('wp_ajax_nopriv_'. $action, array($instance, 'noprivActionFunc'));
            add_action('wp_ajax_' . $action, array($instance, 'actionFunc'));
            self::$controllers[$action] = $instance;
        }
    }

    public static function getActionUrl($action) {
        return add_query_arg(array('action' => $action), admin_url('admin-ajax.php'));
    }

    public static function url($action) {
        return add_query_arg(array('_ajax_nonce' => wp_create_nonce('gk-action')), self::getActionUrl($action));
    }

    public function error($message) {
        return array(
            'status' => 'error',
            'message' => $message,
        );
    }

    public function html($html) {
        return array(
            'status' => 'success',
            'html' => $html,
        );
    }
}