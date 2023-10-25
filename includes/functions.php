<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Utility functions or additional code can go here

/**
 * Example utility function to check if the content freeze alert is enabled.
 * This function can be used elsewhere in the plugin.
 */
function cfa_is_alert_enabled()
{
    return get_option('cfa_enabled', '1') === '1';
}
