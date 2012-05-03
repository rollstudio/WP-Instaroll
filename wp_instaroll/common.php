<?php

// plugin commond definitions and configuration


global $wpdb;


// plugin base URLs and prefixes
define('WP_ROLL_INSTAGRAM_PLUGIN_PREFIX', 'wpinstaroll');
define('WP_ROLL_INSTAGRAM_PLUGIN_METADATA_PREFIX', 'wpinstaroll');
define('WP_ROLL_INSTAGRAM_PLUGIN_CALLBACK_ACTION', 'wpinstaroll_redirect_uri');

// Instagram base URLs
define('WP_ROLL_INSTAGRAM_DEVELOPER_URL', 'http://instagram.com/developer/');
define('WP_ROLL_INSTAGRAM_USER_STREAM_URLBASE', 'https://api.instagram.com/v1/users/self/feed?access_token=');
define('WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_A', 'https://api.instagram.com/v1/tags/');
define('WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_B', '/media/recent?access_token=');

// menu definition
define('WP_ROLL_INSTAGRAM_PHOTOS_MENU', 'WP-Instaroll Photos');
define('WP_ROLL_INSTAGRAM_PHOTOS_PAGE_TITLE', 'Instagram Photo Selection');
define('WP_ROLL_INSTAGRAM_SETTINGS_MENU', 'WP-Instaroll Settings');
define('WP_ROLL_INSTAGRAM_SETTINGS_PAGE_TITLE', 'Instagram Management');
define('WP_ROLL_INSTAGRAM_PHOTOS_TABS_USER', 'Instagram User Photos');
define('WP_ROLL_INSTAGRAM_PHOTOS_TABS_TAG', 'Instagram Photos by Tag');

// additional DB table definitions
define('WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE', $wpdb->prefix.'wpinstaroll_instapics_track_table');
define('WP_ROLL_INSTAGRAM_DB_VERSION_STRING', 'wpinstaroll_db_version');




// custom admin styles
function wpinstaroll_admin_basic_init()
{
    if (is_admin())
    {
		wp_register_style(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_admin_styles', plugins_url('wp_instaroll/admin_style.css'));
	}
}
add_action('init', 'wpinstaroll_admin_basic_init');

function wpinstaroll_admin_styles()
{
	wp_enqueue_style(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_admin_styles');
}
add_action('admin_print_styles', 'wpinstaroll_admin_styles');

?>