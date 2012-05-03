<?php

// plugin db functions

// get Instagram photo data from passed Instagram photo ID
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

// check if Instagram photo with passed Instagram photo ID is present in DB
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

// get all Instagram photos in DB
function getInstagramPhotos()
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	$query = 'SELECT * FROM '.$table;
	$result = $wpdb->get_results($query);

	return $result;
}

// get Instagram photos in db with passed published status (true/false)
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

// insert Instagram photo data into DB (if not already present; if present, return without updating the record)
function insertInstagramPhotoData($insta_id, $insta_url, $insta_link, $published=false, $media_id=null)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;

	// mandatory parameter missing
	if (empty($insta_id))
		return -1;

	// pic data already present
	if (checkInstagramPhotoDataPresenceFromInstaID($insta_id))
		return -2;

	if (empty($insta_url))
		$insta_url = '';
	if (empty($insta_link))
		$insta_link = '';

	if ($published != true)
		$published = 0;
	else
		$published = 1;

	if (empty($media_id))
		$insert_data = array(
			'pic_id'	=>	$insta_id,
			'pic_url'	=>	$insta_url,
			'pic_link'	=>	$insta_link,
			'published'	=>	$published
			);
	else
		$insert_data = array(
			'pic_id'	=>	$insta_id,
			'pic_url'	=>	$insta_url,
			'pic_link'	=>	$insta_link,
			'published'	=>	$published,
			'media_id'	=>	$media_id
			);

	$wpdb->insert($table, $insert_data);
	$added_pic_id = $wpdb->insert_id;
		
	if ($added_pic_id)
		return $added_pic_id;
	else
		return 0;
}


function updateInstagramPhotoStatus($insta_id, $published=true, $media_id=null)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;

	// mandatory parameter missing
	if (empty($insta_id))
		return -1;

	if ($published != true)
		$published = 0;
	else
		$published = 1;


	if (empty($media_id))
	{
		$update_data = array(
			'published'	=>	$published
			);
		$data_formats = array('%d');
	} 
	else {
		$update_data = array(
			'published'	=>	$published,
			'media_id'	=>	$media_id
			);
		$data_formats = array('%d', '%s');
	}

	$result = $wpdb->update($table, $update_data, array('pic_id' => $insta_id), $data_formats, array('%s'));

	return $result;
}


?>