<?php

// plugin admin panel


function wpinstaroll_getInstagramGeneratedDraftPosts()
{
	$category_for_post = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category');
	if (empty($category_for_post))
	{
			$category_for_post = 'Uncategorized';
			update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category', $category_for_post);
	}
	
	$cat_id = category_exists($category_for_post);
	if ($cat_id)
	{
		$category = get_category($cat_id);
		$category_slug = $category->slug;
		
		return get_bloginfo('wpurl').'/wp-admin/edit.php?post_status=draft&post_type=post&category_name='.$category_slug;
	}
	else
		return get_bloginfo('wpurl').'/wp-admin/edit.php?post_status=draft&post_type=post';	
}


// Settings menu
$wpinstaroll_page_title 							= WP_ROLL_INSTAGRAM_SETTINGS_PAGE_TITLE;
$wpinstaroll_menu_title 							= WP_ROLL_INSTAGRAM_SETTINGS_MENU;

// top level menu for photo selection
$wpinstaroll_photo_selection_page_title 			= WP_ROLL_INSTAGRAM_PHOTOS_PAGE_TITLE;
$wpinstaroll_photo_selection_menu_title 			= WP_ROLL_INSTAGRAM_PHOTOS_MENU;

// admin panel menu
function wpinstaroll_menu()
{
	global $wpinstaroll_page_title, $wpinstaroll_menu_title, $wpinstaroll_photo_selection_page_title, $wpinstaroll_photo_selection_menu_title;
	
	
		// options page
		
	// page title, menu title, access level required for user to access the page, unique menu slug,
	// optional callback function to call for drawing the page
	add_options_page($wpinstaroll_page_title, $wpinstaroll_menu_title, 'administrator', 'wpinstaroll_menu', 'wpinstaroll_panel_draw');
	
	// callback function for registering the settings
	add_action('admin_init', 'wpinstaroll_register_settings');
	
	
		// top level menu for photo selection
	
	$photo_selection_menu_icon_url = plugins_url(WP_ROLL_INSTAGRAM_PLUGIN_BASE_DIR.'/images/menuicon.png');
	add_menu_page($wpinstaroll_photo_selection_page_title, $wpinstaroll_photo_selection_menu_title, 'administrator', 'wpinstaroll_menu_photo_selection', 'wpinstaroll_photo_selection_panel_draw', $photo_selection_menu_icon_url);
}
add_action('admin_menu', 'wpinstaroll_menu');


// admin panel settings callback function
function wpinstaroll_register_settings()
{
	// Instagram App ID
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');

	// Instagram App Secret
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');
	
	// category to use for post created from Instagram photos
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category');

	// Instagram selected research hashtag
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag');
	
	// Instagram created post title placeholder
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder');

	// Instagram created post status
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status');

	// Instagram photo insertion mode (as featured image or inside the post)
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode');

	// show or don't show already published photos in photos selection panels (editable in photos selection panels)
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_show_published_photos');
	// show only user photos for user stream photo panel
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_show_useronly_photos');
	// show only user photos for tag stream photo panel
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_show_useronly_photos_by_tag');


	// tag to be added WordPress posts created from Instagram photos
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_tag_to_add_to_posts');

	// period for automatic post creation from Instagram photos
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period');

	// stream to use for automatic post creation from Instagram photos
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream');

	
		// (not showed and/or not directly editable)
	// Instagram Authorized User Access Token
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');
	// username
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_username');
	// userid
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid');
	// profile picture
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_profilepicture');
}


// draws the ADMIN panel page
function wpinstaroll_panel_draw()
{
	global $wpinstaroll_page_title;


	// check for plugin requirements
	if (!wpinstaroll_check_requirements(true))
		wp_die('');

	
	// not the requested access level
	if (!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	
	// fields updated, if the save button was pressed
	settings_fields(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group');
	
	
	$app_id = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');
	$app_secret = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');
				
	$user_access_token = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken');
	$username = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_username');
	$id = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid');
	$profile_picture = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_profilepicture');
	
	$search_tag = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag');

	$tag_to_add_to_posts = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_tag_to_add_to_posts');


	$category_for_post = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category');
	if (empty($category_for_post))
	{
			$category_for_post = 'Uncategorized';
			update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category', $category_for_post);
	}

	$title_placeholder = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder');

	$created_post_status = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status');
	if ($created_post_status == false)
	{
		$created_post_status = 'draft';
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status', $created_post_status);
	}

	$insert_photo_mode = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode');
	if ($insert_photo_mode == false)
	{
		$insert_photo_mode = 'post_content';
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode', $insert_photo_mode);
	}

	$scheduled_publication_period = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period');
	if ($scheduled_publication_period == false)
	{
		$scheduled_publication_period = 'never';
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period', $scheduled_publication_period);
	}

	$scheduled_publication_stream = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream');
	if ($scheduled_publication_stream == false)
	{
		$scheduled_publication_stream = 'user';
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream', $scheduled_publication_stream);
	}
		
		
	$accessTokenInvalid = false;
		
	// is a user_access_token set?	
	if (empty($user_access_token))
		$accessTokenInvalid = true;
		
	// Instagram App ID updated
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id']) &&
		$_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id'] != $app_id)
	{
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id', $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id']);
		$app_id = $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id'];
		
		$accessTokenInvalid = true;
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken', '');
		
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_username', '');
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid', '');
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_profilepicture', '');
	}
	
	// Instagram App Secret updated
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret']) &&
		$_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret'] != $app_secret)
	{
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret', $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret']);
		$app_secret = $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret'];
		
		$accessTokenInvalid = true;
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken', '');
		
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_username', '');
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid', '');
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_profilepicture', '');
	}
	
	
	// user pressed 'Change user' button
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_disconnect']) && $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_disconnect'] === 'yes')
	{
		$accessTokenInvalid = true;
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_accesstoken', '');
		
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_username', '');
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_userid', '');
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_user_profilepicture', '');
	}
	

	// post category updated
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category']) &&
		trim($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category']) != $category_for_post)
	{
		$category_for_post = trim($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category']);

		if (empty($category_for_post))
			$category_for_post = 'Uncategorized';

		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category', $category_for_post);

		// if it doesn't exist yet, we don't create the category: it is actually created with first
		// post creation
	}
	// see: should remove strange characters

	
	// seach tag updated
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag']) &&
		trim($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag']) != $search_tag)
	{
		$search_tag = trim($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag']);

		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag', $search_tag);
	}
	// see: should remove strange characters


	// tag to add to posts updated
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_tag_to_add_to_posts']) &&
		trim($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_tag_to_add_to_posts']) != $tag_to_add_to_posts)
	{
		$tag_to_add_to_posts = trim($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_tag_to_add_to_posts']);

		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_tag_to_add_to_posts', $tag_to_add_to_posts);
	}
	// see: should remove strange characters, check for correct comma separation, and so on...
	
	
	// post title placeholder updated ('Instagram picture' is used if empty)
	$default_instagram_title_placeholder = WP_ROLL_INSTAGRAM_DEFAULT_TITLE_PLACEHOLDER;
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder']) &&
		$_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder'] != $title_placeholder)
	{
		if (empty($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder']))
			$placeholder = $default_instagram_title_placeholder;
		else
			$placeholder = $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder'];
		
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder', $placeholder);
		$title_placeholder = $placeholder;
	}
	else {
		
		$current_placeholder = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder');
		
		if (empty($current_placeholder))
		{
			update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder', $default_instagram_title_placeholder);
			$title_placeholder = $default_instagram_title_placeholder;
		}
	}

	// post status for Instagram-based created posts updated
	$default_post_status = 'draft';
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status']) &&
		$_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status'] != $created_post_status)
	{
		if (empty($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status']))
			$created_post_status = $default_post_status;
		else
			$created_post_status = $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status'];

		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status', $created_post_status);
	}


	// photo insertion mode updated
	$default_insertion_mode = 'post_content';
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode']) &&
		$_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode'] != $insert_photo_mode)
	{
		if (empty($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode']))
			$insert_photo_mode = $default_insertion_mode;
		else
			$insert_photo_mode = $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode'];

		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode', $insert_photo_mode);
	}


	// automatic post creation sheduling period updated
	$default_scheduled_publication_period = 'never';
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period']) &&
		$_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period'] != $scheduled_publication_period)
	{
		if (empty($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period']))
			$scheduled_publication_period = $default_scheduled_publication_period;
		else
			$scheduled_publication_period = $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period'];

		if (!($scheduled_publication_period == 'never' ||
				$scheduled_publication_period == 'wpinstaroll_oneminute' ||
				$scheduled_publication_period == 'wpinstaroll_fiveminutes' ||
				$scheduled_publication_period == 'wpinstaroll_tenminutes' ||
				$scheduled_publication_period == 'wpinstaroll_twentynminutes' ||
				$scheduled_publication_period == 'wpinstaroll_twicehourly' ||
				$scheduled_publication_period == 'hourly' ||
				$scheduled_publication_period == 'twicedaily' ||
				$scheduled_publication_period == 'daily' ||
				$scheduled_publication_period == 'wpinstaroll_weekly' ||
				$scheduled_publication_period == 'wpinstaroll_monthly'))
			$scheduled_publication_period = 'never';

		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period', $scheduled_publication_period);

		// actually schedule the event
			// 1. remove current schedule (always)
		wpinstaroll_remove_scheduled_event();

			// 2. add new schedule (only for periods different than 'never')
		if ($scheduled_publication_period !== 'never')
			wpinstaroll_schedule_event($scheduled_publication_period);

		// see: possibility of setting the time for first activation
	}

	// automatic post creation sheduling stream updated
	$default_scheduled_publication_stream = 'user';
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream']) &&
		$_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream'] != $scheduled_publication_stream)
	{
		if (empty($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream']))
			$scheduled_publication_stream = $default_scheduled_publication_stream;
		else
			$scheduled_publication_stream = $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream'];

		if (!($scheduled_publication_stream == 'user' ||
				$scheduled_publication_stream == 'tag' ||
				$scheduled_publication_stream == 'user_tag'))
			$scheduled_publication_stream = 'user';

		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream', $scheduled_publication_stream);
	}

			
	// changes saved message
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_save_changes']) && $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_save_changes'] == 'yes')
		print('<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div>');
	?>
	
	
	<div class="wrap">
		
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2><?php echo $wpinstaroll_page_title; ?></h2>
		
		<form method="post" action="#">
			<input type="hidden" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_save_changes'; ?>" value="yes" />
		
			<div id="InstagramSettingsPanel">
			
				<h3>Instagram configuration</h3>
				<p><strong>You can set-up an Instagram application here: <a href="<?php echo WP_ROLL_INSTAGRAM_DEVELOPER_URL; ?>" target="_blank"><?php echo WP_ROLL_INSTAGRAM_DEVELOPER_URL; ?></a></strong><p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label>Instagram <em>Client ID</em></label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id')); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label>Instagram <em>Client Secret</em></label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret')); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label>Use this URL as <em>Callback/Redirect URL</em>, when registering <em>Instagram application</em></label>
							</th>
							<td>
								<label><strong><?php echo wpinstaroll_getInstagramRedirectURI(); ?></strong></label>
							</td>
						</tr>
					</tbody>
				</table>
						
				<p><strong>Instagram WordPress posts parameters</strong></p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label>Instagram <em>Post Title</em> Placeholder</label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder')); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label>Instagram <em>post category</em></label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category')); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label>Instagram <em>Search Tag</em> (without #)<br/><em>(optional)</em></label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag')); ?>" class="regular-text" />
							</td>
						</tr>	
						<tr>
							<th scope="row">
								<label>Post <em>status</em> for posts created from Instagram photos</label>
							</th>
							<td>
								<select name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status'; ?>">
                                    <option value="draft"<?php if ($created_post_status !== 'publish') echo ' selected=selected'; ?>>draft</option>
                                    <option value="publish"<?php if ($created_post_status === 'publish') echo ' selected=selected'; ?>>published</option>                          
                                </select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label>Photo <em>insertion mode</em> for posts</label>
							</th>
							<td>
								<select name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode'; ?>">
									<option value="post_content"<?php if ($insert_photo_mode !== 'featured') echo ' selected=selected'; ?>>in post content</option>
                                    <option value="featured"<?php if ($insert_photo_mode === 'featured') echo ' selected=selected'; ?>>as featured image</option>                     
                                </select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label><em>Tags</em> to add to WordPress <em>posts</em>, comma separated<br/><em>(optional)</em></label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_tag_to_add_to_posts'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_tag_to_add_to_posts')); ?>" class="regular-text" />
							</td>
						</tr>
					</tbody>
				</table>

				<p><strong>Automatic creation of posts from Instagram photos</strong></p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label><em>Automatically create</em> posts from Instagram new photos</label>
							</th>
							<td>
								<select name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_period'; ?>">
									<option value="never"<?php if ($scheduled_publication_period === 'never') echo ' selected=selected'; ?>>never</option>
									<option value="wpinstaroll_oneminute"<?php if ($scheduled_publication_period === 'wpinstaroll_oneminute') echo ' selected=selected'; ?>>every minute</option>
                                    <option value="wpinstaroll_fiveminutes"<?php if ($scheduled_publication_period === 'wpinstaroll_fiveminutes') echo ' selected=selected'; ?>>every 5 minutes</option>
                                    <option value="wpinstaroll_tenminutes"<?php if ($scheduled_publication_period === 'wpinstaroll_tenminutes') echo ' selected=selected'; ?>>every 10 minutes</option>
                                    <option value="wpinstaroll_twentynminutes"<?php if ($scheduled_publication_period === 'wpinstaroll_twentynminutes') echo ' selected=selected'; ?>>every 20 minutes</option>
                                    <option value="wpinstaroll_twicehourly"<?php if ($scheduled_publication_period === 'wpinstaroll_twicehourly') echo ' selected=selected'; ?>>every 30 minutes</option>
                                    <option value="hourly"<?php if ($scheduled_publication_period === 'hourly') echo ' selected=selected'; ?>>hourly</option>
                                    <option value="twicedaily"<?php if ($scheduled_publication_period === 'twicedaily') echo ' selected=selected'; ?>>twice a day</option>
                                    <option value="daily"<?php if ($scheduled_publication_period === 'daily') echo ' selected=selected'; ?>>daily</option>
                                    <option value="wpinstaroll_weekly"<?php if ($scheduled_publication_period === 'wpinstaroll_weekly') echo ' selected=selected'; ?>>weekly</option>
                                    <option value="wpinstaroll_monthly"<?php if ($scheduled_publication_period === 'wpinstaroll_monthly') echo ' selected=selected'; ?>>monthly</option>
                                </select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label><em>Instagram stream</em> for automatic post creation <em>(not used when period is set to "never")</em></label>
							</th>
							<td>
								<select name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_scheduled_publication_stream'; ?>">
									<option value="user"<?php if ($scheduled_publication_stream === 'user') echo ' selected=selected'; ?>>user stream</option>
									<option value="tag"<?php if ($scheduled_publication_stream === 'tag') echo ' selected=selected'; ?>>tag stream</option>
                                    <option value="user_tag"<?php if ($scheduled_publication_stream === 'user_tag') echo ' selected=selected'; ?>>user and tag streams</option>
                                </select>
							</td>
						</tr>
					</tbody>
				</table>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
				</p>
				
				
				<?php

					if ($accessTokenInvalid)
					{
						if (!empty($app_id) && !empty($app_secret))
						{
							/*
							can't use iframe, because of X-Frame-Options HTTP header sent by Instagram; used pop-up, instead
							*/
							?>
			
							<input type="button" class="button-primary" value="Instagram authorization" id="InstaAuthButton" />
							
							<script type="text/javascript">
								var InstagramAuthWindow = null;
								
								jQuery(document).ready(function() {

									jQuery('form').attr('action', '');
									
									jQuery('#InstaAuthButton').click(function() {
										
										InstagramAuthWindow = window.open('<?php print(wpinstaroll_getAuthorizationPageURI()); ?>', 'InstagramAuthorization', 'width=800,height=400');
									});
								});
							</script>

							<?php			
						}
						else
							print('<p><strong>You need to insert Instagram Client ID and Client Secret and then authorize the app after saving.</strong></p>');
							
							
						$showChangeUserButton = false;
					}
					else {

						// in case we already have used data saved, we use it

						print('<img class="profilePicture" src="'.$profile_picture.'" alt="'.$username.'" />');
						print('<p>username: '.$username.'<br />user id: '.$id.'</p>');
						
						$showChangeUserButton = true;
					}
				?>
				
			</div>
		</form>
		
		<?php
		
			if ($showChangeUserButton)
			{
				?>
				
				<form method="post" action="#">
					<input type="hidden" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_disconnect'; ?>" value="yes" />
					<input type="submit" class="button-primary" value="<?php _e('Disconnect from Instagram'); ?>" />
				</form>
				
				<?php
			}
		?>

		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('form').attr('action', '');
			});
		</script>
		
	</div>
	
	<?php
}


// draws the PHOTO SELECTION panel
function wpinstaroll_photo_selection_panel_draw()
{
	// check for plugin requirements
	if (!wpinstaroll_check_requirements(true))
		wp_die('');

	global $wpinstaroll_photo_selection_page_title;

	$category_for_post = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category');
	if (empty($category_for_post))
	{
			$category_for_post = 'Uncategorized';
			update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_category', $category_for_post);
	}
	$insta_post_title = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder');
	if (empty($insta_post_title))
	{
		$insta_post_title = WP_ROLL_INSTAGRAM_DEFAULT_TITLE_PLACEHOLDER;
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder', $insta_post_title);
	}
		
	
	// not the requested access level
	if (!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
		
	?>
	
	<script type="text/javascript">

		var WPInstaroll_ActivePanel = 'user';
			
			function UserPhotosActivatePanel()
			{
				if (WPInstaroll_ActivePanel == 'tag')
				{
					jQuery('#InstaTagPhotos').removeClass('nav-tab-active');
					jQuery('#InstaUserPhotos').addClass('nav-tab-active');
					
					jQuery('#InstagramTagPhotosPanel').css('display', 'none');
					jQuery('#InstagramUserPhotosPanel').css('display', 'block');
					
					WPInstaroll_ActivePanel = 'user';

					AJAXDrawUserPhotosTable();
				}
	
				return false;
			}
			function TagPhotosActivatePanel()
			{
				if (WPInstaroll_ActivePanel == 'user')
				{
					jQuery('#InstaUserPhotos').removeClass('nav-tab-active');
					jQuery('#InstaTagPhotos').addClass('nav-tab-active');
					
					jQuery('#InstagramUserPhotosPanel').css('display', 'none');
					jQuery('#InstagramTagPhotosPanel').css('display', 'block');
					
					WPInstaroll_ActivePanel = 'tag';

					AJAXDrawTagPhotosTable();
				}
				
				return false;
			}

		function AJAXDrawUserPhotosTable()
		{
			jQuery('#InstagramUserPhotosPanel').html('<p>loading...</p>');
				
			jQuery.ajax({
				url: ajaxurl + '?action=wpinstaroll_photosbyusertable',
				success: function(data) {
			    
					jQuery('#InstagramUserPhotosPanel').html(data);
				}
			});	
		}
	
		function AJAXDrawTagPhotosTable()
		{
			jQuery('#InstagramTagPhotosPanel').html('<p>loading...</p>');
				
			jQuery.ajax({
				url: ajaxurl + '?action=wpinstaroll_photosbytagtable',
				success: function(data) {
			    
					jQuery('#InstagramTagPhotosPanel').html(data);
				}
			});	
		}
		
		jQuery(document).ready(function() {
			
			// open first panel: user stream
			AJAXDrawUserPhotosTable();

		});
		
	</script>
	
	<div class="wrap">
		
		<div id="icon-options-general" class="icon32 instaroll"><br /></div>
		<h2><?php print($wpinstaroll_photo_selection_page_title); ?></h2>

		<h3 class="nav-tab-wrapper">
			<a id="InstaUserPhotos" class="nav-tab nav-tab-active" href="#" onclick="return UserPhotosActivatePanel()"><?php echo WP_ROLL_INSTAGRAM_PHOTOS_TABS_USER; ?></a>
			<a id="InstaTagPhotos" class="nav-tab" href="#" onclick="return TagPhotosActivatePanel()"><?php echo WP_ROLL_INSTAGRAM_PHOTOS_TABS_TAG; ?></a>
		</h3>

		<div id="InstagramUserPhotosPanel"></div>

		<div id="InstagramTagPhotosPanel" style="display: none"></div>

		<script type="text/javascript">

			jQuery('#Instagram_userphotosupdate').live('click', function() {
					
				AJAXDrawUserPhotosTable();
				
				return false;
			});

			jQuery('#Instagram_tagphotosupdate').live('click', function() {
					
				AJAXDrawTagPhotosTable();
				
				return false;
			});


			jQuery("#show_useronly_userpanel").live('click', function() {

				var status;
				if (jQuery(this).attr('checked') == 'checked')
					status = 'show_useronly';
				else
					status = 'show_userandfriends';

				jQuery.ajax({
					url: ajaxurl + '?action=set_instagram_show_useronly_flag',
					type: 'POST',
					data: {
						show: status,
					},
					success: function() {

						// reload the view after setting the flag
						AJAXDrawUserPhotosTable();	
					}
				});

				return true;
			});


			jQuery("#show_useronly_tagpanel").live('click', function() {

				var status;
				if (jQuery(this).attr('checked') == 'checked')
					status = 'show_useronly_by_tag';
				else
					status = 'show_all_by_tag';

				jQuery.ajax({
					url: ajaxurl + '?action=set_instagram_show_useronly_by_tag_flag',
					type: 'POST',
					data: {
						show: status,
					},
					success: function() {

						// reload the view after setting the flag
						AJAXDrawTagPhotosTable();	
					}
				});

				return true;
			});


			jQuery("#show_already_published_userpanel").live('click', function() {

				var status;
				if (jQuery(this).attr('checked') == 'checked')
					status = 'show_published';
				else
					status = 'dont_show_published';

				jQuery.ajax({
					url: ajaxurl + '?action=set_instagram_show_published_flag',
					type: 'POST',
					data: {
						show: status,
					},
					success: function() {

						// reload the view after setting the flag
						AJAXDrawUserPhotosTable();	
					}
				});

				return true;
			});

			jQuery("#show_already_published_tagpanel").live('click', function() {

				var status;
				if (jQuery(this).attr('checked') == 'checked')
					status = 'show_published';
				else
					status = 'dont_show_published';

				jQuery.ajax({
					url: ajaxurl + '?action=set_instagram_show_published_flag',
					type: 'POST',
					data: {
						show: status,
					},
					success: function() {

						// reload the view after setting the flag
						AJAXDrawTagPhotosTable();	
					}
				});

				return true;
			});

			jQuery('.<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX; ?>_createpost_action').live('click', function() {
				
				var t_id = jQuery(this).attr('id');
				var pic_id = t_id.substr('create_wp_post_'.length);

				
				var postCreationString = '<?php echo get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_created_post_status'); ?>';
				if (postCreationString != 'publish')
					postCreationString = 'saved as draft.\n\nYou will then be able to edit and actually publish the post.';
				else
					postCreationString = 'directly published.';

				var postCreationModeString = '<?php echo get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_insert_photo_mode'); ?>';
				if (postCreationModeString !== 'featured')
					postCreationModeString = '\n\nThe photo will be inserted into the created post.';
				else
					postCreationModeString = '\n\nThe photo will be added as featured image for created post.';
			
				if (!window.confirm('Do you want to create a post from the Instagram image with ID: ' + pic_id + '?' +
					'\n\nA new post will be created with category \"' + '<?php print($category_for_post); ?>\" and title \"' + '<?php print($insta_post_title); ?>' + '\", and will be ' + postCreationString + postCreationModeString))
					return false;
					
			
				jQuery.ajax({
					url: ajaxurl + '?action=create_post_from_instagram_pic',
					type: 'POST',
					dataType: 'json',
					data: {
						url: jQuery(this).closest('tr').find('.insta_image a img').attr('data-fullimageurl'),
						id: pic_id,
						link: jQuery(this).closest('tr').find('.insta_image a').attr('href'),
						caption: jQuery(this).closest('tr').find('.insta_description').html(),
						author_username: jQuery(this).closest('tr').find('.insta_username').html(),
						author_id: jQuery(this).closest('tr').find('.insta_userid').html()
					},
					success: function(data) {
						
						if (data.error == false)
						{
							alert('Post successfully created, width ID: ' + data.post_id);
						}
						else
							alert('There was a problem creating the post.\n\nReason: ' + data.error_description);	
					}
				});
			
				return false;
			});

		</script>
	
	</div>
	
	<?php
}

?>