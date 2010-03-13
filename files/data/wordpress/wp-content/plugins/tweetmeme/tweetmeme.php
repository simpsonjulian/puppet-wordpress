<?php
/*
Plugin Name: TweetMeme Retweet Button
Plugin URI: http://tweetmeme.com/about/plugins
Description: Adds a button which easily lets you retweet your blog posts.
Version: 1.8.2
Author: TweetMeme
Author URI: http://tweetmeme.com
*/

function tm_options() {
	add_menu_page('TweetMeme', 'TweetMeme', 8, basename(__FILE__), 'tm_options_page');
	add_submenu_page(basename(__FILE__), 'Settings', 'Settings', 8, basename(__FILE__), 'tm_options_page');
    add_submenu_page(basename(__FILE__), 'Analytics', 'Analytics', 8, basename(__FILE__) . 'stats', 'tm_stats_page');
}

/**
* Build up all the params for the button
*/
function tm_build_options() {
	// get the post varibale (should be in the loop)
	global $post;
	// get the permalink
    if (get_post_status($post->ID) == 'publish') {
        $url = get_permalink();
    }
    $button = '?url=' . urlencode($url);

	// now build up the params, start with the source
    if (get_option('tm_source')) {
        $button .= '&amp;source=' . urlencode(get_option('tm_source'));
    }

	// which style
    if (get_option('tm_version') == 'compact') {
        $button .= '&amp;style=compact';
    } else {
		$button .= '&amp;style=normal';
	}

	// what shortner to use
	if (get_option('tm_url_shortner') && get_option('tm_url_shortner') != 'default') {
    	$button .= '&amp;service=' . urlencode(get_option('tm_url_shortner'));
	}

	// does the shortner have an API key
	if (get_option('tm_api_key')) {
		$button .= '&amp;service_api=' . urlencode(get_option('tm_api_key'));
	}

	// how many spaces do we want to leave at the end
	if (get_option('tm_space')) {
		$button .= '&amp;space=' . get_option('tm_space');
	}

	// append the hashtags
	if (get_option('tm_hashtags') == 'yes') {
		// first lets see if the post has the custom field
		if (($hashtags = get_post_meta($post->ID, 'tm_hashtags')) != false) {
			// first split them out
			$hashtags = explode(',', $hashtags[0]);
			// go through and urlencode
			foreach($hashtags as $row => $tag) {
				$hashtags[$row] = urlencode(trim($tag));
			}
			// nope so lets use them
			$button .= '&amp;hashtags=' . implode(',', $hashtags);
		} else if (($tags = get_the_tags()) != false) {
			// ok, grab them off the post tags
			$hashtags = array();
			foreach ($tags as $tag) {
				$hashtags[] = urlencode($tag->name);
			}
			$button .= '&amp;hashtags=' . implode(',', $hashtags);
		} else if (($hashtags = get_option('tm_hashtags_tags')) != false) {
			// first split them out
			$hashtags = explode(',', $hashtags);
			// go through and urlencode
			foreach($hashtags as $row => $tag) {
				$hashtags[$row] = urlencode(trim($tag));
			}
			// add them all back together
			$button .= '&amp;hashtags=' . implode(',', $hashtags);
		}
	}
	// return all the params
	return $button;
}

/**
* Generate the iFrame render of the button
*/
function tm_generate_button() {
	// build up the outer style
    $button = '<div class="tweetmeme_button" style="' . get_option('tm_style') . '">';
    $button .= '<iframe src="http://api.tweetmeme.com/button.js' . tm_build_options() . '" ';

	// give it a height, dependant on style
    if (get_option('tm_version') == 'compact') {
        $button .= 'height="20" width="90"';
    } else {
		$button .= 'height="61" width="50"';
	}
	// close off the iframe
	$button .= ' frameborder="0" scrolling="no"></iframe></div>';
	// return the iframe code
    return $button;
}

/**
* Generates the image button
*/
function tm_generate_static_button() {
	if (get_post_status($post->ID) == 'publish') {
        $url = get_permalink();
		return
		'<div class="tweetmeme_button" style="' . get_option('tm_style') . '">
			<a href="http://api.tweetmeme.com/share?url=' . urlencode($url) . '">
				<img src="http://api.tweetmeme.com/imagebutton.gif' . tm_build_options() . '" height="61" width="50" />
			</a>
		</div>';
	} else {
		return;
	}
}

/**
* Gets run when the content is loaded in the loop
*/
function tm_update($content) {

    global $post;

    // add the manual option, code added by kovshenin
    if (get_option('tm_where') == 'manual') {
        return $content;
	}
    // is it a page
    if (get_option('tm_display_page') == null && is_page()) {
        return $content;
    }
	// are we on the front page
    if (get_option('tm_display_front') == null && is_home()) {
        return $content;
    }
	// are we in a feed
    if (is_feed()) {
		$button = tm_generate_static_button();
		$where = 'tm_rss_where';
    } else {
		$button = tm_generate_button();
		$where = 'tm_where';
	}
	// are we displaying in a feed
	if (is_feed() && get_option('tm_display_rss') == null) {
		return $content;
	}

	// are we just using the shortcode
	if (get_option($where) == 'shortcode') {
		return str_replace('[tweetmeme]', $button, $content);
	} else {
		// if we have switched the button off
		if (get_post_meta($post->ID, 'tweetmeme') == null) {
			if (get_option($where) == 'beforeandafter') {
				// adding it before and after
				return $button . $content . $button;
			} else if (get_option($where) == 'before') {
				// just before
				return $button . $content;
			} else {
				// just after
				return $content . $button;
			}
		} else {
			// not at all
			return $content;
		}
	}
}

// Manual output
function tweetmeme() {
    if (get_option('tm_where') == 'manual') {
        return tm_generate_button();
    } else {
        return false;
    }
}

// Remove the filter excerpts
// Code added by Soccer Dad
function tm_remove_filter($content) {
	if (!is_feed()) {
    	remove_action('the_content', 'tm_update');
	}
    return $content;
}

/**
* Ping when tweetmeme when a post is updated, this makes sure the titles/desc are correct on tweetmeme
*/
function tm_ping($post_id) {
    // do we have curl
    if ((get_option('tm_ping') != 'off') && function_exists('curl_init')) {
        $url = get_permalink($post_id);
        // create a new cURL resource
        $ch = curl_init();
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, 'http://api.tweetmeme.com/ping.php?url=' . urlencode($url));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // grab URL and pass it to the browser
        curl_exec($ch);
        // close cURL resource, and free up system resources
        curl_close($ch);
    }
}

/**
* Adds a tweetmeme-title meta title, provides a much more accurate title for the button
*/
function tm_head() {
	// if its a post page
	if (is_single()) {
		global $post;
		$title = get_the_title($post->ID);
		echo '<meta name="tweetmeme-title" content="' . strip_tags($title) . '" />';
	}
}

function tm_options_page() {
?>
    <div class="wrap">
    <div class="icon32" id="icon-options-general"><br/></div><h2>Settings for Tweetmeme Integration</h2>
    <p>This plugin will install the tweetmeme widget for each of your blog posts in both the content of your posts and the RSS feed.
    It can be easily styles in your blog posts and is referenced by the id <code>tweetmeme_button</code>.
    </p>
    <form method="post" action="options.php">
    <?php
        // New way of setting the fields, for WP 2.7 and newer
        if(function_exists('settings_fields')){
            settings_fields('tm-options');
        } else {
            wp_nonce_field('update-options');
            ?>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="tm_ping,tm_where,tm_style,tm_version,tm_display_page,tm_display_front,tm_display_rss,tm_display_feed,tm_source,tm_url_shortner,tm_space,tm_hashtags,tm_hashtags_tags" />
            <?php
        }
    ?>
        <table class="form-table">
            <tr>
	            <tr>
	                <th scope="row" valign="top">
	                    Display
	                </th>
	                <td>
	                    <input type="checkbox" value="1" <?php if (get_option('tm_display_page') == '1') echo 'checked="checked"'; ?> name="tm_display_page" id="tm_display_page" group="tm_display"/>
	                    <label for="tm_display_page">Display the button on pages</label>
	                    <br/>
	                    <input type="checkbox" value="1" <?php if (get_option('tm_display_front') == '1') echo 'checked="checked"'; ?> name="tm_display_front" id="tm_display_front" group="tm_display"/>
	                    <label for="tm_display_front">Display the button on the front page (home)</label>
	                    <br/>
	                    <input type="checkbox" value="1" <?php if (get_option('tm_display_rss') == '1') echo 'checked="checked"'; ?> name="tm_display_rss" id="tm_display_rss" group="tm_display"/>
	                    <label for="tm_display_rss">Display the image button in your feed, only available as <strong>the normal size</strong> widget.</label>
	                </td>
	            </tr>
                <th scope="row" valign="top">
                    Position
                </th>
                <td>
                	<select name="tm_where">
                		<option <?php if (get_option('tm_where') == 'before') echo 'selected="selected"'; ?> value="before">Before</option>
                		<option <?php if (get_option('tm_where') == 'after') echo 'selected="selected"'; ?> value="after">After</option>
                		<option <?php if (get_option('tm_where') == 'beforeandafter') echo 'selected="selected"'; ?> value="beforeandafter">Before and After</option>
                		<option <?php if (get_option('tm_where') == 'shortcode') echo 'selected="selected"'; ?> value="shortcode">Shortcode [tweetmeme]</option>
                		<option <?php if (get_option('tm_where') == 'manual') echo 'selected="selected"'; ?> value="manual">Manual</option>
                	</select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    RSS Position
                </th>
                <td>
                	<select name="tm_rss_where">
                		<option <?php if (get_option('tm_rss_where') == 'before') echo 'selected="selected"'; ?> value="before">Before</option>
                		<option <?php if (get_option('tm_rss_where') == 'after') echo 'selected="selected"'; ?> value="after">After</option>
                		<option <?php if (get_option('tm_rss_where') == 'beforeandafter') echo 'selected="selected"'; ?> value="beforeandafter">Before and After</option>
                		<option <?php if (get_option('tm_where') == 'shortcode') echo 'selected="selected"'; ?> value="shortcode">Shortcode [tweetmeme]</option>
                	</select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="tm_style">Styling</label></th>
                <td>
                    <input type="text" value="<?php echo htmlspecialchars(get_option('tm_style')); ?>" name="tm_style" id="tm_style" />
                    <span class="description">Add style to the div that surrounds the button E.g. <code>float: left; margin-right: 10px;</code></span>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    Type
                </th>
                <td>
                    <input type="radio" value="large" <?php if (get_option('tm_version') == 'large') echo 'checked="checked"'; ?> name="tm_version" id="tm_version_large" group="tm_version"/>
                    <label for="tm_version_large">The normal size widget</label>
                    <br/>
                    <input type="radio" value="compact" <?php if (get_option('tm_version') == 'compact') echo 'checked="checked"'; ?> name="tm_version" id="tm_version_compact" group="tm_version" />
                    <label for="tm_version_compact">The compact widget</label>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    <label for="tm_source">Source</label>
                </th>
                <td>
                    RT @<input type="text" value="<?php echo get_option('tm_source'); ?>" name="tm_source" id="tm_source" />
                    <span class="description">Please use the format of 'yourname', not 'RT @yourname'. For more information please see the <a href="http://help.tweetmeme.com">help</a>.</span>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    URL Shortner
                </th>
                <td>
                    <select name="tm_url_shortner">
                        <option <?php if (get_option('tm_url_shortner') == 'default') echo 'selected="selected"'; ?> value="default">Default</option>
                        <option <?php if (get_option('tm_url_shortner') == 'bit.ly') echo 'selected="selected"'; ?> value="bit.ly">bit.ly</option>
                        <option <?php if (get_option('tm_url_shortner') == 'awe.sm') echo 'selected="selected"'; ?> value="awe.sm">awe.sm</option>
                        <option <?php if (get_option('tm_url_shortner') == 'cli.gs') echo 'selected="selected"'; ?> value="cli.gs">cligs</option>
                        <option <?php if (get_option('tm_url_shortner') == 'digg.com') echo 'selected="selected"'; ?> value="digg.com">digg</option>
                        <option <?php if (get_option('tm_url_shortner') == 'is.gd') echo 'selected="selected"'; ?> value="is.gd">is.gd</option>
                        <option <?php if (get_option('tm_url_shortner') == 'TinyURL.com') echo 'selected="selected"'; ?> value="TinyURL.com">TinyURL</option>
                        <option <?php if (get_option('tm_url_shortner') == 'ow.ly') echo 'selected="selected"'; ?> value="ow.ly">Ow.ly</option>
                        <option <?php if (get_option('tm_url_shortner') == 'retwt.me') echo 'selected="selected"'; ?> value="retwt.me">retwt.me</option>
                    </select>
                    <span class="description">If you use <strong>awe.sm</strong> an API key is required.</span>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    <label for="tm_api_key">URL Shortener API Key</label>
                </th>
                <td>
                    <input type="text" value="<?php echo get_option('tm_api_key'); ?>" name="tm_api_key" id="tm_api_key" />
                    <span class="description">API Key for use with <strong>awe.sm</strong>, <strong>cligs</strong> and <strong>digg</strong>.</span>
                </td>
            </tr>
            <tr>
            	<th scope="row" valign="top">
                    <label for="tm_space">Spaces</label>
                </th>
                <td>
                    <input type="text" value="<?php echo get_option('tm_space'); ?>" name="tm_space" id="tm_space" />
                    <span class="description">The amount of empty space to leave at the end of the tweet.</span>
                </td>
            </tr>
            <tr>
            	<th scope="row" valigh="top">
            		Hashtags
            	</th>
            	<td>
            		<input type="radio" value="yes" name="tm_hashtags" group="tm_hashtags" id="tm_hashtags_on" <?php if (get_option('tm_hashtags') == 'yes') echo 'checked="checked"'; ?> />
            		<label for="tm_hashtags_on">Take the top tags from the post and apply to the tweet</label>
            		<br/>
            		<input type="radio" value="no" name="tm_hashtags" group="tm_hashtags" id="tm_hashtags_off" <?php if (get_option('tm_hashtags') == 'no') echo 'checked="checked"'; ?> />
            		<label for="tm_hashtags_off">Dont use hashtags</label>
            		<br/>
            		<label for="tm_hashtags_tags">Use these default tags if there are no tags on the post.</label>
            		<input type="text" value="<?php echo get_option('tm_hashtags_tags'); ?>" name="tm_hashtags_tags" />
            		<br/><br/>
            		<span class="description">You can override any of these by specifying hashtags on a per post basis, by using the custom field tm_hashtags (seperated by ,).</span>
            	</td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    <label for="tm_ping">Ping Tweetmeme</label>
                </th>
                <td>
                    <input type="radio" value="on" <?php if (get_option('tm_ping') == 'on') echo 'checked="checked"'; ?> name="tm_ping" id="tm_ping_on" group="tm_ping"/>
                    <label for="tm_ping_on">Yes (Alert TweetMeme whenever a new post is published)</label>
                    <br/>
                    <input type="radio" value="off" <?php if (get_option('tm_ping') == 'off') echo 'checked="checked"'; ?> name="tm_ping" id="tm_ping_off" group="tm_ping" />
                    <label for="tm_ping_off">No</label>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
    </div>
<?php
}



// On access of the admin page, register these variables (required for WP 2.7 & newer)
function tm_init(){
    if(function_exists('register_setting')){
        register_setting('tm-options', 'tm_display_page');
        register_setting('tm-options', 'tm_display_front');
        register_setting('tm-options', 'tm_display_rss');
        register_setting('tm-options', 'tm_source', 'tm_sanitize_username');
        register_setting('tm-options', 'tm_style');
        register_setting('tm-options', 'tm_version');
        register_setting('tm-options', 'tm_where');
        register_setting('tm-options', 'tm_rss_where');
        register_setting('tm-options', 'tm_ping');
        register_setting('tm-options', 'tm_url_shortner');
        register_setting('tm-options', 'tm_space');
        register_setting('tm-options', 'tm_hashtags');
        register_setting('tm-options', 'tm_hashtags_tags');
    }
}

function tm_sanitize_username($username){
    return preg_replace('/[^A-Za-z0-9_]/','',$username);
}

// Only all the admin options if the user is an admin
if(is_admin()){
    add_action('admin_menu', 'tm_options');
    add_action('admin_init', 'tm_init');
}

// Set the default options when the plugin is activated
function tm_activate(){
    add_option('tm_where', 'before');
    add_option('tm_rss_where', 'before');
    add_option('tm_source');
    add_option('tm_style', 'float: right; margin-left: 10px;');
    add_option('tm_version', 'large');
    add_option('tm_display_page', '1');
    add_option('tm_display_front', '1');
    add_option('tm_display_rss', '1');
    add_option('tm_ping', 'on');
    add_option('tm_hashtags', 'on');
}

add_filter('the_content', 'tm_update', 8);
add_filter('get_the_excerpt', 'tm_remove_filter', 9);

add_action('publish_post', 'tm_ping', 9);

add_action('wp_head', 'tm_head');

add_action('admin_print_scripts', 'tm_js_admin_header');
add_action('wp_ajax_tm_ajax_elev_lookup', 'tm_ajax_elev_lookup');

// load in the other files
require('analytics.php');

register_activation_hook( __FILE__, 'tm_activate');
