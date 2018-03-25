<?php
$base_url = add_query_arg(array('post_id' => $post->ID), AjaxControllerBase::url('vote'));
$votes = gk_get_post_votes();
$my_vote = gk_get_user_post_vote();
$user = wp_get_current_user();
$is_my_post = (int) $post->post_author === $user->ID;
?>

<p class="vote<?php if ($my_vote || $is_my_post) echo ' my-voted'; ?>">
    <?php if (is_user_logged_in()): ?>
        <?php if ($is_my_post): ?>
            <span class="vote-against" title="Я не могу голосовать за собственный код">↓</span>
        <?php elseif ($my_vote < 0): ?>
            <span class="vote-against my-vote" title="Минуснул">↓</span>
        <?php else: ?>
            <a class="vote-against" rel="nofollow" href="<?php echo $base_url; ?>&v=-1" title="Минусну!">↓</a>
        <?php endif; ?>
    <?php endif; ?>

    <?php printf('<strong class="%s" title="%s за и %s против">%s</strong>',
        !is_user_logged_in() ? 'just-rating' : ($votes['rating'] < 0 ? 'bad' : ''),
        $votes['on'],
        $votes['against'],
        ($votes['rating'] > 0 ? '+' : '') . $votes['rating']); ?>

    <?php if (is_user_logged_in()): ?>
        <?php if ($is_my_post): ?>
            <span class="vote-on" title="Я не могу голосовать за собственный код"">↑</span>
        <?php elseif ($my_vote > 0): ?>
            <span class="vote-on my-vote" title="Плюсанул">↑</span>
        <?php else: ?>
            <a class="vote-on" rel="nofollow" href="<?php echo $base_url; ?>&v=1" title="Плюсану!">↑</a>
        <?php endif; ?>
    <?php endif; ?>
</p>