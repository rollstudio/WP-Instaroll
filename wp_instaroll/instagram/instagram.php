<?php

// Instagram API


	// *** INSTAGRAM AUTHENTICATION ***

// Instagram redirect URI: where to go after user app authorization
function getInstagramRedirectURI()
{
	return get_bloginfo('wpurl').'/wp-admin/admin-ajax.php?action='.WP_ROLL_INSTAGRAM_PLUGIN_CALLBACK_ACTION;
}


// gest Instagram login/authorization page URI
function getAuthorizationPageURI()
{
	$InstagramClientID = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');
	$InstagramClientSecret = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');
	$InstagramRedirectURI = getInstagramRedirectURI();
	
	if (empty($InstagramClientID) || empty($InstagramClientSecret) || empty($InstagramRedirectURI))
		return null;
	
	// API: http://instagr.am/developer/auth/
	return 'https://api.instagram.com/oauth/authorize/?client_id='.$InstagramClientID.'&redirect_uri='.urlencode($InstagramRedirectURI).'&response_type=code';
}


// handler for Integram redirect URI
function deal_with_instagram_auth_redirect_uri()
{
	// API: http://instagr.am/developer/auth/
	
	$InstagramClientID = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');
	$InstagramClientSecret = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');
	$InstagramRedirectURI = getInstagramRedirectURI();
	
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
//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($ch);
	echo curl_errno($ch);
	curl_close($ch);
	

	$decoded_response = json_decode($response);
	
	// get user data from the response
	$access_token = $decoded_response->access_token;
	$username = $decoded_response->user->username;
	$bio = $decoded_response->user->bio;
	$website = $decoded_response->user->website;
	$profile_picture = $decoded_response->user->profile_picture;
	$full_name = $decoded_response->user->full_name;
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
add_action('wp_ajax_wpinstaroll_redirect_uri', 'deal_with_instagram_auth_redirect_uri');


	// *** INSTAGRAM API ***
	
// gets Instagram stream for current logged user (contains pics sent by the user and his fiends)
function getInstagramUserStream()
{
	$accessToken = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');

	if (empty($accessToken))
		return null;

	// API: http://instagr.am/developer/endpoints/users/
	$file_contents = @file_get_contents(WP_ROLL_INSTAGRAM_USER_STREAM_URLBASE.$accessToken);

	if (empty($file_contents))
		return null;

	$photo_data = json_decode($file_contents);

	// add photo data (if new) to local db
	updateLocalDBWithNewPhotos($photo_data);

	return $photo_data;
}


// gets Instagram pics corresponding to passed hashtag
function getInstagramPhotosWithTag($tag)
{
	if (empty($tag))
		return null;
	
	$accessToken = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');
	
	if (empty($accessToken))
		return null;
			
	// API: http://instagr.am/developer/endpoints/tags/
	$file_contents = file_get_contents(WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_A.$tag.WP_ROLL_INSTAGRAM_STREAM_BYTAG_URL_B.$accessToken);
	
	if (empty($file_contents))
		return null;
		
	$photo_data = json_decode($file_contents);

	// add photo data (if new) to local db
	updateLocalDBWithNewPhotos($photo_data);

	return $photo_data;
}


// adds new photo data to the DB
function updateLocalDBWithNewPhotos($photo_data)
{ 
	if (!$photo_data)
		return 0;

	$added_pic_counter = 0;

	$photo_data = $photo_data->data;

	foreach ($photo_data as $element)
	{
		// add the photo to database - without setting the published status flag and the local media id
		insertInstagramPhotoData($element->id, $element->images->standard_resolution->url, $element->link);
	}
}

?>
