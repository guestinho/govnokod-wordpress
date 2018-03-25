<li class="hentry">
    <?php $comment_post = get_post($comment->comment_post_ID); ?>
    <h2>Комментарий к <a rel="bookmark" class="entry-title" href="<?php the_permalink($comment_post); ?>">говнокоду #<?php echo gk_get_govnokod_id($comment_post); ?></a></h2>
    <div class="entry-comments">
        <ul>
            <li class="hcomment">
                <div class="entry-comment-wrapper">
                    <p class="entry-info">
                        <?php gk_comment_header_template($comment, array('avatar_size' => 28)); ?>

                        <?php if (is_user_logged_in()): ?>
                            <span class="add-ignore" style="position:absolute;right:2em;">
                                <a href="<?php echo AjaxControllerBase::url('add-ignore', array('username' => $comment->comment_author)); ?>"
                                   title="Кликните, чтобы скрыть комментарии этого пользователя"
                                   style="color:red;font-size: 0.8rem;">#скрытьник
                                </a>
                            </span>
                        <?php endif; ?>
                    </p>

                    <div class="entry-comment"><?php comment_text($comment); ?></div>

                    <a class="answer" href="<?php echo esc_url(get_comment_link($comment)); ?>">Ответить</a>
                </div>
            </li>
        </ul>
    </div>
</li>