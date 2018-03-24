<?php

$gk_default_settings = array(
    'gk_default_avatar_url' => 'http://govnokod.ru/files/avatars/noavatar_100.png',
    'gk_guest_avatar_url' => 'http://govnokod.ru/files/avatars/guest_100.png',
    'gk_post_lines_limit' => 100,
    'gk_comments_per_page' => 40,
    'gk_comments_refresh_interval' => 30,
    'gk_comments_refresh_delay' => 5,
    'gk_enable_sync' => 0,
    'gk_enable_initial_posts_sync' => 0,
    'gk_enable_initial_posts_syncs_per_30_sec' => 2,
    'gk_initial_posts_sync_from' => 1,
    'gk_initial_posts_sync_to' => 23986,
);

$gk_default_site_options = array(
    'users_can_register' => 1,
    'default_role' => 'contributor',
    'timezone_string' => 'UTC+0',
    'gmt_offset' => '0',
    'default_pingback_flag' => 0,
    'require_name_email' => 0,
    'comments_notify' => 0,
    'comment_whitelist' => 0,
    'avatar_default' => $gk_default_settings['gk_guest_avatar_url'],
    'permalink_structure' => '/%postname%/',
    'blogdescription' => 'зеркало',
);

$gk_options = array(
    array(
        "name" => "Avatars",
        "type" => "sub-section-3",
        "category" => "avatars",
    ),
    array(
        "name" => "Default avatar url",
        "desc" => "Set the image to use for users without avatar.",
        "id" => "gk_default_avatar_url",
        "type" => "input",
        "placeholder" => "http://",
    ),
    array(
        "name" => "Guest avatar url",
        "desc" => "Set the image to use for guests. (You need to select new avatar in wp-admin/options-discussion.php after change this option)",
        "id" => "gk_guest_avatar_url",
        "type" => "input",
        "placeholder" => "http://",
    ),

    array(
        "name" => "Posts",
        "type" => "sub-section-3",
        "category" => "posts",
    ),
    array(
        "name" => "Post lines limit",
        "desc" => "",
        "id" => "gk_post_lines_limit",
        "type" => "input",
        "input-type" => "number",
    ),

    array(
        "name" => "Comments",
        "type" => "sub-section-3",
        "category" => "comments",
    ),
    array(
        "name" => "Number of comments per page on comments page",
        "desc" => "",
        "id" => "gk_comments_per_page",
        "type" => "input",
        "input-type" => "number",
    ),
    array(
        "name" => "Ajax refresh interval",
        "desc" => "(in seconds)",
        "id" => "gk_comments_refresh_interval",
        "type" => "input",
        "input-type" => "number",
    ),
    array(
        "name" => "Ajax refresh delay",
        "desc" => "Time before sending first ajax request (in seconds)",
        "id" => "gk_comments_refresh_delay",
        "type" => "input",
        "input-type" => "number",
    ),

    array(
        "name" => "Govnokod.ru synchronization",
        "type" => "sub-section-3",
        "category" => "sync",
    ),
    array(
        "name" => "Enable synchronization",
        "desc" => "Enable synchronization of stock comments, users",
        "id" => "gk_enable_sync",
        "type" => "checkbox",
    ),


    array(
        "id" => "gk_enable_initial_posts_sync",
        "type" => "",
    ),
    array(
        "id" => "gk_initial_posts_sync_from",
        "type" => "",
    ),
    array(
        "id" => "gk_initial_posts_sync_to",
        "type" => "",
    ),
    array(
        "id" => "gk_enable_initial_posts_syncs_per_30_sec",
        "type" => "",
    ),
);

function gk_get_option($option) {
    global $gk_default_settings;
    $return = get_option($option);
    if ($return === false && isset($gk_default_settings[$option])) {
        $return = $gk_default_settings[$option];
    }
    return $return;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// https://codex.wordpress.org/Creating_Options_Pages

function create_opening_tag($value) {
    $group_class = "";
    if (isset($value['grouping'])) {
        $group_class = "suf-grouping-rhs";
    }
    echo '<div class="suf-section fix">'."\n";
    if ($group_class != "") {
        echo "<div class='$group_class fix'>\n";
    }
    if (isset($value['name'])) {
        echo "<h4>" . $value['name'] . "</h4>\n";
    }
    if (isset($value['desc']) && !(isset($value['type']) && $value['type'] == 'checkbox')) {
        echo '<p>' . $value['desc'] . '</p>';
    }
    if (isset($value['note'])) {
        echo "<span class=\"note\">".$value['note']."</span>";
    }
}

/**
 * Creates the closing markup for each option.
 *
 * @param $value
 * @return void
 */
function create_closing_tag($value) {
    if (isset($value['grouping'])) {
        echo "</div>\n";
    }
    //echo "</div><!-- suf-section -->\n";
    echo "</div>\n";
}

function create_suf_header_3($value) { echo '<h3 class="suf-header-3">'.$value['name']."</h3>\n"; }

function create_section_for_checkbox($value) {
    create_opening_tag($value);
    $checked = gk_get_option($value['id']);
    $desc = isset($value['desc']) ? $value['desc'] : '';

    echo '<input type="checkbox" id="' . $value['id'] . '" name="' . $value['id'] . '" value="1" ' . ($checked ? 'checked' : '') . ' />'. $desc . "\n";
    create_closing_tag($value);
}

function create_section_for_text($value) {
    create_opening_tag($value);
    $text = gk_get_option($value['id']);
    $placeholder = isset($value['placeholder']) ? $value['placeholder'] : '';
    $type = isset($value['input-type']) ? $value['input-type'] : 'text';

    echo '<input type="' . $type . '" id="' . $value['id'] . '" placeholder="' . $placeholder . '" name="' . $value['id'] . '" value="' . $text . '" />'."\n";
    create_closing_tag($value);
}

function create_section_for_textarea($value) {
    create_opening_tag($value);
    echo '<textarea name="'.$value['id'].'" type="textarea" cols="" rows="">'."\n";

    echo gk_get_option( $value['id'] );

    echo '</textarea>';
    create_closing_tag($value);
}

function create_section_for_radio($value) {
    create_opening_tag($value);
    foreach ($value['options'] as $option_value => $option_text) {
        $checked = ' ';
        if (gk_get_option($value['id']) == $option_value) {
            $checked = ' checked="checked" ';
        }
        else {
            $checked = ' ';
        }
        echo '<div class="mnt-radio"><input type="radio" name="'.$value['id'].'" value="'.
            $option_value.'" '.$checked."/>".$option_text."</div>\n";
    }
    create_closing_tag($value);
}

function create_section_for_multi_select($value) {
    create_opening_tag($value);
    echo '<ul class="mnt-checklist" id="'.$value['id'].'" >'."\n";
    foreach ($value['options'] as $option_value => $option_list) {
        $checked = " ";
        if (gk_get_option($value['id']."_".$option_value)) {
            $checked = " checked='checked' ";
        }
        echo "<li>\n";
        echo '<input type="checkbox" name="'.$value['id']."_".$option_value.'" value="true" '.$checked.' class="depth-'.($option_list['depth']+1).'" />'.$option_list['title']."\n";
        echo "</li>\n";
    }
    echo "</ul>\n";
    create_closing_tag($value);
}

function create_section_for_category_select($page_section,$value) {
    create_opening_tag($value);
    $all_categoris='';
    echo '<div class="wrap" id="'.$value['id'].'" >'."\n";
    echo '<h1>Theme Options</h1> '."\n" .'
				<p><strong>'.$page_section.':</strong></p>';
    echo "<select id='".$value['id']."' class='post_form' name='".$value['id']."' value='true'>\n";
    echo "<option id='all' value=''>All</option>";
    foreach ($value['options'] as $option_value => $option_list) {
        $checked = ' ';
        echo 'value_id=' . $value['id'] .' value_id=' . gk_get_option($value['id']) . ' options_value=' . $option_value;
        if (gk_get_option($value['id']) == $option_value) {
            $checked = ' checked="checked" ';
        }
        else {
            $checked = '';
        }
        echo '<option value="'.$option_list['name'].'" class="level-0" '.$checked.' number="'.($option_list['number']).'" />'.$option_list['name']."</option>\n";
        //$all_categoris .= $option_list['name'] . ',';
    }
    echo "</select>\n </div>";
    //echo '<script>jQuery("#all").val("'.$all_categoris.'")</\script>';
    create_closing_tag($value);
}

function create_form($options) {
    ?>
    <style>
        input[name*=reset_all] {
            background-color: #ff000023 !important;
        }
        #options_form input[type="text"] {
            width: 800px;
            max-width: 100%;
            height: 30px;
        }
        #options_form .suf-section p {
            margin: 2px;
        }
        #options_form .suf-section h4 {
            margin: 7px 0;
        }
    </style>
    <?php

    echo "<form id='options_form' method='post' name='form' >\n";
    foreach ($options as $value) {
        switch ( $value['type'] ) {
            case "sub-section-3":
                create_suf_header_3($value);
                break;

            case "checkbox":
                create_section_for_checkbox($value);
                break;

            case "input":
                create_section_for_text($value);
                break;

            case "textarea":
                create_section_for_textarea($value);
                break;

            case "multi-select":
                create_section_for_multi_select($value);
                break;

            case "radio":
                create_section_for_radio($value);
                break;

            case "select":
                create_section_for_category_select('first section',$value);
                break;
            case "select-2":
                create_section_for_category_select('second section',$value);
                break;
        }
    }

    ?>

    <h1>Status</h1>
    <h3>Users sync last id:</h3>
    <p style="color:blue"><?php echo $GLOBALS['gk_sync_users']->getMaxId(); ?></p>

    <h3>Active locks:</h3>
    <?php
    $dir = GK_PLUGIN_PATH . '/controllers/synchronization/lock';
    foreach (scandir($dir) as $fname) {
        if (preg_match('#gk_sync_#', $fname)) {
            echo "<p style='color:red'>$fname</p>";
        }
    }
    ?>
    <p>
        <input type="checkbox" id="gk_enable_initial_posts_sync" name="gk_enable_initial_posts_sync" value="1" <?php if (gk_get_option('gk_enable_initial_posts_sync')) echo 'checked'; ?>>
        Enable initial posts synchronization?
        <br>
        Post id's from: <input type="number" id="gk_initial_posts_sync_from" placeholder="" name="gk_initial_posts_sync_from" value="<?php echo gk_get_option('gk_initial_posts_sync_from'); ?>">
        to: <input type="number" id="gk_initial_posts_sync_to" placeholder="" name="gk_initial_posts_sync_to" value="<?php echo gk_get_option('gk_initial_posts_sync_to'); ?>">
        <input type="number" id="gk_enable_initial_posts_syncs_per_30_sec" placeholder="" name="gk_enable_initial_posts_syncs_per_30_sec" value="<?php echo gk_get_option('gk_enable_initial_posts_syncs_per_30_sec'); ?>">
        times per 30 seconds
    </p>



    <div style="margin-top:20px">
        <?php wp_nonce_field('gk-action'); ?>
        <input name="save" type="button" value="Save" class="button button-primary" onclick="submit_form(this, document.forms['form'])" />
        <input name="reset_all" type="button" value="Reset to default values" class="button" onclick="submit_form(this, document.forms['form'])" />
        <input name="reset_all2" type="button" value="Reset site settings to default values" class="button" onclick="submit_form(this, document.forms['form'])" />
        <input name="add_languages" type="button" value="Add default languages" class="button button-primary" onclick="submit_form(this, document.forms['form'])" />
        <input type="hidden" name="formaction" value="default" />
    </div>
    <div>

    </div>

    <script>
        function submit_form(element, form) {
            form['formaction'].value = element.name;
            form.submit();
        }
    </script>

    </form>
<?php }



add_action('admin_menu', 'mynewtheme_add_admin');
function mynewtheme_add_admin() {
    global $gk_options, $gk_default_settings;

    if (current_user_can('administrator') && isset($_GET['page']) && isset($_REQUEST['formaction']) && $_GET['page'] === 'govnokod-settings' ) {
        check_admin_referer('gk-action');

        if ('save' === $_REQUEST['formaction'] ) {
            foreach ($gk_options as $value) {
                if (isset($value['id'])) {
                    if (isset($_REQUEST[$value['id']])) {
                        update_option($value['id'], $_REQUEST[$value['id']]);
                    } else {
                        delete_option($value['id']);
                    }
                }
            }

            echo '<div id="message" class="updated fade"><p><strong>Settings saved for this page.</strong></p></div>';

        } else if ('reset_all' == $_REQUEST['formaction']) {
            foreach ($gk_default_settings as $id => $value) {
                delete_option($id);
            }

            echo '<div id="message" class="updated fade"><p><strong>Settings reset.</strong></p></div>';
        } else if ('reset_all2' == $_REQUEST['formaction']) {
            gk_reset_site_settings();
            echo '<div id="message" class="updated fade"><p><strong>Settings reset.</strong></p></div>';
        } else if ('add_languages' == $_REQUEST['formaction']) {
            gk_add_default_languages();
            echo '<div id="message" class="updated fade"><p><strong>Languages added.</strong></p></div>';
        }
    }

    add_menu_page('Govnokod settings', 'Govnokod settings', 'administrator', 'govnokod-settings', 'mynewtheme_admin');
}

function gk_reset_site_settings() {
    global $gk_default_site_options;
    foreach ($gk_default_site_options as $id => $value) {
        update_option($id, $value);
    }
}

function gk_add_default_languages() {
    $defaults = array_flip(GovnokodRuLanguageDecoder::$map);
    $defaults['bash'] = 'Bash';
    $defaults['c'] = 'C';
    $defaults['go'] = 'Go';
    $defaults[GK_DEFAULT_LANGUAGE_SLUG] = 'Куча';

    $langs = gk_get_languages();
    $slugs = wp_list_pluck($langs, 'slug');
    $names = wp_list_pluck($langs, 'name');

    foreach ($defaults as $slug => $name) {
        if (!in_array($slug, $slugs) && !in_array($name, $names)) {
            wp_insert_term($name, 'language', array('slug' => $slug));
        }
    }
}

function mynewtheme_admin() {
    global $gk_options;

    ?>
    <div class="wrap">
        <h1>Govnokod settings</h1>
        <div class="mnt-options">
            <?php create_form($gk_options); ?>
        </div><!-- mnt-options -->
    </div><!-- wrap -->
<?php } // end function mynewtheme_admin()