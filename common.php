<?php

// plugin commond definitions and configuration


global $wpdb;


// plugin base dir
define('WP_ROLL_INSTAGRAM_PLUGIN_BASE_DIR', 'wp-instaroll');

// plugin base URLs and prefixes
define('WP_ROLL_INSTAGRAM_PLUGIN_PREFIX', 'wpinstaroll');
define('WP_ROLL_INSTAGRAM_PLUGIN_METADATA_PREFIX', 'wpinstaroll');
define('WP_ROLL_INSTAGRAM_PLUGIN_CALLBACK_ACTION', 'wpinstaroll_redirect_uri');

// Instagram base URLs
define('WP_ROLL_INSTAGRAM_DEVELOPER_URL', 'http://instagram.com/developer/');
define('WP_ROLL_INSTAGRAM_USER_STREAM_URLBASE', 'https://api.instagram.com/v1/users/self/feed?access_token=');
define('WP_ROLL_INSTAGRAM_USER_PHOTOS_URL_A', 'https://api.instagram.com/v1/users/');
define('WP_ROLL_INSTAGRAM_USER_PHOTOS_URL_B', '/media/recent/?access_token=');
define('WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_A', 'https://api.instagram.com/v1/tags/');
define('WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_B', '/media/recent?access_token=');

// menu definition
define('WP_ROLL_INSTAGRAM_PHOTOS_MENU', 'Instaroll Photos');
define('WP_ROLL_INSTAGRAM_PHOTOS_PAGE_TITLE', 'Instagram Photo Selection');
define('WP_ROLL_INSTAGRAM_SETTINGS_MENU', 'Instaroll Settings');
define('WP_ROLL_INSTAGRAM_SETTINGS_PAGE_TITLE', 'Instagram Management');
define('WP_ROLL_INSTAGRAM_PHOTOS_TABS_USER', 'Instagram User Stream');
define('WP_ROLL_INSTAGRAM_PHOTOS_TABS_TAG', 'Instagram Photos by Tag');

// additional DB table definitions
define('WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE', $wpdb->prefix.'wpinstaroll_instapics_track_table');
define('WP_ROLL_INSTAGRAM_DB_VERSION_STRING', 'wpinstaroll_db_version');

// error message and error codes (fronted-backend communication)
define('WP_ROLL_INSTAGRAM_ERROR_MISSING_PARAMETERS_MESSAGE', 'Required parameters missing');
define('WP_ROLL_INSTAGRAM_ERROR_MISSING_PARAMETERS_CODE', 1);
define('WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_ACCESS_NOT_CONFIGURED_MESSAGE', 'Instagram access not properly configured');
define('WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_ACCESS_NOT_CONFIGURED_CODE', 2);
define('WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_POST_CREATION_PROBLEM_MESSAGE', 'Problem creating the post');
define('WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_POST_CREATION_PROBLEM_CODE', 3);
define('WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_IMAGE_DOWNLOAD_PROBLEM_MESSAGE', 'Problem downloading the image from Instagram');
define('WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_IMAGE_DOWNLOAD_PROBLEM_CODE', 4);
define('WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_IMAGE_ADD_TO_POST_PROBLEM_MESSAGE', 'Problem adding the image to the post');
define('WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_IMAGE_ADD_TO_POST_PROBLEM_CODE', 5);

// defaults
define('WP_ROLL_INSTAGRAM_DEFAULT_TITLE_PLACEHOLDER', 'Instagram picture');

// minimum requirements
define('WP_ROLL_INSTAGRAM_WP_VERSION_MIN', '3.0');
define('WP_ROLL_INSTAGRAM_PHP_VERSION_MIN', '5.3');



// custom admin styles
function wpinstaroll_admin_basic_init()
{
    if (is_admin())
    {
		wp_register_style(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_admin_styles', plugins_url(WP_ROLL_INSTAGRAM_PLUGIN_BASE_DIR.'/admin_style.css'));
	}
}
add_action('init', 'wpinstaroll_admin_basic_init');

function wpinstaroll_admin_styles()
{
	wp_enqueue_style(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_admin_styles');
}
add_action('admin_print_styles', 'wpinstaroll_admin_styles');


// task scheduling - custom time periods
function wpinstaroll_cron_definer($schedules)
{
	// 1 minute
	$schedules['wpinstaroll_oneminute'] = array(
		'interval'=> 60,
		'display'=> __('Once Every Minute')
  	);

	// 5 minutes
	$schedules['wpinstaroll_fiveminutes'] = array(
		'interval'=> 300,
		'display'=> __('Once Every 5 Minutes')
  	);

	// 10 minutes
	$schedules['wpinstaroll_tenminutes'] = array(
		'interval'=> 600,
		'display'=> __('Once Every 10 Minutes')
  	);

  	// 20 minutes
	$schedules['wpinstaroll_twentynminutes'] = array(
		'interval'=> 1200,
		'display'=> __('Once Every 20 Minutes')
  	);

	// 30 minutes
	$schedules['wpinstaroll_twicehourly'] = array(
		'interval'=> 1800,
		'display'=> __('Once Every 30 Minutes')
  	);

  	// 'hourly', 'twicedaily', 'daily' already defined in WordPress

	// weekly
	$schedules['wpinstaroll_weekly'] = array(
		'interval'=> 604800,
		'display'=> __('Once Every 7 Days')
  	);

	// monthly
	$schedules['wpinstaroll_monthly'] = array(
		'interval'=> 2592000,
		'display'=> __('Once Every 30 Days')
  	);	

	return $schedules;
}
add_filter('cron_schedules','wpinstaroll_cron_definer');


function wpinstaroll_schedule_event($period)
{
	if ($period == 'wpinstaroll_oneminute' ||
		$period == 'wpinstaroll_fiveminutes' ||
		$period == 'wpinstaroll_tenminutes' ||
		$period == 'wpinstaroll_twentynminutes' ||
		$period == 'wpinstaroll_twicehourly' ||
		$period == 'hourly' ||
		$period == 'twicedaily' ||
		$period == 'daily' ||
		$period == 'wpinstaroll_weekly' ||
		$period == 'wpinstaroll_monthly')

		wp_schedule_event(current_time('timestamp'), $period, 'wpinstaroll_scheduled_post_creation_event');
}
function wpinstaroll_remove_scheduled_event()
{
	wp_clear_scheduled_hook('wpinstaroll_scheduled_post_creation_event');
}
add_action('wpinstaroll_scheduled_post_creation_event', 'wpinstaroll_automatic_post_creation');


function wpinstaroll_check_requirements($echo = false)
{
	$requirements_ok =  true;

	if (floatval(get_bloginfo('version')) < floatval(WP_ROLL_INSTAGRAM_WP_VERSION_MIN))
	{
		$error_message = 'WP-Instaroll problem: WordPress version must be at least '.WP_ROLL_INSTAGRAM_WP_VERSION_MIN.'!';

		error_log($error_message);
		if ($echo)
			echo '<p><strong>'.$error_message.'</strong></p>';

		$requirements_ok =  false;
	}

	if (floatval(phpversion()) < floatval(WP_ROLL_INSTAGRAM_PHP_VERSION_MIN))
	{
		$error_message = 'WP-Instaroll problem: PHP version must be at least '.WP_ROLL_INSTAGRAM_PHP_VERSION_MIN.'!';

		error_log($error_message);
		if ($echo)
			echo '<p><strong>'.$error_message.'</strong></p>';

		$requirements_ok =  false;
	}

	if (!function_exists('curl_init'))
	{
		$error_message = 'WP-Instaroll problem: cURL PHP extension must be installed!';

		error_log($error_message);
		if ($echo)
			echo '<p><strong>'.$error_message.'</strong></p>';

		$requirements_ok =  false;
	}

	return $requirements_ok;
}

?>