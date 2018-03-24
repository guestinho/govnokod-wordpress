<?php

class GkLanguageTaxonomy {

    public static function createTaxonomy() {
        $labels = array(
            'name'                       => _x('Languages', 'taxonomy general name', 'gk'),
            'singular_name'              => _x('Language', 'taxonomy singular name', 'gk'),
            'search_items'               => __('Search Languages', 'gk'),
            'popular_items'              => __('Popular Languages', 'gk'),
            'all_items'                  => __('All Languages', 'gk'),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __('Edit Language', 'gk'),
            'update_item'                => __('Update Language', 'gk'),
            'add_new_item'               => __('Add New Language', 'gk'),
            'new_item_name'              => __('New Language Name', 'gk'),
            'separate_items_with_commas' => __('Separate languages with commas', 'gk'),
            'add_or_remove_items'        => __('Add or remove languages', 'gk'),
            'choose_from_most_used'      => __('Choose from the most used languages', 'gk'),
            'not_found'                  => __('No languages found.', 'gk'),
            'menu_name'                  => __('Languages', 'gk'),
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array('slug' => 'language'),
        );

        register_taxonomy('language', 'post', $args);
    }
}

add_action('init', 'GkLanguageTaxonomy::createTaxonomy', 0);
