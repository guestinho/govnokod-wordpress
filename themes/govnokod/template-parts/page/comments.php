<?php

gk_enqueue_highlight_js_assets();

$date_query = array();

if (isset($_GET['before']) && is_string($_GET['before'])) {
    $date_query['before'] = $_GET['before'];
}
if (isset($_GET['after']) && is_string($_GET['after'])) {
    $date_query['after'] = $_GET['after'];
}

$filter = function ($pieces) {
    global $user_ID;
    $names = IgnoreListController::getIgnoredNames($user_ID);
    if ($names) {
        global $wpdb;
        $list = call_user_func_array(array($wpdb, 'prepare'), array_merge(array(implode(',', array_fill(0, count($names), '%s'))), $names));
        $where = "(comment_author NOT IN ($list))";
        if (empty($pieces['where'])) {
            $pieces['where'] = $where;
        } else {
            $pieces['where'] = "(({$pieces['where']}) AND $where)";
        }
    }
    return $pieces;
};
add_filter('comments_clauses', $filter);

$comments = get_comments(array(
    'number' => gk_get_option('gk_comments_per_page'),
    'date_query' => $date_query,
));

remove_filter('comments_clauses', $filter);


$posts_ids = array_unique(wp_list_pluck($comments, 'comment_post_ID'));
// чтобы сделать 1 запрос к базе
//get_posts(array('post__id' => $posts_ids));

$interval = gk_get_option('gk_comments_refresh_interval');
$delay = gk_get_option('gk_comments_refresh_delay');
?>

<?php gk_remove_parent_class_js('posts'); ?>
<?php gk_remove_parent_class_js('hentry'); ?>

<span id="comments-notice">Страница обновляется автоматически каждые <?php echo $interval; ?>сек. </span>
<?php if (is_user_logged_in() && IgnoreListController::getIgnoredNames($GLOBALS['user_ID'])): ?>
    <a
        href="<?php echo AjaxControllerBase::url('add-ignore', array('username' => '')); ?>"
        style="position:absolute;right:2em;z-index:1;margin-top:-2.5em;"
    >
        Показать всё, что скрыто
    </a>
<?php endif; ?>
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