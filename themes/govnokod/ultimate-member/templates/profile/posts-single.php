<?php while ($ultimatemember->shortcodes->loop->have_posts()) { $ultimatemember->shortcodes->loop->the_post(); $post_id = get_the_ID(); ?>

	<?php get_template_part('template-parts/post/content'); ?>

<?php } ?>

<?php if ( isset($ultimatemember->shortcodes->modified_args) && $ultimatemember->shortcodes->loop->have_posts() && $ultimatemember->shortcodes->loop->found_posts >= 10 ) { ?>

	<div class="um-load-items">
		<a href="#" class="um-ajax-paginate um-button" data-hook="um_load_posts" data-args="<?php echo $ultimatemember->shortcodes->modified_args; ?>"><?php _e('load more posts','ultimate-member'); ?></a>
	</div>

<?php } ?>