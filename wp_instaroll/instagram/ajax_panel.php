<?php

// plugin AJAX handlers


// AJAX handler for Instagram user stream
function wpinstaroll_photosbyusertable_ayax()
{
	$app_id = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');
	$app_secret = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');			
	$user_access_token = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');
	$search_tag = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag');
	$instagram_username = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_username');
	
	if (empty($app_id) || empty($app_secret) || empty($user_access_token) || empty($search_tag))
	{					
		$instagram_settings_page = get_bloginfo('wpurl').'/wp-admin/options-general.php?page='.WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_menu';
		
		print('<p><strong>You need to  configure Instagram access from the <a href="'.$instagram_settings_page.'">Instagram Settings</a> panel inside the Settings menu.</strong></p>');
	}
	else {

		print('<h3>Instagram stream for user: '.$instagram_username.'</h3>');
		
		print(	'<p><a class="button-primary" href="'.wpinstaroll_getInstagramGeneratedDraftPosts().'">Go to Instagram draft posts</a>'.
				'<span class="top_right_buttons"><span class="show_published_pics_check">Show already selected Instagram photos&nbsp;<input type="checkbox" name="show_already_published" value="yes" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>'.
				'<a class="button-primary" id="Instagram_userphotosupdate" href="#">Update view</a></span></p>');

		$user_feed = wpinstaroll_getInstagramUserStream();
		
		if ($user_feed)
		{
			?>
			<div id="InstagramPhotosTable">
				
				<table class="wp-list-table widefat fixed posts">
					<thead>
						<tr>
							<th style="width: 350px;">Picture</th>
							<th style="width: 140px;">ID</th>
							<th style="width: 210px;">Tags</th>
							<th style="width: 80px;">Likes</th>
							<th style="width: 90px;">Comments</th>
							<th>Caption</th>
							<th style="width: 130px;">Author username</th>
							<th style="width: 80px;">Author ID</th>
							<th style="width: 100px;">Action</th>
						</tr>
					</thead>
					<tbody>
				<?php
								
					$data = $user_feed->data;
							
					foreach ($data as $element)
					{
						print('<tr class="alternate author-self status-publish format-default iedit">');
							print('<td class="insta_image"><a href="'.$element->link.'" target="_blank"><img src="'.$element->images->low_resolution->url.'" alt="" data-fullimageurl="'.$element->images->standard_resolution->url.'" /></a></td>');
							print('<td class="insta_id">'.$element->id.'<br />&nbsp;</td>');
							
							$tags_string = '';
							$tags = $element->tags;
							$tags_counter = 0;
							foreach ($tags as $tag)
							{
								if ($tags_counter++ > 0)
									$tags_string .= ', ';
									
								$tags_string .= $tag;
							}
								
							print('<td class="insta_tags">'.$tags_string.'<br />&nbsp;</td>');
							
							print('<td class="insta_likes_count">'.$element->likes->count.'<br />&nbsp;</td>');
							print('<td class="insta_comments_count">'.$element->comments->count.'<br />&nbsp;</td>');
							print('<td class="insta_description">'.$element->caption->text.'<br />&nbsp;</td>');
							print('<td class="insta_username">'.$element->user->username.'<br />&nbsp;</td>');
							print('<td class="insta_userid">'.$element->user->id.'<br />&nbsp;</td>');
							print('<td class="insta_createpost"><a href="#" id="create_wp_post_'.$element->id.'" class="button-secondary '.WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_createpost_action">Create post</a></td>');
						print('</tr>');
					}
				?>
					</tbody>
				</table>
			</div>
			
			<?php
		}
	}

	exit;
	
	// accessible with URL:
	// http://[HOST]/wp-admin/admin-ajax.php?action=wpinstaroll_photosbytagtable
}
add_action('wp_ajax_wpinstaroll_photosbyusertable', 'wpinstaroll_photosbyusertable_ayax');


// AJAX handler for Instagram tag stream
function wpinstaroll_photosbytagtable_ayax()
{
	$app_id = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');
	$app_secret = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');			
	$user_access_token = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');
	$search_tag = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag');
	
	if (empty($app_id) || empty($app_secret) || empty($user_access_token) || empty($search_tag))
	{					
		$instagram_settings_page = get_bloginfo('wpurl').'/wp-admin/options-general.php?page='.WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_menu';
		
		print('<p><strong>You need to  configure Instagram access from the <a href="'.$instagram_settings_page.'">Instagram Settings</a> panel inside the Settings menu.</strong></p>');
	}
	else {
		
		print('<h3>Instagram tag: '.$search_tag.'</h3>');
		
		print(	'<p><a class="button-primary" href="'.wpinstaroll_getInstagramGeneratedDraftPosts().'">Go to Instagram draft posts</a>'.
				'<span class="top_right_buttons"><span class="show_published_pics_check">Show already selected Instagram photos&nbsp;<input type="checkbox" name="show_already_published" value="yes" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>'.
				'<a class="button-primary" id="Instagram_tagphotosupdate" href="#">Update view</a></span></p>');

		$tag_feed = wpinstaroll_getInstagramPhotosWithTag($search_tag);
		
		if ($tag_feed)
		{
			?>
			<div id="InstagramPhotosTable">
				
				<table class="wp-list-table widefat fixed posts">
					<thead>
						<tr>
							<th style="width: 350px;">Picture</th>
							<th style="width: 140px;">ID</th>
							<th style="width: 210px;">Tags</th>
							<th style="width: 80px;">Likes</th>
							<th style="width: 90px;">Comments</th>
							<th>Caption</th>
							<th style="width: 130px;">Author username</th>
							<th style="width: 80px;">Author ID</th>
							<th style="width: 100px;">Action</th>
						</tr>
					</thead>
					<tbody>
				<?php
								
					$data = $tag_feed->data;
							
					foreach ($data as $element)
					{
						print('<tr class="alternate author-self status-publish format-default iedit">');
							print('<td class="insta_image"><a href="'.$element->link.'" target="_blank"><img src="'.$element->images->low_resolution->url.'" alt="" data-fullimageurl="'.$element->images->standard_resolution->url.'" /></a></td>');
							print('<td class="insta_id">'.$element->id.'<br />&nbsp;</td>');
							
							$tags_string = '';
							$tags = $element->tags;
							$tags_counter = 0;
							foreach ($tags as $tag)
							{
								if ($tags_counter++ > 0)
									$tags_string .= ', ';
									
								$tags_string .= $tag;
							}
								
							print('<td class="insta_tags">'.$tags_string.'<br />&nbsp;</td>');
							
							print('<td class="insta_likes_count">'.$element->likes->count.'<br />&nbsp;</td>');
							print('<td class="insta_comments_count">'.$element->comments->count.'<br />&nbsp;</td>');
							print('<td class="insta_description">'.$element->caption->text.'<br />&nbsp;</td>');
							print('<td class="insta_username">'.$element->user->username.'<br />&nbsp;</td>');
							print('<td class="insta_userid">'.$element->user->id.'<br />&nbsp;</td>');
							print('<td class="insta_createpost"><a href="#" id="create_wp_post_'.$element->id.'" class="button-secondary '.WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_createpost_action">Create post</a></td>');
						print('</tr>');
					}
				?>
					</tbody>
				</table>
			</div>
			
			<?php
		}
	}

	exit;
	
	// accessible with URL:
	// http://[HOST]/wp-admin/admin-ajax.php?action=wpinstaroll_photosbytagtable
}
add_action('wp_ajax_wpinstaroll_photosbytagtable', 'wpinstaroll_photosbytagtable_ayax');


// handler for creating a post from Instagram pic
function wpinstaroll_createpostfromphoto_ayax()
{
	$response = '';

	if (!isset($_POST['id']) || !isset($_POST['url']) || !isset($_POST['link']))
	{
		$response = array(
			'error' => true,
			'error_description' => WP_ROLL_INSTAGRAM_ERROR_MISSING_PARAMETERS_MESSAGE,
			'error_code' => WP_ROLL_INSTAGRAM_ERROR_MISSING_PARAMETERS_CODE
		);
	}
	else
		$response = wpinstaroll_createpostfromphoto($_POST['id'], $_POST['url'], $_POST['link'], $_POST['caption'], $_POST['author_username'], $_POST['author_id']);

	echo json_encode($response);
		
	exit;
	
	// accessible with URL:
	// http://[HOST]/wp-admin/admin-ajax.php?action=create_post_from_instagram_pic
}
add_action('wp_ajax_create_post_from_instagram_pic', 'wpinstaroll_createpostfromphoto_ayax');


?>