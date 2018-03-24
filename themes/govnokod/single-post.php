<?php get_header(); ?>

<div class="posts hatom">
	<?php
	while (have_posts()) {
		the_post();
		get_template_part('template-parts/post/content', get_post_format());
	}
	?>
</div>

<?php get_footer();
