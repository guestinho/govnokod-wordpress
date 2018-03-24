<?php
$author_id = get_the_author_meta('ID');
$author_data = gk_get_user_profile_data($author_id, $post);
$is_profile = !empty($GLOBALS['ultimatemember']->shortcodes->loop);

wp_enqueue_script('comment-reply');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('hentry'); ?>>
	<h2 class="entry-header">
		<?php
			global $post;
			$categories = wp_get_post_terms($post->ID, 'language');
			foreach ($categories as $category) {
				echo '<a rel="chapter" href="' . get_term_link($category->term_id, 'language') . '">' . $category->name . '</a> /';
			}
		?>
		<a rel="bookmark" class="entry-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

		<?php if (gk_is_legacy_post($post)): ?>
			<a href="<?php echo gk_get_legacy_post_url($post); ?>" target="_blank" style="">
				<img title="Этот пост является копией поста с сайта govnokod.ru. Кликните, чтобы перейти к оригиналу."
					 alt="Ссылка на оригинал"
					 src="<?php echo get_template_directory_uri() . '/assets/images/ghost.png'; ?>"
				>
			</a>
		<?php endif; ?>

	</h2>

	<?php get_template_part('template-parts/voting/post-votes'); ?>

	<div class="entry-content">
		<?php gk_the_govnokod(); ?>
	</div>

	<p class="description">
		<?php gk_the_govnokod_description(); ?>
	</p>

	<p class="author">
		<?php if (is_singular()) echo 'Запостил: '; ?>
		<?php if ($author_data['is_legacy']): ?>
			<a target="_blank" href="<?php echo $author_data['url']; ?>"><img src="<?php echo $author_data['avatar']; ?>" class="avatar" width="20" height="20" alt="<?php echo $author_data['name']; ?>"></a>
			<a target="_blank" href="<?php echo $author_data['url']; ?>"><?php echo $author_data['name']; ?></a>,
		<?php else: ?>
			<a href="<?php echo $author_data['url']; ?>"><?php echo get_avatar($author_id, 20, '', '', array('class' => 'avatar')); ?></a>
			<a href="<?php echo $author_data['url']; ?>"><?php echo $author_data['name']; ?></a>,
		<?php endif; ?>
		<?php echo gk_post_time_link(); ?>
	</p>

	<?php

	if (is_singular() && !$is_profile) {
		comments_template();
	} else {
		get_template_part('template-parts/post/comments');
	}
	?>
</article>
