<?php

/**
 * Plugin Name: Content Freeze
 * Plugin URI: https://kaleidico.com
 * Description: Displays a content freeze alert in the WordPress dashboard.
 * Version: 2.2
 * Author: Angelo Marasa
 * Author URI: https://kaleidico.com
 * License: GPL2
 */

/* -------------------------------------------------------------------------------------- */
// Updated
require 'puc/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/amarasa/content-freeze',
    __FILE__,
    'content-freeze-helper'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('stable-branch-name');

//Optional: If you're using a private repository, specify the access token like this:
// $myUpdateChecker->setAuthentication('your-token-here');

/* -------------------------------------------------------------------------------------- */

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

    // Check if the user is on the dashboard and if the alert is enabled
    if (!isset($_COOKIE['cfa_hide_alert']) && $is_enabled === '1') {
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
// Register the settings
function cfa_register_settings()
{
    register_setting('cfa_settings_group', 'cfa_enabled');
    register_setting('cfa_settings_group', 'cfa_special_user');  // Register special user setting here
    register_setting('cfa_settings_group', 'cfa_custom_message');

    add_settings_section(
        'cfa_settings_section',
        'General Settings',
        'cfa_render_settings_section',
        'content-freeze-alert'
    );

    // Add the special user field here
    add_settings_field(
        'cfa_special_user',
        'Select Special User',
        'cfa_render_special_user_field',
        'content-freeze-alert',
        'cfa_settings_section'
    );

    add_settings_field(
        'cfa_enabled',
        'Enable Content Freeze Alert',
        'cfa_render_enabled_field',
        'content-freeze-alert',
        'cfa_settings_section'
    );

    add_settings_field(
        'cfa_custom_message',
        'Custom Modal Message',
        'cfa_render_custom_message_field',
        'content-freeze-alert',
        'cfa_settings_section'
    );
}
add_action('admin_init', 'cfa_register_settings');

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

function cfa_restrict_user_capabilities($allcaps, $caps, $args, $user)
{
    // Check if the content freeze is enabled
    $is_enabled = get_option('cfa_enabled', '1');

    // Get the special user ID from the settings
    $special_user_id = get_option('cfa_special_user', '');

    // Check if the current user is the special user
    $is_special_user = ($user->ID == $special_user_id);

    // If content freeze is enabled and the user is not special, restrict capabilities
    if ($is_enabled === '1' && !$is_special_user) {
        foreach ($allcaps as $cap => $value) {
            if ($cap != 'read') {
                unset($allcaps[$cap]);
            }
        }
    }

    return $allcaps;
}


add_filter('user_has_cap', 'cfa_restrict_user_capabilities', 10, 4);

function cfa_dashboard_widget_content()
{
    // Get the custom message from the settings, if available
    $custom_message = get_option('cfa_custom_message', 'Content is currently frozen. Please do not make any changes.');

    echo '<div class="cfa-dashboard-widget">';
    echo wp_kses_post($custom_message);
    echo '</div>';
}

function cfa_add_dashboard_widget()
{
    // Check if the content freeze is enabled
    $is_enabled = get_option('cfa_enabled', '1');

    if ($is_enabled === '1') {
        wp_add_dashboard_widget(
            'cfa_dashboard_widget',         // Widget slug
            'Content Freeze',               // Title
            'cfa_dashboard_widget_content'  // Display function
        );
    }
}

add_action('wp_dashboard_setup', 'cfa_add_dashboard_widget');

// Register the special user setting
function cfa_register_special_user_setting()
{
    register_setting('cfa_settings_group', 'cfa_special_user');
    add_settings_field(
        'cfa_special_user',
        'Select Special User',
        'cfa_render_special_user_field',
        'content-freeze-alert',
        'cfa_settings_section'
    );
}
add_action('admin_init', 'cfa_register_special_user_setting');

// Render the user dropdown field
function cfa_render_special_user_field()
{
    $selected_user = get_option('cfa_special_user', '');
    wp_dropdown_users(array(
        'name' => 'cfa_special_user',
        'selected' => $selected_user,
        'show_option_none' => '— Select a User —'
    ));
}
