<?php

	// *** INSTAGRAM AUTHENTICATION ***

// Instagram redirect URI: where to go after user app authorization
function getInstagramRedirectURI()
{
	return get_bloginfo('wpurl').'/wp-admin/admin-ajax.php?action=wpinstapost_redirect_uri';
}


// gest Instagram login/authorization page URI
function getAuthorizationPageURI()
{
	$InstagramClientID = get_option('wpinstapost_instagram_app_id');
	$InstagramClientSecret = get_option('wpinstapost_instagram_app_secret');
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
	
	$InstagramClientID = get_option('wpinstapost_instagram_app_id');
	$InstagramClientSecret = get_option('wpinstapost_instagram_app_secret');
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
	$response = curl_exec($ch);
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
		update_option('wpinstapost_instagram_user_accesstoken', $access_token);
		update_option('wpinstapost_instagram_user_username', $username);
		update_option('wpinstapost_instagram_user_userid', $id);
		update_option('wpinstapost_instagram_user_profilepicture', $profile_picture);
		
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
	// http://[HOST]/wp-admin/admin-ajax.php?action=wpinstapost_redirect_uri
}
add_action('wp_ajax_wpinstapost_redirect_uri', 'deal_with_instagram_auth_redirect_uri');



	// *** INSTAGRAM API ***
	
// gets Instagram stream for current logged user (contains pics sent by the user and his fiends)
function getInstagramUserStream()
{
	$accessToken = get_option('wpinstapost_instagram_user_accesstoken');

	if (empty($accessToken))
		return null;

	// API: http://instagr.am/developer/endpoints/users/
	$file_contents = @file_get_contents('https://api.instagram.com/v1/users/self/feed?access_token='.$accessToken);

	if (empty($file_contents))
		return null;

	return json_decode($file_contents);
}


// gets Instagram pics corresponding to passed hashtag
function getInstagramPhotosWithTag($tag)
{
	if (empty($tag))
		return null;
	
	$accessToken = get_option('wpinstapost_instagram_user_accesstoken');
	
	if (empty($accessToken))
		return null;
			
	// API: http://instagr.am/developer/endpoints/tags/
	$file_contents = file_get_contents('https://api.instagram.com/v1/tags/'.$tag.'/media/recent?access_token='.$accessToken);
	
	if (empty($file_contents))
		return null;
		
	return json_decode($file_contents);
}

?>