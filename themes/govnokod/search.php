<?php get_header(); ?>

<div class="posts hatom">
	<div class="hentry">
		<?php get_search_form(); ?>
	</div>
	<?php
	if ( have_posts() ) :
		/* Start the Loop */
		while ( have_posts() ) : the_post();

			get_template_part( 'template-parts/post/content' );

		endwhile; // End of the loop.

		gk_the_posts_navigation();

	else : ?>

		<div class="hentry">
			<h2>Ничего не найдено</h2>
			<p>Поиск не дал результатов!</p>
		</div>

		<?php
	endif;
	?>
</div>


<?php get_footer();
