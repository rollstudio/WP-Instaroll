<?php

// plugin db functions


function getInstagramPhotoDataFromInstaID($insta_id)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	if (empty($insta_id))
		return null;
	
	$query = 'SELECT * FROM '.$table.' WHERE pic_id=\''.$insta_id."'";
	$result = $wpdb->get_results($query);

	if (count($result) > 0)
		return $result[0];
	else
		return null;
}


function checkInstagramPhotoDataPresenceFromInstaID($insta_id)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	if (empty($insta_id))
		return false;

	$result = getInstagramPhotoDataFromInstaID($insta_id);
	
	if ($result)
		return true;
	else
		return false;
}


function getInstagramPhotos()
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	$query = 'SELECT * FROM '.$table;
	$result = $wpdb->get_results($query);

	return $result;
}


function getInstagramPhotosByStatus($published)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	if (empty($published))
		return null;

	if ($published != true)
		$published = 0;
	else
		$published = 1;
	
	$query = 'SELECT * FROM '.$table.' WHERE published='.$published;
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