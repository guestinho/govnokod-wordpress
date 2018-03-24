<?php

class CommentsAjaxController extends AjaxControllerBase {

    public function main() {
        if (!isset($_REQUEST['post_id']) || !is_numeric($_REQUEST['post_id'])) {
            return $this->error('Invalid post_id parameter');
        }
        $post_id = (int) $_REQUEST['post_id'];
        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish') {
            return $this->error('Invalid post_id parameter');
        }

        $query = new WP_Query(array(
            'p' => $post_id,
        ));
        $html_result = '';

        if ($query->have_posts() ) {
            // The Loop
            while ($query->have_posts()) {
                $query->the_post();

                global $withcomments;
                $withcomments = true;
                ob_start();
                comments_template();
                $html_result = ob_get_clean();
            }
            wp_reset_postdata();
        }

        return $this->html($html_result);
    }

    public function noprivActionFunc() {
        $this->actionFunc();
    }
}

AjaxControllerBase::add('comments', new CommentsAjaxController());