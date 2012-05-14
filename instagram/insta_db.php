<?php

// plugin db functions


// get Instagram photo data from passed Instagram photo ID
function wpinstaroll_getInstagramPhotoDataFromInstaID($insta_id)
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
function wpinstaroll_checkInstagramPhotoDataPresenceFromInstaID($insta_id)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	if (empty($insta_id))
		return false;

	$result = wpinstaroll_getInstagramPhotoDataFromInstaID($insta_id);
	
	if ($result)
		return true;
	else
		return false;
}


// get all Instagram photos in DB
function wpinstaroll_getInstagramPhotos()
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	// returns the pic ordered by timestamp, from the newest to the oldest
	$query = 'SELECT * FROM '.$table.' ORDER BY pic_timestamp DESC';
	$result = $wpdb->get_results($query);

	return $result;
}


// get Instagram photos in db with passed published status (true/false)
function wpinstaroll_getInstagramPhotosByStatus($published)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;
	
	if (empty($published))
		return null;

	if ($published != true)
		$published = 0;
	else
		$published = 1;
	
	$query = 'SELECT * FROM '.$table.' WHERE published='.$published.' ORDER BY pic_timestamp DESC';
	$result = $wpdb->get_results($query);

	return $result;
}


// returns an array containing Instagram IDs of previously published images
function wpinstaroll_getInstagramPublishedPhotosIDs()
{
	$result = wpinstaroll_getInstagramPhotosByStatus(true);

	$indexes = array();

	if ($result)
	{
		foreach ($result as $element)
		{
			$indexes[] = $element->pic_id;
		}
	}

	return $indexes;
}


// insert Instagram photo data into DB (if not already present; if present, return without updating the record)
function wpinstaroll_insertInstagramPhotoData($insta_id, $insta_timestamp, $insta_url, $insta_link='', $published=false, $media_id=null)
{
	global $wpdb;
	$table = WP_ROLL_INSTAGRAM_PICS_TRACK_TABLE;

	// mandatory parameters missing
	if (empty($insta_id) || empty($insta_url))
		return -1;

	// pic data already present
	if (wpinstaroll_checkInstagramPhotoDataPresenceFromInstaID($insta_id))
		return -2;


	if ($published != true)
		$published = 0;
	else
		$published = 1;

	if (empty($media_id))
		$insert_data = array(
			'pic_id'		=>	$insta_id,
			'pic_url'		=>	$insta_url,
			'pic_link'		=>	$insta_link,
			'pic_timestamp'	=>	$insta_timestamp,
			'published'		=>	$published
			);
	else
		$insert_data = array(
			'pic_id'		=>	$insta_id,
			'pic_url'		=>	$insta_url,
			'pic_link'		=>	$insta_link,
			'pic_timestamp'	=>	$insta_timestamp,
			'published'		=>	$published,
			'media_id'		=>	$media_id
			);

	$wpdb->insert($table, $insert_data);
	$added_pic_id = $wpdb->insert_id;
		
	if ($added_pic_id)
		return $added_pic_id;
	else
		return 0;
}


// update status for Instagram photo in local data table
function wpinstaroll_updateInstagramPhotoStatus($insta_id, $published=true, $media_id=null)
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