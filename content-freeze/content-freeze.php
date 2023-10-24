<?php

/**
 * Plugin Name: Content Freeze
 * Plugin URI: https://kaleidico.com
 * Description: Displays a content freeze alert in the WordPress dashboard.
 * Version: 1.0
 * Author: Angelo Marasa
 * Author URI: https://kaleidico.com
 * License: GPL2
 */

include plugin_dir_path(__FILE__) . 'includes/functions.php';


// Enqueue the necessary scripts and styles
function cfa_enqueue_scripts()
{
    wp_enqueue_style('cfa-styles', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('cfa-scripts', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0.0', true);

    // Pass ajax_url to script.js
    wp_localize_script('cfa-scripts', 'cfa_params', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('admin_enqueue_scripts', 'cfa_enqueue_scripts');


// Display the modal in the dashboard
function cfa_display_modal()
{
    // Check if the alert is enabled in the settings
    $is_enabled = get_option('cfa_enabled', '1');

    // Check if the user is on the dashboard, if the cookie is not set, and if the alert is enabled
    if (current_user_can('manage_options') && !isset($_COOKIE['cfa_hide_alert']) && $is_enabled === '1') {
        include plugin_dir_path(__FILE__) . 'includes/modal.php';
    }
}

add_action('admin_footer', 'cfa_display_modal');

// Handle the AJAX request to set the cookie
function cfa_set_cookie()
{
    setcookie('cfa_hide_alert', '1', time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
    wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_cfa_set_cookie', 'cfa_set_cookie');


// Add the options page to the WordPress dashboard
function cfa_add_options_page()
{
    add_options_page(
        'Content Freeze Alert Settings',
        'Content Freeze Alert',
        'manage_options',
        'content-freeze-alert',
        'cfa_render_options_page'
    );
}
add_action('admin_menu', 'cfa_add_options_page');

// Render the options page
function cfa_render_options_page()
{
?>
    <div class="wrap">
        <h2>Content Freeze Alert Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('cfa_settings_group');
            do_settings_sections('content-freeze-alert');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

// Register the settings
function cfa_register_settings()
{
    register_setting('cfa_settings_group', 'cfa_enabled');

    add_settings_section(
        'cfa_settings_section',
        'General Settings',
        'cfa_render_settings_section',
        'content-freeze-alert'
    );

    add_settings_field(
        'cfa_enabled',
        'Enable Content Freeze Alert',
        'cfa_render_enabled_field',
        'content-freeze-alert',
        'cfa_settings_section'
    );
}
add_action('admin_init', 'cfa_register_settings');

// Render the settings section
function cfa_render_settings_section()
{
    echo '<p>Enable or disable the content freeze alert for the WordPress dashboard.</p>';
}

// Render the "Enable Content Freeze Alert" field
function cfa_render_enabled_field()
{
    $value = get_option('cfa_enabled', '1');
    echo '<input type="checkbox" id="cfa_enabled" name="cfa_enabled" value="1"' . checked(1, $value, false) . ' />';
}


// Register the custom message setting
function cfa_register_custom_message_setting()
{
    register_setting('cfa_settings_group', 'cfa_custom_message');

    add_settings_field(
        'cfa_custom_message',
        'Custom Modal Message',
        'cfa_render_custom_message_field',
        'content-freeze-alert',
        'cfa_settings_section'
    );
}
add_action('admin_init', 'cfa_register_custom_message_setting');

// Render the WYSIWYG field for the custom message
function cfa_render_custom_message_field()
{
    $value = get_option('cfa_custom_message', '');
    wp_editor($value, 'cfa_custom_message', array('textarea_name' => 'cfa_custom_message'));
}

function cfa_check_option_update($option, $old_value, $new_value)
{
    if ($option === 'cfa_enabled' && $new_value === '0' && isset($_COOKIE['cfa_hide_alert'])) {
        // Delete the cookie
        setcookie('cfa_hide_alert', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
    }
}
add_action('update_option', 'cfa_check_option_update', 10, 3);
