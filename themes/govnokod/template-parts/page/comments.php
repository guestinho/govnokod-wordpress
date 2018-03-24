<?php

gk_enqueue_highlight_js_assets();

$date_query = array();

if (isset($_GET['before']) && is_string($_GET['before'])) {
    $date_query['before'] = $_GET['before'];
}
if (isset($_GET['after']) && is_string($_GET['after'])) {
    $date_query['after'] = $_GET['after'];
}

$comments = get_comments(array(
    'number' => gk_get_option('gk_comments_per_page'),
    'date_query' => $date_query,
));


$posts_ids = array_unique(wp_list_pluck($comments, 'comment_post_ID'));
// чтобы сделать 1 запрос к базе
//get_posts(array('post__id' => $posts_ids));

$interval = gk_get_option('gk_comments_refresh_interval');
$delay = gk_get_option('gk_comments_refresh_delay');
?>

<?php gk_remove_parent_class_js('posts'); ?>
<?php gk_remove_parent_class_js('hentry'); ?>

<span id="comments-notice"">Страница обновляется автоматически каждые <?php echo $interval; ?>сек. </span>
<ol class="all-comments posts hatom"
    data-update-url="<?php echo esc_attr(AjaxControllerBase::url('all-comments')); ?>"
    data-update-delay="<?php echo $delay; ?>"
    data-update-interval="<?php echo $interval; ?>"
>
    <?php foreach ($comments as $comment): ?>
        <?php get_template_part('template-parts/comment/single'); ?>
    <?php endforeach; ?>

    <div id="more-comments" class="um-load-items">
        <button class="um-ajax-paginate um-button"><?php _e('load more comments', 'ultimate-member'); ?></button>
    </div>
</ol>