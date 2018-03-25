<?php

class IgnoreListController {

    private static function _getIgnoreArray($user_id) {
        $ignore = get_user_meta($user_id, 'gk_ignore_user', true);
        if (!is_array($ignore)) {
            $ignore = array();
        }
        return $ignore;
    }

    public static function getIgnoredNames($user_id) {
        return array_keys(self::_getIgnoreArray($user_id));
    }

    public static function addIgnoredName($user_id, $name) {
        $current_list = self::_getIgnoreArray($user_id);

        $current_list[$name] = array(
            'date' => time(),
        );
        update_user_meta($user_id, 'gk_ignore_user', $current_list);
    }

    public static function removeAllIgnoredNames($user_id) {
        delete_user_meta($user_id, 'gk_ignore_user');
    }
}