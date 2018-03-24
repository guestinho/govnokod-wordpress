<li class="hentry">
    <?php $comment_post = get_post($comment->comment_post_ID); ?>
    <h2>Комментарий к <a rel="bookmark" class="entry-title" href="<?php the_permalink($comment_post); ?>">говнокоду #<?php echo gk_get_govnokod_id($comment_post); ?></a></h2>
    <div class="entry-comments">
        <ul>
            <li class="hcomment">
                <div class="entry-comment-wrapper">
                    <p class="entry-info">
                        <?php gk_comment_header_template($comment, array('avatar_size' => 28)); ?>
                    </p>

                    <div class="entry-comment"><?php comment_text($comment); ?></div>

                    <a class="answer" href="<?php echo esc_url(get_comment_link($comment)); ?>">Ответить</a>
                </div>
            </li>
        </ul>
    </div>
</li>