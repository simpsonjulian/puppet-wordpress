<?php
/*
Plugin Name: FD Feedburner Plugin
Plugin URI: http://flagrantdisregard.com/feedburner/
Description: Redirects all feeds to a Feedburner feed
Author: John Watson
Author URI: http://flagrantdisregard.com/
Version: 1.41

Copyright (C) Sat Feb 18 2006 John Watson
john@flagrantdisregard.com
http://flagrantdisregard.com/

$Id: feedburnerplugin.php 407 2006-11-14 17:40:47Z John $

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
'
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/ 

add_action('admin_menu', 'feedburner_config_page');

function feedburner_config_page() {
	global $wpdb;
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php', __('Feedburner Configuration'), __('Feedburner Configuration'), 8, __FILE__, 'feedburner_conf');
}

function feedburner_fix_url($url) {
	$url = preg_replace('!^(http|https)://!i', '', $url);
	$url = preg_replace('!^/!i', '', $url);
	$url = 'http://'.$url;
	return $url;
}

function feedburner_conf() {
	$options = get_option('fd_feedburner');

	if (!isset($options['feedburner_url'])) $options['feedburner_url'] = null;
	if (!isset($options['feedburner_comment_url'])) $options['feedburner_comment_url'] = null;
	if (!isset($options['feedburner_append_cats'])) $options['feedburner_append_cats'] = 0;
	if (!isset($options['feedburner_no_cats'])) $options['feedburner_no_cats'] = 0;
	if (!isset($options['feedburner_no_search'])) $options['feedburner_no_search'] = 0;
	
	$updated = false;
	if ( isset($_POST['submit']) ) {
		check_admin_referer();
		
		if (isset($_POST['feedburner_url'])) {
			$feedburner_url = $_POST['feedburner_url'];
			if ($feedburner_url != null) $feedburner_url = feedburner_fix_url($feedburner_url);
		} else {
			$feedburner_url = null;
		}
		
		if (isset($_POST['feedburner_comment_url'])) {
			$feedburner_comment_url = $_POST['feedburner_comment_url'];
			if ($feedburner_comment_url != null) $feedburner_comment_url = feedburner_fix_url($feedburner_comment_url);
		} else {
			$feedburner_comment_url = null;
		}
		
		if (isset($_POST['feedburner_append_cats'])) {
			$feedburner_append_cats = $_POST['feedburner_append_cats'];
		} else {
			$feedburner_append_cats = 0;
		}
		
		if (isset($_POST['feedburner_no_cats'])) {
			$feedburner_no_cats = $_POST['feedburner_no_cats'];
		} else {
			$feedburner_no_cats = 0;
		}
		
		if (isset($_POST['feedburner_no_search'])) {
			$feedburner_no_search = $_POST['feedburner_no_search'];
		} else {
			$feedburner_no_search = 0;
		}
		
		$options['feedburner_url'] = $feedburner_url;
		$options['feedburner_comment_url'] = $feedburner_comment_url;
		$options['feedburner_append_cats'] = $feedburner_append_cats;
		$options['feedburner_no_cats'] = $feedburner_no_cats;
		$options['feedburner_no_search'] = $feedburner_no_search;
		
		update_option('fd_feedburner', $options);
		
		$updated = true;
	}
?>

<div class="wrap">
<?php
if ($updated) {
	echo "<div id='message' class='updated fade'><p>";
	_e('Configuration updated.');
	echo "</p></div>";
}
?>
<h2><?php _e('Feedburner Configuration'); ?></h2>
<div style="float: right; width: 350px">
	<h3>How does this work?</h3>
	<p><?php _e('This plugin automatically redirects all of your existing feeds to Feedburner (including category and tag feeds).')?></p>
	<p><?php _e('First go to <a href="http://feedburner.com">Feedburner.com</a> and burn your feed. Enter the URL Feedburner created for you. You may optionally redirect your comments feed using the same procedure. To disable redirection, disable the plugin or erase the URLs.');
	?></p>
	<p><?php _e('Once you enter URLs your feeds will be redirected automatically and you do not need to take any further action.') ?> <em><?php _e('Note that your feeds may not appear to redirect to Feedburner until you add a new post.'); ?></em>
	</p>
</div>
<form action="" method="post" id="feedburner-conf">
<h3><label for="feedburner_url"><?php _e('Redirect my feeds here:'); ?></label></h3>
<p><input id="feedburner_url" name="feedburner_url" type="text" size="65" maxlength="200" value="<?php echo $options['feedburner_url']; ?>" /></p>

<h3><label for="feedburner_comment_url"><?php _e('Redirect my comments feed here:'); ?></label></h3>
<p><input id="feedburner_comment_url" name="feedburner_comment_url" type="text" size="65" maxlength="200" value="<?php echo $options['feedburner_comment_url']; ?>" /></p>

<h3>Advanced Options</h3>
<p>
	<input id="feedburner_no_cats" name="feedburner_no_cats" type="checkbox" value="1"<?php if ($options['feedburner_no_cats']==1) echo ' checked'; ?> />
	<label for="feedburner_no_cats"><?php _e('Do not redirect category or tag feeds'); ?></label>
</p>
<p>
	<input id="feedburner_append_cats" name="feedburner_append_cats" type="checkbox" value="1"<?php if ($options['feedburner_append_cats']==1) echo ' checked'; ?> />
	<label for="feedburner_append_cats"><?php _e('Append category/tag to URL for category/tag feeds'); ?> (<i>http://url<b>_category</b></i>)</label>
</p>
<p>
	<input id="feedburner_no_search" name="feedburner_no_search" type="checkbox" value="1"<?php if ($options['feedburner_no_search']==1) echo ' checked'; ?> />
	<label for="feedburner_no_search"><?php _e('Do not redirect search result feeds'); ?></label>
</p>

<p class="submit" style="text-align: left"><input type="submit" name="submit" value="<?php _e('Save &raquo;'); ?>" /></p>
</form>
</div>
<?php
}

function feedburner_redirect() {
	global $feed, $withcomments, $wp, $wpdb, $wp_version, $wp_db_version;
	
	// Do nothing if not a feed
	if (!is_feed()) return;
	
	// Do nothing if feedburner is the user-agent
	if (preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT'])) return;
	
	// Do nothing if not configured
	$options = get_option('fd_feedburner');
	if (!isset($options['feedburner_url'])) $options['feedburner_url'] = null;
	if (!isset($options['feedburner_comment_url'])) $options['feedburner_comment_url'] = null;
	if (!isset($options['feedburner_append_cats'])) $options['feedburner_append_cats'] = 0;
	if (!isset($options['feedburner_no_cats'])) $options['feedburner_no_cats'] = 0;
	if (!isset($options['feedburner_no_search'])) $options['feedburner_no_search'] = 0;
	$feed_url = $options['feedburner_url'];
	$comment_url = $options['feedburner_comment_url'];
	if ($feed_url == null && $comment_url == null) return;
	
	// Get category
	$cat = null;
	if ($wp->query_vars['category_name'] != null) {
		$cat = $wp->query_vars['category_name'];
	}
	if ($wp->query_vars['cat'] != null) {
		if ($wp_db_version >= 6124) {
			// 6124 = WP 2.3
			$cat = $wpdb->get_var("SELECT slug FROM $wpdb->terms WHERE term_id = '".$wp->query_vars['cat']."' LIMIT 1");
		} else {
			$cat = $wpdb->get_var("SELECT category_nicename FROM $wpdb->categories WHERE cat_ID = '".$wp->query_vars['cat']."' LIMIT 1");
		}
	}
	if ($options['feedburner_append_cats'] == 1 && $cat) {
		$feed_url .= '_'.$cat;
	}
	
	// Get tag
	$tag = null;
	if ($wp->query_vars['tag'] != null) {
		$tag = $wp->query_vars['tag'];
	}
	if ($options['feedburner_append_cats'] == 1 && $tag) {
		$feed_url .= '_'.$tag;
	}

	// Get search terms
	$search = null;
	if ($wp->query_vars['s'] != null) {
		$search = $wp->query_vars['s'];
	}

	// Redirect comment feed
	if ($feed == 'comments-rss2' || is_single() || $withcomments) {
		if ($comment_url != null) {
			header("Location: ".$comment_url);
			die;
		}
	} else {
		// Other feeds
		switch($feed) {
			case 'feed':
			case 'rdf':
			case 'rss':
			case 'rss2':
			case 'atom':
				if (($cat || $tag) && $options['feedburner_no_cats'] == 1) {
					// If this is a category/tag feed and redirect is disabled, do nothing
				} else if ($search && $options['feedburner_no_search'] == 1) {
					// If this is a search result feed and redirect is disabled, do nothing
				} else {
					if ($feed_url != null) {
						// Redirect the feed
						header("Location: ".$feed_url);
						die;
					}
				}
		}
	}
}

/*
==================================================
Add action hooks
==================================================
*/
add_action('template_redirect', 'feedburner_redirect');
?>
