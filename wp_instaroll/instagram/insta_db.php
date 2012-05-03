<?php

// plugin db functions


function getInstagramPhotoDataFromInstaID($insta_id)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	if (empty($insta_id))
		return false;
	
	$query = 'SELECT * FROM '.$table.' WHERE pic_id=\''.$insta_id."'";
	$result = $wpdb->get_results($query);

	return $result;
}


function checkInstagramPhotoDataPresenceFromInstaID($insta_id)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	if (empty($insta_id))
		return false;

	$result = getInstagramPhotoDataFromInstaID($insta_id);
	
	if (count($result) > 0)
		return true;
	else
		return false;
}


function getInstagramPublishedPhotos()
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	if (empty($insta_id))
		return false;
	
	$query = 'SELECT * FROM '.$table;
	$result = $wpdb->get_results($query);

	return $result;
}


function insertInstagramPhotoData($insta_id, $insta_url, $insta_link, $published=false)
{




}


function updateInstagramPhotoStatus($insta_id, $published=true)
{



}


?>