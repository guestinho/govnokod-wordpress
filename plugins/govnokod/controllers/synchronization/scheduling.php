<?php

add_action('init', function () {
    global $gk_sync, $gk_sync_stock, $gk_sync_users, $gk_sync_posts;

    if (current_user_can('editor') || current_user_can('administrator')) {
        if (isset($_GET['snk']) && is_string($_GET['snk'])) {
            foreach (explode(',', $_GET['snk']) as $id) {
                if (is_numeric($id)) {
                    $gk_sync->syncPost((int) $id);
                }
            }
        }
        if (isset($_GET['snks'])) {
            $gk_sync_stock->syncStock();
        }
        if (isset($_GET['snkp'])) {
            $gk_sync_posts->syncPosts((int) $_GET['snkp']);
        }
        if (isset($_GET['snku']) && is_numeric($_GET['snku'])) {
            $gk_sync_users->syncUsers((int) $_GET['snku']);
        }
    }

    if (gk_get_option('gk_enable_sync')) {
        if (!wp_next_scheduled('gk_sync_stock')) {
            wp_schedule_event(time(), '30sec', 'gk_sync_stock');
        }
        add_action('gk_sync_stock', array($gk_sync_stock, 'syncStock'));

        if (!wp_next_scheduled('gk_sync_users')) {
            wp_schedule_event(time(), 'hourly', 'gk_sync_users');
        }
        add_action('gk_sync_users', function () {
            global $gk_sync_users;
            $gk_sync_users->syncUsers(10);
        });
    } else {
        wp_clear_scheduled_hook('gk_sync_stock');
        wp_clear_scheduled_hook('gk_sync_users');
    }

    if (gk_get_option('gk_enable_initial_posts_sync')) {
        if (!wp_next_scheduled('gk_sync_posts')) {
            wp_schedule_event(time(), '30sec', 'gk_sync_posts');
        }
        add_action('gk_sync_posts', function () {
            global $gk_sync_posts;
            $gk_sync_posts->syncPosts((int) gk_get_option('gk_enable_initial_posts_syncs_per_30_sec'));
        });
    } else {
        wp_clear_scheduled_hook('gk_sync_posts');
    }
});



function gk_extend_schedules($arr) {
    $arr['30sec'] = array(
        'interval' => 30,
        'display' => '30 seconds'
    );
    return $arr;
}
add_filter('cron_schedules', 'gk_extend_schedules');