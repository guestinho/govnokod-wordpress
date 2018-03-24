<?php

class GkShortcodes {

    public static function add() {
        ob_start();
        get_template_part('template-parts/page/add-post');
        return ob_get_clean();
    }

    public static function comments() {
        ob_start();
        get_template_part('template-parts/page/comments');
        return ob_get_clean();
    }

    public static function search() {
        ob_start();
        get_template_part('searchform');
        return ob_get_clean();
    }
}

add_shortcode('gk_add', 'GkShortcodes::add');
add_shortcode('gk_comments', 'GkShortcodes::comments');
add_shortcode('gk_search', 'GkShortcodes::search');