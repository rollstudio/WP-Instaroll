<?php

// plugin admin panel


function getInstagramGeneratedDraftPosts()
{
	$search_tag = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag');
	if (empty($search_tag))
		return null;
		
	$category_name = '#'.$search_tag;
	$cat_id = category_exists($category_name);
	if ($cat_id)
	{
		$category = get_category($cat_id);
		$category_slug = $category->slug;
		
		return get_bloginfo('wpurl').'/wp-admin/edit.php?post_status=draft&post_type=post&category_name='.$category_slug;
	}
	else
		return get_bloginfo('wpurl').'/wp-admin/edit.php?post_status=draft&post_type=post';	
}


// admin panel menu
add_action('admin_menu', 'wpinstaroll_menu');


// Settings menu
$wpinstaroll_page_title 							= WP_ROLL_INSTAGRAM_SETTINGS_PAGE_TITLE;
$wpinstaroll_menu_title 							= WP_ROLL_INSTAGRAM_SETTINGS_MENU;

// top level menu for photo selection
$wpinstaroll_photo_selection_page_title 			= WP_ROLL_INSTAGRAM_PHOTOS_PAGE_TITLE;
$wpinstaroll_photo_selection_menu_title 			= WP_ROLL_INSTAGRAM_PHOTOS_MENU;

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
	
	add_menu_page($wpinstaroll_photo_selection_page_title, $wpinstaroll_photo_selection_menu_title, 'administrator', 'wpinstaroll_menu_photo_selection', 'wpinstaroll_photo_selection_panel_draw');
}


// admin panel settings callback function
function wpinstaroll_register_settings()
{
	// Instagram App ID
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id');
	// Instagram App Secret
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret');
	
	// Instagram selected research hashtag
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag');
	
	// Instagram created post title placeholder
	register_setting(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'-settings-group', WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder');
	
	
		//(not showed and not directly editable)
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
	$title_placeholder = get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder');	
		
		
	$accessTokenInvalid = false;
		
	// is a user_access_token set?	
	if (empty($user_access_token))
		$accessTokenInvalid = true;;
		
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
	
	
	// seach tag updated
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag']) &&
		$_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag'] != $search_tag)
	{
		update_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag', $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag']);
		$search_tag = $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag'];
	}
	
	
	// post title placeholder updated ('Instagram picture' is used if empty)
	$default_instagram_title_placeholder = 'Instagram picture';
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
			
	// changes saved message
	if (isset($_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_save_changes']) && $_POST[WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_save_changes'] == 'yes')
		print('<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div>');
	?>
	
	
	<div class="wrap">
		
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2><?php echo $wpinstaroll_page_title; ?></h2>
		
		<form method="post" action="">
			<input type="hidden" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_save_changes'; ?>" value="yes" />
		
			<div id="InstagramSettingsPanel">
			
				<h3>Instagram configuration</h3>
				<p>You can set-up an Instagram application here: <a href="<?php echo WP_ROLL_INSTAGRAM_DEVELOPER_URL; ?>" target="_blank"><?php echo WP_ROLL_INSTAGRAM_DEVELOPER_URL; ?></a><p>
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label>Instagram <em>Client ID</em></label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_id')); ?>" class="regular-text" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label>Instagram <em>Client Secret</em></label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_app_secret')); ?>" class="regular-text" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label>Use this URL as <em>Callback/Redirect URL</em>, when registering <em>Instagram application</em></label>
							</th>
							<td>
								<label><strong><?php echo getInstagramRedirectURI(); ?></strong></label>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">
								<label>Instagram <em>Search Tag</em> (without #)</label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_search_tag')); ?>" class="regular-text" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label>Instagram Post Title Placeholder</label>
							</th>
							<td>
								<input type="text" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder'; ?>" value="<?php print(get_option(WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_instagram_post_title_placeholder')); ?>" class="regular-text" />
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
							can't use iframe, because of X-Frame-Options HTTP header sent by Instagram
							<iframe id="InstagramAuthiFrame" src="<?php print(getAuthorizationPageURI()); ?>" width="100%" height="350" frameborder="0" scrolling="no" style="padding: 0; margin: 0;"></iframe>
							*/
							?>
			
							<input type="button" class="button-primary" value="Instagram authorization" id="InstaAuthButton" />
							
							<script type="text/javascript">
								var InstagramAuthWindow = null;
								
								jQuery(document).ready(function() {
									
									jQuery('#InstaAuthButton').click(function() {
										
										InstagramAuthWindow = window.open('<?php print(getAuthorizationPageURI()); ?>', 'InstagramAuthorization', 'width=800,height=400');
									
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

						print('<img src="'.$profile_picture.'" alt="'.$username.'" />');
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
				
				<form method="post" action="">
						<input type="hidden" name="<?php echo WP_ROLL_INSTAGRAM_PLUGIN_PREFIX.'_disconnect'; ?>" value="yes" />
						<input type="submit" class="button-primary" value="<?php _e('Disconnect from Instagram'); ?>" />
				</form>
				
				<?php
			}
		?>
		
	</div>
	
	<?php
}


// draws the PHOTO SELECTION panel
function wpinstaroll_photo_selection_panel_draw()
{
	global $wpinstaroll_photo_selection_page_title;
	
	// not the requested access level
	if (!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
		
	?>
	
	<script type="text/javascript">

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
		
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2><?php print($wpinstaroll_photo_selection_page_title); ?></h2>

		<h3 class="nav-tab-wrapper">
			<a id="KeenSCtabSettings" class="nav-tab nav-tab-active" href="#" onclick="return KeenAdminSettingsActivatePanel()">Instagram User Stream</a>
			<a id="KeenSCtabUsers" class="nav-tab" href="#" onclick="return KeenAdminUsersActivatePanel()">Instagram Photos by Tag</a>
		</h3>

		<div id="InstagramUserPhotosPanel"></div>

		<div id="InstagramTagPhotosPanel" style="display: none"></div>
	
	</div>
	
	<?php
}