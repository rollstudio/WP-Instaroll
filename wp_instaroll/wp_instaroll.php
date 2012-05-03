<?php
/*
Plugin Name: WP-Instaroll
Plugin URI: http://rollstudio.it
Description: Simple Instagram plug-in for creating WordPress posts from Instagram photos
Version: 1.0.2
Author: ROLL Multimedia Design
Author URI: http://rollstudio.it
*/


require_once('common.php');
require_once('instagram/instagram.php');
require_once('instagram/insta_db.php');
require_once('instagram/panel.php');
require_once('instagram/ajax_panel.php');



// plugin activation hook - create custom db table on plugin activation
function wpinstaroll_instapics_track_install()
{
	global $wpdb;

	$table_name = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;

	$sql = 'CREATE TABLE '.$table_name.' (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		pic_id VARCHAR(256) NOT NULL UNIQUE,
		pic_url VARCHAR(256),
		pic_link VARCHAR(256),
		time_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		published TINYINT(1) DEFAULT 0,
		media_id BIGINT(20) DEFAULT NULL,
		UNIQUE KEY id (id)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

	// id: auto_increment table ID/index
	// pic_id		Instagram photo id
	// pic_url		Instagram photo source URL
	// pic_link		Instagram photo page link
	// time_added	when was the photo data added to db
	// published	boolean flag specifying if the photo has be published on WordPress blog or not
	// media_id		for Instagram photos imported in WordPress blog, id of the media file

	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option('WP_ROLL_INSTAGRAM_DB_VERSION_STRING', '0.1');
}
register_activation_hook(WP_PLUGIN_DIR.'/wp_instaroll/wp_instaroll.php', 'wpinstaroll_instapics_track_install');

?>
