<?php


function curl_file_get_contents($url)
{
	$curl = curl_init();
	$userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';

	curl_setopt($curl,CURLOPT_URL,$url);
	// TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
	// the number of seconds to wait while trying to connect
	curl_setopt($curl,CURLOPT_CONNECTTIMEOUT, 6); 	

	curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($curl, CURLOPT_TIMEOUT, 12);	

	$contents = curl_exec($curl);
	curl_close($curl);
	return $contents;
}


// Instagram API


	// *** INSTAGRAM AUTHENTICATION ***

// Instagram redirect URI: where to go after user app authorization
function wpinstaroll_getInstagramRedirectURI()
{
	return get_bloginfo('wpurl').'/wp-admin/admin-ajax.php?action='.WP_ROLL_INSTAGRAM_PLUGIN_CALLBACK_ACTION;
}


// gets Instagram login/authorization page URI
function wpinstaroll_getAuthorizationPageURI()
{
	$InstagramClientID = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');
	$InstagramClientSecret = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');
	$InstagramRedirectURI = wpinstaroll_getInstagramRedirectURI();
	
	if (empty($InstagramClientID) || empty($InstagramClientSecret) || empty($InstagramRedirectURI))
		return null;
	
	// API: http://instagr.am/developer/auth/
	return 'https://api.instagram.com/oauth/authorize/?client_id='.$InstagramClientID.'&redirect_uri='.urlencode($InstagramRedirectURI).'&response_type=code';
}


// handler for Integram redirect URI
function wpinstaroll_deal_with_instagram_auth_redirect_uri()
{
	// API: http://instagr.am/developer/auth/
	
	$InstagramClientID = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');
	$InstagramClientSecret = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');
	$InstagramRedirectURI = wpinstaroll_getInstagramRedirectURI();
	
	if (empty($InstagramClientID) || empty($InstagramClientSecret) || empty($InstagramRedirectURI))
		exit;
		
	$auth_code = $_GET['code'];
	
	if (empty($auth_code))
	{
		print('<p>&nbsp;<br />There was a problem requesting the authorization code:</p>');
		
		$error = $_GET['error'];
		$error_reason = $_GET['error_reason'];
		$error_description = $_GET['error_description'];
		if (!empty($error) && !empty($error_reason) && !empty($error_description))
			print('<p><strong>'.$error_description.'</strong></p>');
		
		exit;
	}
	
	// CURL POST request for getting the user access token from the code
	$request_uri = 'https://api.instagram.com/oauth/access_token';
	
	$data = array(	'client_id' => $InstagramClientID,
					'client_secret' => $InstagramClientSecret,
					'grant_type' => 'authorization_code',
					'redirect_uri' => $InstagramRedirectURI,
					'code' => $auth_code
					);
	
	$ch = curl_init($request_uri);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($ch);
	//echo curl_errno($ch);
	curl_close($ch);
	
	$decoded_response = json_decode($response);
	
	// get user data from the response
	$access_token = $decoded_response->access_token;
	$username = $decoded_response->user->username;
	$bio = $decoded_response->user->bio;
	$website = $decoded_response->user->website;
	$profile_picture = $decoded_response->user->profile_picture;
	//$full_name = $decoded_response->user->full_name;
	$id = $decoded_response->user->id;
	
	if (!empty($access_token))
	{
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken', $access_token);
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_username', $username);
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid', $id);
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_profilepicture', $profile_picture);
		
		// now we reload the main page and close the popup
		?>
		
		<script type="text/javascript">
			window.opener.location = window.opener.location;
			self.close();
		</script>
		
		<?php
	}
	else
		print('<p>There was a problem getting the required authorization!</p>');

	exit;
	
	// accessible with URL:
	// http://[HOST]/wp-admin/admin-ajax.php?action=wpinstaroll_redirect_uri
}
add_action('wp_ajax_wpinstaroll_redirect_uri', 'wpinstaroll_deal_with_instagram_auth_redirect_uri');


	// *** INSTAGRAM API ***
	
// gets Instagram stream for current logged user (contains pics sent by the user and his fiends),
// in case WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid' is not set to 'show_useronly',
// otherwise retrieves user stream (user photos only)
function wpinstaroll_getInstagramUserStream()
{
	$accessToken = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');

	if (empty($accessToken))
		return null;

	// API: http://instagr.am/developer/endpoints/users/
	//$file_contents = @file_get_contents(WP_ROLL_INSTAGRAM_USER_STREAM_URLBASE.$accessToken);

	$show_useronly = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_show_useronly_photos');
	if ($show_useronly != 'show_useronly') {
		$file_contents = @curl_file_get_contents(WP_ROLL_INSTAGRAM_USER_STREAM_URLBASE.$accessToken);
	} else {

		$instagram_user_id = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid');

		if (empty($instagram_user_id))
			return null;

		$file_contents = @curl_file_get_contents(WP_ROLL_INSTAGRAM_USER_PHOTOS_URL_A.$instagram_user_id.WP_ROLL_INSTAGRAM_USER_PHOTOS_URL_B.$accessToken);
	}

	if (empty($file_contents))
		return null;

	$photo_data = json_decode($file_contents);

	// add photo data (if new) to local db
	wpinstaroll_updateLocalDBWithNewPhotos($photo_data);

	return $photo_data;
}


// gets Instagram pics corresponding to passed hashtag (it only shows user photos, if corresponding option is set)
/*function wpinstaroll_getInstagramPhotosWithTag($tag)
{
	if (empty($tag))
		return null;

	$accessToken = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');

	if (empty($accessToken))
		return null;

	// API: http://instagr.am/developer/endpoints/tags/
	//$file_contents = file_get_contents(WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_A.$tag.WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_B.$accessToken);
	$file_contents = @curl_file_get_contents(WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_A.$tag.WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_B.$accessToken);

	if (empty($file_contents))
		return null;

	$photo_data = json_decode($file_contents);


	// in case the option is set, oly show photos with passed search tag by current user
	$show_useronly = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_show_useronly_photos_by_tag');
	if ($show_useronly === 'show_useronly_by_tag')
	{
		// function that checks against userid
		function test_id($test_var)
		{
			static $instagram_user_id = false;

			if (!$instagram_user_id)
				$instagram_user_id = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid');

			if (empty($instagram_user_id))
				return false;

			$id = $test_var->user->id;

			if ($id === $instagram_user_id)
				return true;
			else
				return false;
		}

		// filter the data array with the test function
		$photo_data_array = $photo_data->data;
		$photo_data->data = array_filter($photo_data_array, 'test_id');
	}
	// NOTE: improve the user photos filtering method for tag stream, calling the API for user photos and then filtering by tag

	
	// proceed as before with the filtered $photo_data
	// add photo data (if new) to local db
	wpinstaroll_updateLocalDBWithNewPhotos($photo_data);

	return $photo_data;
}*/
function wpinstaroll_getInstagramPhotosWithTag($tag)
{
	if (empty($tag))
		return null;

	$accessToken = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');

	if (empty($accessToken))
		return null;

	$show_useronly = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_show_useronly_photos_by_tag');

	if ($show_useronly != 'show_useronly_by_tag')
	{
		// all photos

		$file_contents = @curl_file_get_contents(WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_A.$tag.WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_B.$accessToken);

		if (empty($file_contents))
			return null;

		$photo_data = json_decode($file_contents);
	}
	else {

		// only user photos

		$instagram_user_id = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid');

		if (empty($instagram_user_id))
			return false;

		$file_contents = @curl_file_get_contents(WP_ROLL_INSTAGRAM_USER_PHOTOS_URL_A.$instagram_user_id.WP_ROLL_INSTAGRAM_USER_PHOTOS_URL_B.$accessToken);

		if (empty($file_contents))
			return null;

		$photo_data = json_decode($file_contents);

		// we filter the data, removing the ones without the specified tag
		$data = $photo_data->data;
		$new_data = array();
		foreach ($data as $element)
		{
			$tags = $element->tags;
			foreach ($tags as $single_tag)
			{
				if (trim($tag) == trim($single_tag))
				{
					$new_data[] = $element;
					break;
				}
			}
		}
		// replace the old data with the filtered version
		$photo_data->data = $new_data;
	}

	wpinstaroll_updateLocalDBWithNewPhotos($photo_data);

	return $photo_data;
}

// adds new photo data to the DB
function wpinstaroll_updateLocalDBWithNewPhotos($photo_data)
{ 
	if (!$photo_data)
		return 0;

	$added_pics_counter = 0;

	$photo_data = $photo_data->data;

	foreach ($photo_data as $element)
	{
		// add the photo to database - without setting the published status flag and the local media id
		$result = wpinstaroll_insertInstagramPhotoData($element->id, $element->created_time, $element->images->standard_resolution->url, $element->link);

		if ($result > 0)
			$added_pics_counter++;
	}

	return $added_pics_counter;
}


// create a new post from and Instagram photo
function wpinstaroll_createpostfromphoto($insta_id, $insta_url, $insta_link='', $insta_caption='', $insta_author_username='', $insta_author_id='')
{
	// mandatory parameters missing
	if (empty($insta_id) || empty($insta_url))
	{
		return array(
			'error' => true,
			'error_description' => WP_ROLL_INSTAGRAM_ERROR_MISSING_PARAMETERS_MESSAGE,
			'error_code' => WP_ROLL_INSTAGRAM_ERROR_MISSING_PARAMETERS_CODE
		);
	}
	

	/*
	// not actually needed, here!

	$search_tag = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag');
	if (empty($search_tag))
	{
		return array(
			'error' => true,
			'error_description' => WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_ACCESS_NOT_CONFIGURED_MESSAGE,
			'error_code' => WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_ACCESS_NOT_CONFIGURED_CODE
		);
	}
	*/

	$title_placeholder = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder');

	$category_for_post = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category');
	if (empty($category_for_post))
	{
			$category_for_post = 'Uncategorized';
			update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category', $category_for_post);
	}

	// a. if the category corresponding to the Instagram search tags
	// doesn't exist, we create it
	$cat_id = category_exists($category_for_post);
		if (!$cat_id)
			$cat_id = wp_create_category($category_for_post);
	
	
	// b. post creation
	$created_post_status = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status');
	if ($created_post_status != 'publish')
		$created_post_status = 'draft';

	$insert_photo_mode = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode');
	if ($insert_photo_mode !== 'featured')
		$insert_photo_mode = 'post_content';

	$post_args = array(
		'post_author' 	=> 0,		// with 0, current post author id is used
		'post_category'	=> array($cat_id),
		'post_content' 	=> $insta_caption,
		'post_status'	=> 'draft', 
		'post_title'	=> $title_placeholder,
		'post_type'		=> 'post' 
	);

	// add comma separated tags to post, if specified
	$tag_to_add_to_post = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_tag_to_add_to_posts');
	if (!empty($tag_to_add_to_post))
		$post_args['tags_input'] = $tag_to_add_to_post;
	// INSERT checks about correct format...

	$created_post_ID = wp_insert_post($post_args);
	
	if (!$created_post_ID)
	{
		return array(
			'error' => true,
			'error_description' => WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_POST_CREATION_PROBLEM_MESSAGE,
			'error_code' => WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_POST_CREATION_PROBLEM_CODE
		);
	}


	// c. add Instagram pic metadata to the just created post
	update_post_meta($created_post_ID, '_'.WP_ROLL_INSTAGRAM_PLUGIN_METADATA_PREFIX.'_insta_id', $insta_id);
	update_post_meta($created_post_ID, '_'.WP_ROLL_INSTAGRAM_PLUGIN_METADATA_PREFIX.'_insta_link', $insta_link);
	update_post_meta($created_post_ID, '_'.WP_ROLL_INSTAGRAM_PLUGIN_METADATA_PREFIX.'_insta_authorusername', $insta_author_username);
	update_post_meta($created_post_ID, '_'.WP_ROLL_INSTAGRAM_PLUGIN_METADATA_PREFIX.'_insta_authorid', $insta_author_id);	
	
	
	// d. download image from Instagram and associate to post
	$photo_data = wpinstaroll_getInstagramPhotoDataFromInstaID($insta_id);

		// if we the image is already inside the media library, we get it from there, without actually downloading it from Instagram
	$image_info = null;
	if ($photo_data && $photo_data->media_id)
	{
		$image_info = wp_get_attachment_image_src($photo_data->media_id, 'full');
	}

	if (!$image_info)
	{
		$tmp = download_url($insta_url);
	    $file_array = array(
	        'name' => basename($insta_url),
	        'tmp_name' => $tmp
	    );

	    if (is_wp_error($tmp))
		{
			@unlink($file_array['tmp_name']);

			// delete just created post
	    	wp_delete_post($created_post_ID, true);
			
			return array(
				'error' => true,
				'error_description' => WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_IMAGE_DOWNLOAD_PROBLEM_MESSAGE,
				'error_code' => WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_IMAGE_DOWNLOAD_PROBLEM_CODE
			);
	    }

	    $attach_id = media_handle_sideload($file_array, $created_post_ID);
	    	// see: what in case the same media file is added to multiple posts (as post content, but also as featured image)
	    	// http://core.trac.wordpress.org/browser/tags/3.3.2/wp-admin/includes/media.php (media_handle_sideload() code)
	    	// http://www.trovster.com/blog/2011/07/wordpress-custom-file-upload

	    if (is_wp_error($attach_id))
		{
			// remove uploaded temporary file
	        @unlink($file_array['tmp_name']);

	        // delete just created post
	        wp_delete_post($created_post_ID, true);
	        
			return array(
				'error' => true,
				'error_description' => WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_IMAGE_ADD_TO_POST_PROBLEM_MESSAGE,
				'error_code' => WP_ROLL_INSTAGRAM_ERROR_INSTAGRAM_IMAGE_ADD_TO_POST_PROBLEM_CODE
			);
	    }
		
		@unlink($file_array['tmp_name']);
	}
	else
		$attach_id = $photo_data->media_id;


	// update Instagram photo local data (better doing it as soon as we are sure we have the image, so
	// if next scheduled event occurs and the same image is present, it is less likely to be added again)
	wpinstaroll_updateInstagramPhotoStatus($insta_id, true, $attach_id);
		// SEE: another solution could be: update the status as soon as we get the image id and, only in case
		// there are problems getting the image and put it inside the media library, we set the status back to
		// non-published

	
	if ($insert_photo_mode === 'featured')
	{
		// attach to image as featured image (post thumbnail)
		add_post_meta($created_post_ID, '_thumbnail_id', $attach_id, true);
	}
	else {

		if (!$image_info)
			$image_info = wp_get_attachment_image_src($attach_id, 'full');

		// insert the image inside the post, followed by post caption
		$update_post_data = array();
  		$update_post_data['ID'] = $created_post_ID;
  		$update_post_data['post_content'] = '<img src="'.$image_info[0].'" alt="'.strip_tags($insta_caption).'" width="'.$image_info[1].'" height="'.$image_info[2].'"/><br/>'.
  											$insta_caption;

  		wp_update_post($update_post_data);
	}

	// the post is always created as draft and, if after post creation the image could actually be added and settings say the
	// post must be directly published, it is moved from 'draft' to 'published'
	if ($created_post_status == 'publish')
	{
		$update_post_data = array();
  		$update_post_data['ID'] = $created_post_ID;
  		$update_post_data['post_status'] = 'publish';
  		wp_update_post($update_post_data);
	}

	return array(
		'error' => false,
		'post_id' => $created_post_ID
	);
}


// this is the function called for sheduled automatic post creation from Instagram photos
function wpinstaroll_automatic_post_creation()
{
	// check for plugin requirements (without echoing error messages, just logging them)
	if (!wpinstaroll_check_requirements())
		wp_die('');

	$InstagramClientID = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');
	$InstagramClientSecret = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');
	$user_access_token = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');
	$search_tag = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag');

	$scheduled_publication_period = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period');
	$scheduled_publication_stream = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream');
	
	// is Instagram properly configured?
	//
	// if not, first reset event scheduling settings and removes event schedulation, then simply exits
	if (empty($InstagramClientID) || empty($InstagramClientSecret) || empty($user_access_token) ||
		empty($scheduled_publication_period) || empty($scheduled_publication_stream))
	{
		wpinstaroll_remove_scheduled_event();
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period', 'never');

		exit;
	}

		// inclusion of functions included only when opening WordPress backend
	require_once('wp-admin/includes/admin.php');

	// force current user to user with id == 1 (that should be an admin)
	wp_set_current_user(1);

	// retrieval of photos and post creation for new ones

		// user stream
	if ($scheduled_publication_stream == 'user' || $scheduled_publication_stream == 'user_tag')
	{
		$photoStream = wpinstaroll_getInstagramUserStream();

		$data = $photoStream->data;

		if ($data)
		{
			// reverse the array, so that oldest photos are processed first
			$data = array_reverse($data);

			// ids of already published photos
			$published_ids = wpinstaroll_getInstagramPublishedPhotosIDs();
					
			// scan the stream and publish new photos			
			foreach ($data as $element)
			{
				// if the photo has not been published yet
				if (!in_array($element->id, $published_ids))
				{
					wpinstaroll_createpostfromphoto($element->id,
													$element->images->standard_resolution->url,
													$element->link,
													$element->caption->text,
													$element->user->username,
													$element->user->id);
				}
			}
		}
	}

		// tag stream - only in this case, we check for search tag presence
	if (($scheduled_publication_stream == 'tag' || $scheduled_publication_stream == 'user_tag') && !empty($search_tag))
	{
		$photoStream = wpinstaroll_getInstagramPhotosWithTag($search_tag);

		$data = $photoStream->data;

		if ($data)
		{
			$data = array_reverse($data);

			$published_ids = wpinstaroll_getInstagramPublishedPhotosIDs();

			foreach ($data as $element)
			{
				if (!in_array($element->id, $published_ids))
				{
					wpinstaroll_createpostfromphoto($element->id,
													$element->images->standard_resolution->url,
													$element->link,
													$element->caption->text,
													$element->user->username,
													$element->user->id);
				}
			}
		}
	}
}

?>