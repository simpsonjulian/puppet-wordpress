<?php
/*
Plugin Name: RSS Footer
Version: 0.8.2
Plugin URI: http://yoast.com/wordpress/rss-footer/
Description: Allows you to add a line of content to the end of your RSS feed articles.
Author: Joost de Valk
Author URI: http://yoast.com/
*/

if ( ! class_exists( 'RSSFoot_Admin' ) ) {

	class RSSFooter_Admin {
		
		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_options_page('RSS Footer Configuration', 'RSS Footer', 10, basename(__FILE__), array('RSSFooter_Admin','config_page'));
				add_filter( 'plugin_action_links', array( 'RSSFooter_Admin', 'filter_plugin_actions'), 10, 2 );
				add_filter( 'ozh_adminmenu_icon', array( 'RSSFooter_Admin', 'add_ozh_adminmenu_icon' ) );				
			}
		}
		
		function add_ozh_adminmenu_icon( $hook ) {
			static $rssfooticon;
			if (!$rssfooticon) {
				$rssfooticon = WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)). '/feed_edit.png';
			}
			if ($hook == 'rss-footer.php') return $rssfooticon;
			return $hook;
		}

		function filter_plugin_actions( $links, $file ){
			//Static so we don't call plugin_basename on every plugin row.
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
			
			if ( $file == $this_plugin ){
				$settings_link = '<a href="options-general.php?page=rss-footer.php">' . __('Settings') . '</a>';
				array_unshift( $links, $settings_link ); // before other links
			}
			return $links;
		}
		
		function config_page() {
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the RSS Footer options.'));
				check_admin_referer('rssfooter-config');

				if (isset($_POST['footerstring']) && $_POST['footerstring'] != "") 
					$options['footerstring'] 	= $_POST['footerstring'];
				
				if (isset($_POST['position']) && $_POST['position'] != "") 
					$options['position'] 	= $_POST['position'];
				
				if (isset($_POST['postlink'])) {
					$options['postlink'] = true;
				} else {
					$options['postlink'] = false;
				}
				
				$options['everset'] = true;
				
				update_option('RSSFooterOptions', $options);
			}
			
			$options  = get_option('RSSFooterOptions');
			
			?>
			<div class="wrap">
				<h2>RSS Footer options</h2>
				<form action="" method="post" id="rssfooter-conf">
					<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('rssfooter-config');
					?>
					<table class="form-table" style="width: 100%;">
						<tr valign="top">
							<th scrope="row">
								<label for="footerstring">Content to put in the footer:</label><br/>
								<small>(HTML allowed)</small>
							</th>
							<td>
								<textarea cols="80" rows="4" id="footerstring" name="footerstring"><?php echo stripslashes(htmlentities($options['footerstring'])); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scrope="row">
								<label for="position">Content position:</label>
							</th>
							<td>
								<select name="position" id="position">
									<option value="after" <?php if ($options['position'] == "after") echo 'selected="selected"'?>>after</option>
									<option value="before" <?php if ($options['position'] == "before") echo 'selected="selected"'?>>before</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scrope="row">
								Other options:
							</th>
							<td>
								<input type="checkbox" name="postlink" <?php if ($options['postlink']) echo 'checked="checked"'?>/> Include a link back to the post, with post title as anchor text too.
							</td>
						</tr>
					</table>
					<p class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></p>
				</form>
			</div>
<?php		}	
	}
}

$options  = get_option('RSSFooterOptions');
if (!isset($options['everset'])) {
	// Set default values
	$options['footerstring'] = "Post from: <a href=\"".get_bloginfo('url')."\">".get_bloginfo('name')."</a>";
	$options['position'] = "after";
	update_option('RSSFooterOptions', $options);
}

function embed_rssfooter($content) {
	if(is_feed()) {
		$options  = get_option('RSSFooterOptions');
		if ($options['position'] == "before") {
			if($options['postlink']) {
				$content = '<p><a href="'.get_permalink().'">'.get_the_title()."</a></p>\n" . $content;	
			}
			$content = "<p>" . stripslashes($options['footerstring']) . "</p>\n" . $content;
		} else {
			$content = $content . "<p>" . stripslashes($options['footerstring']) . "</p>\n";
			if($options['postlink']) {
				$content = $content . '<p><a href="'.get_permalink().'">'.get_the_title()."</a></p>\n";
			}
		}
	}
	return $content;
}

add_filter('the_content', 'embed_rssfooter');
add_filter('the_excerpt_rss', 'embed_rssfooter');

add_action('admin_menu', array('RSSFooter_Admin','add_config_page'));

?>