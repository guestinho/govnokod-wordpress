<?php


/**
 * Указываем необходимые плагины
 */
function gk_register_required_plugins() {
    $plugins = array(
        array(
            'name'               => 'Ultimate Member', // The plugin name.
            'slug'               => 'ultimate-member', // The plugin slug (typically the folder name).
            'source'             => get_template_directory() . '/plugins/ultimate-member.1.3.88.zip', // The plugin source.
            'required'           => true, // If false, the plugin is only 'recommended' instead of required.
            'version'            => '1.3.88', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
            'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
            'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
            'external_url'       => '', // If set, overrides default API URL and points to an external URL.
            'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
        ),
        array(
            'name'               => 'Govnokod', // The plugin name.
            'slug'               => 'govnokod', // The plugin slug (typically the folder name).
            'source'             => get_template_directory() . '/plugins/govnokod.zip', // The plugin source.
            'required'           => true, // If false, the plugin is only 'recommended' instead of required.
            'version'            => '0.0.2', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
            'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
            'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
            'external_url'       => '', // If set, overrides default API URL and points to an external URL.
            'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
        ),
    );

    $config = array(
        'id'           => 'gk',                    // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      // Default absolute path to bundled plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'parent_slug'  => 'themes.php',            // Parent menu slug.
        'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => true,                    // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.
    );

    tgmpa($plugins, $config);
}
require_once dirname(__FILE__) . '/inc/class-tgm-plugin-activation.php';
add_action('tgmpa_register', 'gk_register_required_plugins');


/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function gk_setup() {
	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support('title-tag');

    register_nav_menus(array(
        'top' => __('Top Menu', 'gk'),
    ));

	add_theme_support('html5', array(
		'comment-form',
		'comment-list',
		'caption',
	));
}
add_action('after_setup_theme', 'gk_setup');

/**
 * TODO: Не знаю нужно ли это
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function gk_pingback_header() {
	if (is_singular() && pings_open()) {
		printf('<link rel="pingback" href="%s">' . "\n", get_bloginfo('pingback_url'));
	}
}
add_action('wp_head', 'gk_pingback_header');

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function gk_body_classes($classes) {
    // Add class of group-blog to blogs with more than 1 published author.
    if (is_multi_author()) {
        $classes[] = 'group-blog';
    }

    // Add class of hfeed to non-singular pages.
    if (!is_singular()) {
        $classes[] = 'hfeed';
    }

    // Add a class if there is a custom header.
    if (has_header_image()) {
        $classes[] = 'has-header-image';
    }

    return $classes;
}
add_filter('body_class', 'gk_body_classes');

/**
 * Gets a nicely formatted string for the published date.
 */
function gk_post_time_link() {
    $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
    if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time> (Updated <time class="updated" datetime="%3$s">%4$s</time>)';
    }

    $time_string = sprintf( $time_string,
        get_the_date( DATE_W3C ),
        get_the_date(),
        get_the_modified_date( DATE_W3C ),
        get_the_modified_date()
    );

    return $time_string;
}

/**
 * Enqueue scripts and styles.
 */
function gk_scripts_and_styles() {
    $ver = wp_get_theme()->get('Version');

    // Theme stylesheet.
    wp_enqueue_style('gk-style', get_stylesheet_uri(), array(), $ver);

    // Theme script.
    wp_enqueue_script('gk-global', get_theme_file_uri('/assets/js/global.js'), array('jquery'), $ver, true);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'gk_scripts_and_styles');


add_filter('comment_class', function ($classes) {
	$classes[] = 'hcomment';
	return $classes;
});
require get_parent_theme_file_path('/inc/class-gk-walker-comment.php');

add_filter('um_get_option_filter__default_avatar', function ($data) {
    $data['url'] = gk_get_default_avatar_url();
    return $data;
});

add_filter('um_predefined_fields_hook', function ($fields) {
	$fields['username_b']['placeholder'] = 'Логин или E-mail';
	return $fields;
});

add_filter('gettext', function ($translation, $text, $domain) {
	if ($text === 'Anonymous') {
		$translation = 'guest';
	}
	return $translation;
}, 10, 3);

add_filter('avatar_defaults', function ($avatar_defaults) {
	$avatar_defaults[gk_get_guest_avatar_url()] = "Guest";
	return $avatar_defaults;
});

function gk_the_posts_navigation() {
	the_posts_pagination(array(
		'prev_text' => '← влево',
		'next_text' => 'вправо →',
		'screen_reader_text' => ' ',
	));
}

add_filter('gk_wp_list_comments_args', function ($args) {
    $args['avatar_size'] = 100;
    $args['style']       = 'ul';
    $args['short_ping']  = true;
    $args['reply_text']  = 'Ответить';
    $args['walker']      = new GK_Walker_Comment();
    return $args;
});

function gk_remove_parent_class_js($class) {
    echo "<script>jQuery(document.scripts[document.scripts.length - 1]).closest('.$class').removeClass('$class');</script>";
}

add_filter('the_title', function ($title, $id) {
    if (get_post_type($id) === 'post') {
        $title = sprintf('Говнокод #%s', gk_get_govnokod_id($id));
    }
    return $title;
}, 10, 2);