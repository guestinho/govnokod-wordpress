<?php
/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area entry-comments">

	<?php
	$comments_number = get_comments_number();
	if ( have_comments() ) : ?>
		<h3 class="comments-title">
			Комментарии <span class="enrty-comments-count">(<?php echo $comments_number; ?>)</span>
			<span class="rss"><a href="#" rel="alternative">RSS</a></span>
		</h3>

		<ul class="comment-list">
			<?php wp_list_comments(apply_filters('gk_wp_list_comments_args', array())); ?>
		</ul>
	<?php
	endif;

	$comment_form_args = array(
		'action'               => AjaxControllerBase::url('add-comment'),
		'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title"><a class="selected">',
		'title_reply_after'    => '</a></h3>',
		'title_reply'          => 'Добавить комментарий',
		'comment_field'        => '<dl class="errors"><dd><ol></ol></dd></dl> <dl><dd><textarea cols="5" id="comment" name="comment" rows="5" maxlength="65525" aria-required="true" required="required"></textarea>
			<div class="field-info">
                А не использовать ли нам <a href="http://govnokod.ru/page/bbcode" onclick="comments.toggleBBCodeBlock(this); return false;">bbcode</a>?
                <div class="bbcodes" style="display: none;">
                    <ul style="margin-left: 0;">
                        <li>[b]жирный[/b] — <b>жирный</b></li>
                        <li>[i]курсив[/i] — <i>курсив</i></li>
                        <li>[u]подчеркнутый[/u] — <span style="text-decoration:underline;">подчеркнутый</span></li>
                        <li>[s]перечеркнутый[/s] — <span style="text-decoration:line-through;">перечеркнутый</span></li>
                        <li>[blink]мигающий[/blink] — <span style="text-decoration:blink;">мигающий</span></li>
                        <li>[color=red]цвет[/color] — <span style="color:red;">цвет</span> (<a href="http://govnokod.ru/page/bbcode#color">подробнее</a>)</li>
                        <li>[size=20]размер[/size] — <span style="font-size:20px">размер</span> (<a href="http://govnokod.ru/page/bbcode#size">подробнее</a>)</li>
                        <li>[code=&lt;language&gt;]some code[/code] (<a href="http://govnokod.ru/page/bbcode#code">подробнее</a>)</li>
                    </ul>
                </div>
            </div></dd></dl>',
		'label_submit'         => 'Отправить комментарий [Ctrl+Enter]',
		'class_submit'         => 'submit send',
	);

	if (!get_option('require_name_email')) {
		$comment_form_args['fields'] = array();
		$comment_form_args['comment_notes_before'] = '';
	}

	comment_form($comment_form_args);
	?>

</div>
