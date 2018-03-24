<?php
$base_url = add_query_arg(array('comment_id' => $comment->comment_ID), AjaxControllerBase::url('vote'));
$votes = gk_get_comment_votes();
$my_vote = gk_get_user_comment_vote();
?>

<span class="comment-vote<?php if ($my_vote) echo ' my-voted'; ?>">
    <?php printf('<strong class="%s" title="%s за и %s против">%s</strong>',
        !is_user_logged_in() ? 'just-rating' : ($votes['rating'] > 0 ? 'good' : ($votes['rating'] < 0 ? 'bad' : '')),
        $votes['on'],
        $votes['against'],
        ($votes['rating'] > 0 ? '+' : '') . $votes['rating']); ?>

    <?php if (is_user_logged_in()): ?>
        <?php if ($my_vote < 0): ?>
            <span class="comment-vote-against my-vote" title="-1"> </span>
        <?php else: ?>
            <a rel="nofollow" class="comment-vote-against" href="<?php echo $base_url; ?>&v=-1" title="-1"> </a>
        <?php endif; ?>

        <?php if ($my_vote > 0): ?>
            <span class="comment-vote-on my-vote" title="+1"> </span>
        <?php else: ?>
            <a rel="nofollow" class="comment-vote-on" href="<?php echo $base_url; ?>&v=1" title="+1"> </a>
        <?php endif; ?>
    <?php endif; ?>
</span>