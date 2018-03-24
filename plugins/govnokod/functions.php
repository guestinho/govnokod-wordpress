<?php

/**
 * TODO:
 * - Подложить UM translations в архив
 * - Нельзя голосовать за себя
 */

define('GK_POST_NAME_PREFIX', 'id');
define('GK_DEFAULT_LANGUAGE_SLUG', 'other');
define('GK_LEGACY_SITE_URL', 'http://govnokod.ru');
define('GK_LEGACY_SITE_USER_URL', GK_LEGACY_SITE_URL . '/user/');
define('GK_LEGACY_COMMENT_IP', '0.0.0.0');
define('GK_LEGACY_COMMENT_EMAIL_SUFFIX', '@lo.ol');
define('GK_LEGACY_POST_NAME_PREFIX', '_');

define('GK_HIGHLIGHT_JS_USE_CDN', true);

function gk_get_default_avatar_url() {
    return gk_get_option('gk_default_avatar_url');
}

function gk_get_guest_avatar_url() {
    return gk_get_option('gk_guest_avatar_url');
}

function gk_get_highlight_type() {
    // 'highlight.js', 'highlight.php'

    return 'highlight.js';
}

function gk_get_highlight_js_style() {
    return 'vs';
}

function gk_get_highlight_php_style() {
    return 'vs';
}

function gk_bb($content) {
    require_once dirname(__FILE__) . '/libs/bbcode/bbcode.php';
    $parser = new bbcode();
    return $parser->parse($content);
}

function gk_lines($content) {
    $content = strtr($content, array(
        "\r" => '',
    ));
    $content = nl2br($content);
    return $content;
}

add_filter('gk_content', 'esc_html');
add_filter('gk_content', 'gk_lines');
add_filter('gk_content', 'gk_bb');

remove_filter('comment_text', 'wptexturize'           );
remove_filter('comment_text', 'convert_chars'         );
remove_filter('comment_text', 'make_clickable',      9);
remove_filter('comment_text', 'force_balance_tags', 25);
remove_filter('comment_text', 'convert_smilies',    20);
remove_filter('comment_text', 'wpautop',            30);
remove_filter('comment_text', 'capital_P_dangit',   31);
remove_filter('comment_text', 'wp_kses_post'          ); // this filter added only when is_admin()

add_filter('comment_text', function ($content) {
    return apply_filters('gk_content', $content);
});

/**
 * Удаление kses для комментов
 * Замену специальных символов берем на себя
 *
 * @param mixed $x
 * @return mixed
 */
function gk_remove_kses_from_comments($x) {
    kses_remove_filters();
    return $x;
}
add_filter('preprocess_comment', 'gk_remove_kses_from_comments');

function gk_enqueue_highlight_js_assets() {
    $ver = '9.12.0';
    $style = gk_get_highlight_js_style();

    if (GK_HIGHLIGHT_JS_USE_CDN) {
        wp_enqueue_style('gk_highlight_js', "//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/$style.min.css", array(), $ver);
        wp_enqueue_script('gk_highlight_css', "//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js", array(), $ver);
    } else {
        wp_enqueue_style('gk_highlight_js', GK_PLUGIN_URL . 'libs/highlight-js/' . $style . '.min.css', array(), $ver);
        wp_enqueue_script('gk_highlight_css', GK_PLUGIN_URL . 'libs/highlight-js/highlight.min.js', array(), $ver);
    }
}

function gk_highlight_js($raw_code, $lang) {
    $highlighted_code = esc_html($raw_code);
    gk_enqueue_highlight_js_assets();
    return $highlighted_code;
}

function gk_highlight_php($raw_code, $lang) {
    require_once 'libs/highlight-php/Highlight/Autoloader.php';
    spl_autoload_register("Highlight\\Autoloader::load");
    $highlighter = new Highlight\Highlighter();

    $result = $highlighter->highlight($lang, $raw_code);
    $highlighted_code = $result->value;

    wp_enqueue_style('gk_highlight', GK_PLUGIN_URL . 'libs/highlight-php/styles/' . gk_get_highlight_php_style() . '.css', array(), '1.0.0');

    return $highlighted_code;
}

function gk_highlight($raw_code, $lang) {
    try {
        if (gk_get_highlight_type() === 'highlight.php') {
            $highlighted_code = gk_highlight_php($raw_code, $lang);
        } else {
            $highlighted_code = gk_highlight_js($raw_code, $lang);
        }
    } catch (Exception $e) {
        $highlighted_code = gk_highlight_js($raw_code, $lang);
    }
    return $highlighted_code;
}

function gk_the_govnokod($post = null) {
    $post = get_post($post);

    $slug = gk_get_post_language_slug($post);
    $lang = $slug; // TODO

    $raw_code = str_replace("\r", '', $post->post_content);
    $raw_code = rtrim($raw_code);

    $highlighted_code = gk_highlight($raw_code, $lang);

    $lines_count = $highlighted_code ? substr_count($highlighted_code, "\n") + 1 : 0;
    ?>
    <ol style="margin-top:0;"><?php for ($i = 1; $i <= $lines_count; $i++) echo "<li>$i</li>"; ?></ol>
    <pre><code class="<?php echo $lang; ?>"><?php echo $highlighted_code; ?></code></pre>
    <?php
}

function gk_the_govnokod_description($post = null) {
    $post = get_post($post);

    $desc = $post->post_excerpt;
    $desc = apply_filters('gk_content', $desc);
    echo $desc;
}


function gk_get_avatar_url($user_id, $size=96) {
    $html = get_avatar($user_id, $size);
    if (!preg_match('#src="([^"]*)"#', $html, $m)) {
        preg_match("#src='([^']*)'#", $html, $m);
    }
    return $m[1];
}

function gk_get_govnokod_id($post = null) {
    $post_name = get_post($post)->post_name;
    return preg_replace('#^(' . GK_POST_NAME_PREFIX . '|' . GK_LEGACY_POST_NAME_PREFIX . ')([0-9]+)#', '$2', $post_name);
}

function gk_get_user_profile_data($user_id, $post=null) {
    $post = get_post($post);
    $legacy_post_author_url = $post ? get_post_meta($post->ID, 'gk_legacy_author_url', true) : false;
    if ($legacy_post_author_url) {
        $legacy_post_author_name = get_post_meta($post->ID, 'gk_legacy_author_name', true);
        $legacy_post_author_avatar = get_post_meta($post->ID, 'gk_legacy_author_avatar', true);
        $result['avatar'] = $legacy_post_author_name !== 'guest'
            ? sprintf("http://www.gravatar.com/avatar/%s?default=%s&r=pg&size=28", $legacy_post_author_avatar, gk_get_default_avatar_url())
            : gk_get_guest_avatar_url();
        $result['url'] = $legacy_post_author_url;
        $result['name'] = $legacy_post_author_name;
        $result['is_legacy'] = true;
    } else {
        // copy-paste
        // see um_comment_link_to_profile

        global $ultimatemember;

        if (empty($ultimatemember->user ->cached_user[$user_id])) {
            um_fetch_user($user_id);
            $ultimatemember->user->cached_user[$user_id] = array(
                'url' => um_user_profile_url(),
                'name' => um_user('display_name')
            );
            um_reset_user();
        }
        $result = $ultimatemember->user->cached_user[$user_id];
        $result['is_legacy'] = false;
    }
    return $result;
}

/**
 * Remove (almost) comments depth limit
 *
 * @return int
 */
function gk_pre_option_thread_comments_depth() {
    return 1000;
}
add_filter('pre_option_thread_comments_depth', 'gk_pre_option_thread_comments_depth');

/**
 * @param WP_Post $post
 *
 * @return bool
 */
function gk_is_legacy_post($post=null) {
    return !!gk_get_legacy_post_url($post);
}

/**
 * @param WP_Post $post
 *
 * @return string
 */
function gk_get_legacy_post_url($post=null) {
    $post = get_post($post);
    $return = get_post_meta($post->ID, 'gk_legacy_url', true);
    return $return ? $return : '';
}

/**
 * @param WP_Post $comment
 *
 * @return bool
 */
function gk_is_legacy_comment($comment=null) {
    $comment = get_comment($comment);
    return $comment->comment_author_IP === GK_LEGACY_COMMENT_IP;
}

/**
 * @param WP_Comment $comment
 *
 * @return string
 */
function gk_get_legacy_comment_url($comment=null) {
    $comment = get_comment($comment);
    $return = get_post_meta($comment->comment_post_ID, 'gk_legacy_url', true);
    return $return ? $return . '#comment' . $comment->comment_agent : '';
}

function gk_legacy_avatar_filter($avatar, $comment) {
    if ($comment instanceof WP_Comment) {
        // comment_author_email - author gravatar hash
        if (gk_is_legacy_comment($comment)) {
            if ($comment->comment_author === 'guest') {
                $src = gk_get_guest_avatar_url();
            } else {
                $hash = str_replace(GK_LEGACY_COMMENT_EMAIL_SUFFIX, '', $comment->comment_author_email);
                $src = sprintf("http://www.gravatar.com/avatar/%s?default=%s&r=pg&size=28", $hash, gk_get_default_avatar_url());
            }
            $avatar = preg_replace('#(src=["\'])([^"\']*)#', '$1' . $src, $avatar);
            $avatar = preg_replace('#srcset=["\'][^"\']*["\']#', '', $avatar);
        }
    }
    return $avatar;
}
add_filter('get_avatar', 'gk_legacy_avatar_filter', 10, 2);

/**
 * Get language terms
 *
 * @param array $args
 * @return WP_Error|WP_Term[]
 */
function gk_get_languages($args = array()) {
    $args = wp_parse_args($args, array(
        'taxonomy' => 'language',
        'orderby' => 'count',
        'order' => 'DESC',
        'hide_empty' => false,
    ));

    return get_terms($args);
}

/**
 * Get post's language slug
 *
 * @param WP_Post|int|null $post
 * @return string
 */
function gk_get_post_language_slug($post=null) {
    $post = get_post($post);

    $terms = wp_get_post_terms($post->ID, 'language');
    foreach ($terms as $term) {
        return $term->slug;
    }
    return '';
}

if (!is_admin()) {
    /**
     * Exclude pages from WordPress Search
     *
     * @param WP_Query $query
     * @return mixed
     */
    function gk_search_filter($query) {
        if ($query->is_search && $query->is_main_query()) {
            $query->set('post_type', 'post');
        }
        return $query;
    }
    add_filter('pre_get_posts','gk_search_filter');
}

add_filter('um_image_upload_nonce', function () {
    die('Image upload disabled');
});

add_action('init', function () {
    remove_action('um_profile_header_cover_area', 'um_profile_header_cover_area', 9);
});

add_filter('um_user_photo_menu_view', '__return_empty_array');
add_filter('um_user_photo_menu_edit', '__return_empty_array');

add_action('wp_head', function () {
    echo '<script>var TEMPLATE_PATH = ' . json_encode(get_template_directory_uri()) . ';</script>';
});

require_once dirname(__FILE__) . '/controllers/synchronization/registration.php';