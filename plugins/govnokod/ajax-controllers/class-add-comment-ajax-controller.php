<?php
defined('ABSPATH') or die;

class AddCommentAjaxController extends AjaxControllerBase {

    public function main() {
        if (is_user_logged_in() && !current_user_can('edit_posts') ||
            !is_user_logged_in() && get_option('comment_registration')
        ) {
            return array(
                'status' => 'error',
                'message' => 'You have not capabilities to write a post',
            );
        }

        $comment = wp_handle_comment_submission(wp_unslash($_POST));

        if (is_wp_error($comment)) {
            return $this->error($comment->get_error_message());
        }

        ob_start();
        wp_list_comments(apply_filters('gk_wp_list_comments_args', array()), array($comment));
        $html = ob_get_clean();

        return $this->html($html);
    }

    public function noprivActionFunc() {
        $this->actionFunc();
    }
}
AjaxControllerBase::add('add-comment', new AddCommentAjaxController());