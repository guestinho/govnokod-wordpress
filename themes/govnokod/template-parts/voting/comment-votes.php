<?php
$base_url = add_query_arg(array('comment_id' => $comment->comment_ID), AjaxControllerBase::url('vote'));
$votes = gk_get_comment_votes();
$my_vote = gk_get_user_comment_vote();
$user = wp_get_current_user();
$is_my_comment = $comment->comment_author === $user->user_login;
?>

<span class="comment-vote<?php if ($my_vote || $is_my_comment) echo ' my-voted'; ?>">
    <?php printf('<strong class="%s" title="%s за и %s против">%s</strong>',
        !is_user_logged_in() ? 'just-rating' : ($votes['rating'] > 0 ? 'good' : ($votes['rating'] < 0 ? 'bad' : '')),
        $votes['on'],
        $votes['against'],
        ($votes['rating'] > 0 ? '+' : '') . $votes['rating']); ?>

    <?php if (is_user_logged_in()): ?>
        <?php if ($is_my_comment): ?>
            <span class="comment-vote-against" title="Это мой комментарий, я не могу за него голосовать"> </span>
        <?php elseif ($my_vote < 0): ?>
            <span class="comment-vote-against my-vote" title="-1"> </span>
        <?php else: ?>
            <a rel="nofollow" class="comment-vote-against" href="<?php echo $base_url; ?>&v=-1" title="-1"> </a>
        <?php endif; ?>

        <?php if ($is_my_comment): ?>
            <span class="comment-vote-on" title="Это мой комментарий, я не могу за него голосовать"> </span>
        <?php elseif ($my_vote > 0): ?>
            <span class="comment-vote-on my-vote" title="+1"> </span>
        <?php else: ?>
            <a rel="nofollow" class="comment-vote-on" href="<?php echo $base_url; ?>&v=1" title="+1"> </a>
        <?php endif; ?>

        <?php if ($my_vote < 0): ?>
            <!--<span class="add-ignore" style="position:absolute;right:0;">
                <a href="<?php echo AjaxControllerBase::url('add-ignore', array('username' => $comment->comment_author)); ?>"
                   title="Кликните, чтобы скрыть комментарии этого пользователя"
                   style="color:red;font-size: 0.8rem;">#скрытьник
                </a>
            </span>-->
        <?php endif; ?>
    <?php endif; ?>
</span>