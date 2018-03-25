<?php

class IgnoreListAjaxController extends AjaxControllerBase {

    public function main() {
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return $this->error('You have not permissions for this action');
        }

        if (!isset($_REQUEST['username']) || !is_string($_REQUEST['username'])) {
            return $this->error('Invalid parameter username');
        }
        $ignore_user_name = $_REQUEST['username'];

        if (strlen($ignore_user_name) > 60) {
            return $this->error('Invalid parameter username');
        }

        if (empty($ignore_user_name)) {
            IgnoreListController::removeAllIgnoredNames($user->ID);
            wp_redirect(wp_get_referer());
        } else {
            IgnoreListController::addIgnoredName($user->ID, $ignore_user_name);
        }

        return $this->html('');
    }
}

AjaxControllerBase::add('add-ignore', new IgnoreListAjaxController());