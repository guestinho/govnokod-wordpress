<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="icon" href="<?php echo get_template_directory_uri() . '/assets/images/favicon.ico'; ?>" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo get_template_directory_uri() . '/assets/images/favicon.ico'; ?>" type="image/x-icon" />

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="page">
	<div id="header">
		<h1><a rel="homepage" href="<?php echo get_home_url(); ?>">Говнокод: по колено в коде.</a></h1>

		<div id="userpane">
			<ul class="menu" style="background-size: contain; background-image: url('<?php echo gk_get_avatar_url(wp_get_current_user()->ID, 20); ?>');">
				<?php $user = wp_get_current_user(); ?>
				<li>
					<?php if ($user->ID): ?>
						<a id="expand-trigger" href="<?php echo gk_get_user_profile_data($user->ID)['url']; ?>">Привет, <?php echo $user->display_name; ?>!</a>
					<?php else: ?>
						<li><a id="expand-trigger0" href="<?php the_permalink(um_get_option('core_login')); ?>">Войти в говнокод</a></li>
					<?php endif; ?>
				</li>

				<?php
				 ?>

			</ul>


			<div class="pane-content">
				<ul>
					<li><a href="<?php echo gk_get_user_profile_data($user->ID)['url']; ?>">Кабинка</a></li>
					<li><a href="<?php the_permalink(um_get_option('core_account')); ?>">Настройки</a></li>
					<li>&nbsp;</li>
					<li><a href="<?php the_permalink(um_get_option('core_logout')); ?>">Выйти</a></li>
				</ul>
			</div>

		</div>

		<?php if (has_nav_menu('top')) : ?>
			<?php get_template_part('template-parts/navigation/navigation', 'top'); ?>
		<?php endif; ?>

		<p id="entrance">
			Нашли или выдавили из себя код, который нельзя назвать нормальным,
			на который без улыбки не взглянешь?
			Не торопитесь его удалять или рефакторить, &mdash; запостите его на
			говнокод.ру, посмеёмся вместе!
		</p>

		<ol id="language">
		<?php
			foreach (gk_get_languages(array('hide_empty' => true)) as $term) {
				echo '<li><a href="' . get_term_link($term) . '">' . $term->name . '</a> <span>('. $term->count .')</span></li>';
			}
		?>
		</ol>
	</div>

	<div id="content">