<?php get_header(); ?>

<div class="posts hatom">
	<?php
	if ( have_posts() ) :

		/* Start the Loop */
		while ( have_posts() ) : the_post();

			get_template_part( 'template-parts/post/content');

		endwhile;

		gk_the_posts_navigation();
	endif;
	?>
</div>

<?php get_footer();
