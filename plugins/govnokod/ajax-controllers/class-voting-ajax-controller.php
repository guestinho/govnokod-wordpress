<?php

class VotingAjaxController extends AjaxControllerBase {

    public function main() {
        if (!isset($_REQUEST['v']) || ($_REQUEST['v'] !== '-1' && $_REQUEST['v'] !== '1')) {
            return array(
                'status' => 'error',
                'message' => 'Invalid parameter v',
            );
        }

        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return array(
                'status' => 'error',
                'message' => 'You have not permissions to voting this item',
            );
        }


        $vote = (int) $_REQUEST['v'];

        global $comment, $post;
        $comment = null;
        $post = 0;

        if (isset($_REQUEST['comment_id']) && is_numeric($_REQUEST['comment_id'])) {
            $comment_id = (int) $_REQUEST['comment_id'];
            $comment = get_comment($comment_id);
            if (!$comment->comment_approved) {
                $comment = null;
            }
        }

        if (isset($_REQUEST['post_id']) && is_numeric($_REQUEST['post_id'])) {
            $post_id = (int) $_REQUEST['post_id'];
            $post = get_post($post_id);
            if ($post->post_status !== 'publish') {
                $post = null;
            }
        }

        if (!$post && !$comment) {
            return array(
                'status' => 'error',
                'message' => 'Invalid parameter post_id or comment_id',
            );
        }

        global $gk_vote;
        $html_result = '';

        if ($comment) {
            if ($vote > 0) {
                $gk_vote->voteCommentUp($comment->comment_ID, $user->ID);
            } else if ($vote < 0) {
                $gk_vote->voteCommentDown($comment->comment_ID, $user->ID);
            }
            ob_start();
            get_template_part('template-parts/voting/comment-votes');
            $html_result = ob_get_clean();
        }

        if ($post) {
            if ($vote > 0) {
                $gk_vote->votePostUp($post->ID, $user->ID);
            } else if ($vote < 0) {
                $gk_vote->votePostDown($post->ID, $user->ID);
            }
            ob_start();
            get_template_part('template-parts/voting/post-votes');
            $html_result = ob_get_clean();
        }

        return array(
            'status' => 'success',
            'html' => $html_result,
        );
    }
}

AjaxControllerBase::add('vote', new VotingAjaxController());