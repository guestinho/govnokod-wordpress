<?php

class GK_Walker_Comment extends Walker_Comment {

    protected function html5_comment($comment, $depth, $args) {
        $tag = 'div' === $args['style'] ? 'div' : 'li';
        ?>
        <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class($this->has_children ? 'parent' : '', $comment); ?>>
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body entry-comment-wrapper">
            <p class="comment-meta entry-info">
                <?php gk_comment_header_template($comment, $args); ?>

                <?php get_template_part('template-parts/voting/comment-votes'); ?>

                <?php if ('0' == $comment->comment_approved): ?>
                    <p class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.'); ?></p>
                <?php endif; ?>
            </p>

            <div class="comment-content entry-comment">
                <?php comment_text(); ?>
            </div>

            <?php
            comment_reply_link( array_merge( $args, array(
                'add_below' => 'div-comment',
                'depth'     => $depth,
                'max_depth' => $args['max_depth'],
                'before'    => '<div class="reply">',
                'after'     => '</div>'
            ) ) );
            ?>
        </article>
        <?php
    }
}

function gk_comment_header_template($comment, $args) {
    if (!empty($args['avatar_size'])) {
        echo get_avatar($comment, $args['avatar_size'], '', 'ava', array('class' => 'avatar'));
    }
    $is_legacy = gk_is_legacy_comment($comment);
    $author_link = get_comment_author_link($comment);
    if ($is_legacy) {
        $author_link = str_replace('href=', 'target="_blank" href=', $author_link);
    }
    ?>

    <strong class="comment-author vcard entry-author"><?php printf('<b class="fn">%s</b>', $author_link); ?></strong>

    <time datetime="<?php comment_time('c'); ?>">
        <?php echo human_time_diff(strtotime($comment->comment_date)); ?> назад
    </time>

    <a href="<?php echo esc_url(get_comment_link($comment, $args)); ?>">#</a>

    <?php edit_comment_link(__('Edit'), '<span class="edit-link">', '</span>'); ?>

    <?php if ($is_legacy): ?>
        <a href="<?php echo gk_get_legacy_comment_url($comment); ?>" target="_blank" style="border-bottom:none">
            <img title="Этот комментарий является копией комментария с сайта govnokod.ru. Кликните, чтобы перейти к оригиналу."
                 alt="Ссылка на оригинал"
                 src="<?php echo get_template_directory_uri() . '/assets/images/ghost.png'; ?>"
            >
        </a>
    <?php endif; ?>
<?php
}

add_filter('comment_reply_link', function ($link) {
    $link = str_replace('comment-reply-link', 'comment-reply-link answer', $link);
    $link = preg_replace('#<svg[\s\S]*?svg>#', '', $link);
    return $link;
});