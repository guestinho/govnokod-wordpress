<?php
$comments_number = get_comments_number();
$unreaded_comments_count = $comments_number - gk_get_user_read_comments_number();
$nonce = wp_create_nonce('gk-action');
$base_url = add_query_arg(array('_ajax_nonce' => $nonce, 'post_id' => $post->ID), AjaxControllerBase::getActionUrl('comments'));
?>

<div class="entry-comments">
    <span class="comments-icon"></span>
    <a href="<?php echo $base_url; ?>" class="entry-comments-load">Комментарии</a>
    <span class="entry-comments-count">(<?php echo $comments_number;
        if ($unreaded_comments_count) printf(', <span title="Новые комментарии" class="entry-comments-new">+%s</span>', $unreaded_comments_count);
        ?>)</span>
</div>