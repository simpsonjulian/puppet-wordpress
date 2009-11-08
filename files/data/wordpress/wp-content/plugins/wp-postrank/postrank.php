<?php
/*
Plugin Name: PostRank
Plugin URI: http://www.postrank.com/publishers/wordpress
Description: Showcase your Top Posts with the <a href="http://www.postrank.com/publishers">PostRank widget</a>, track social media analytics, and engage with your readers from the WP dashboard. Learn more about <a href="http://www.postrank.com/postrank">PostRank</a>.
Version: 1.1.1
Author: Trevor Creech
Author URI: http://trevorcreech.com/
*/

// POSTRANK_DB_VERSION is used to check if the postrank table needs to be upgraded.  Change this if the schema in update_postrank_db is changed
define('POSTRANK_DB_VERSION', '0.1.1');
define('POSTRANK_APPKEY', 'wp-postrank');
global $wpdb;
define('POSTRANK_TABLE_NAME', $wpdb->prefix . 'postrank');

// To enable debug output, set this to true.
// Create a postrank.log file, and make it writable by the web server.  This can be done with 'chmod 777 postrank.log'
define('POSTRANK_DEBUG', false);

// Default widget options
global $postrank_widget_options;
$postrank_widget_options = array('num' => 6, 'theme' => 'blueSteel');

global $postrank_messages;
$postrank_messages = array();

if(!class_exists('Services_JSON'))
	require_once('JSON/JSON.php');
global $postrank_json;
$postrank_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);


/*******************
 * PostRank Object *
 *******************/

class PostRank
{
	// PHP4 constructor calls PHP5 constructor
	function PostRank($value, $color)
	{
		$this->__construct($value, $color);
	}

	// PHP5 constructor
	function __construct($value,$color)
	{
		$this->_value = $value;
		$this->_color = $color;
	}

	function value()
	{
		return $this->_value;
	}

	function color()
	{
		return $this->_color;
	}

	function inspect()
	{
		return "$this->_postrank, $this->_color";
	}
}


/****************************
 * Admin PostRank Functions *
 ****************************/

function postrank_admin_css()
{
?>
<style type="text/css">
.fixed .column-postrank {
	width: 5.5em;
}
</style>
<?php
}

function postrank_admin_add_column($columns)
{
	$columns['postrank'] = __('PostRank');
	return $columns;
}

function postrank_admin_show($column_name,$id)
{
	if ($column_name != 'postrank')
		return;

	the_postrank_badge($id);
}

function postrank_check_status()
{
	postrank_check_import();
	postrank_check_curl();
}

function postrank_check_import()
{
	if(!get_option('postrank_importing'))
		return;
	postrank_guess_feed_hash();
	if(!get_option('postrank_importing'))
		return;

	global $postrank_messages;
	$postrank_messages[] = 'Your feed has been added to our system.  Don\'t despair, PostRanks will be appearing shortly!  If they still haven\'t turned up after several hours, go ahead and contact us on <a href="http://getsatisfaction.com/aiderss">Get Satisfaction</a>';
}

function postrank_check_curl()
{
	if(!get_option('postrank_no_curl'))
		return;
	delete_option('postrank_no_curl');

	global $postrank_messages;
	$postrank_messages[] = 'I\'ve detected that you don\'t have PHP\'s <a href="http://php.net/curl">libcurl</a> installed.  The sidebar widget will work perfectly, but you are out of luck for the admin PostRanks.  They\'ve been disabled for now, but if you get libcurl installed and want them, you can enable them from the <a href="options-general.php?page=wp-postrank/postrank.php">PostRank Settings Panel</a>.';
}

function postrank_show_messages()
{
	global $postrank_messages;
	foreach($postrank_messages as $message)
	{
		echo "<div class='updated fade'><p><strong>PostRank Says:</strong> $message</p></div>";
	}
}


/********************
 * Widget Functions *
 ********************/

function widget_postrank_init()
{	
	if (!function_exists('register_sidebar_widget'))
		return;

	// Display widget
	function widget_postrank($args)
	{
		extract($args);

		echo $before_widget;
		the_postrank_widget();
		echo $after_widget;
	}

	// Widget options
	function widget_postrank_control()
	{
		global $postrank_widget_options;
		$themes = array('blueSteel' => 'Blue Steel','hawt' => 'Hawt','springMeadows' => 'Spring Meadows','theDarkSide' => 'The Dark Side','hotChocolate' => 'Hot Chocolate','siren' => 'Siren','pimento' => 'Pimento','diner' => 'Mel\'s Diner','diy' => 'Do It Yourself');
		$options = get_option('widget_postrank');

		if ( $_POST['postrank-submit'] )
		{
			$options['num'] = strip_tags(stripslashes($_POST['postrank-num']));
			$options['theme'] = strip_tags(stripslashes($_POST['postrank-theme']));
			update_option('widget_postrank', $options);
		}

		echo '<p><label for="postrank-num">Show: </label>';
		echo '<select name="postrank-num" class="widefat">';
		for($i = 1; $i <= 15; $i ++)
		{
			echo '<option ' . ($i == $options['num'] ? 'selected="selected" ' : '') . 'value="' . $i . '">' . $i . ' Posts</option>';
		}
		echo '</select>';
		echo '</p>';
		foreach($themes as $theme_id => $theme_name)
		{
			echo '<p style="float: left; margin-right: 5px;">';
			echo '<label for="' . $theme_id . '"><img style="display: block; width: 73px;" alt="' . $theme_name . '" title="' . $theme_name . '" src="' . get_postrank_path() . '/images/' . $theme_id . '.png" /></label>';
			echo '<input type="radio" id="' . $theme_id . '" style="display: block; margin: auto;" name="postrank-theme" ' . ($options['theme'] == $theme_id ? 'checked="checked" ' : '') . 'value="' . $theme_id . '" />';
			echo '</p>';
		}
		echo '<div style="clear:both;"></div>';
		echo '<input type="hidden" id="postrank-submit" name="postrank-submit" value="1" />';
	}

	if (function_exists('register_sidebar_widget'))
	{
		register_sidebar_widget('PostRank', 'widget_postrank');
		register_widget_control('PostRank', 'widget_postrank_control', 240, 400);
	}
}

add_action('plugins_loaded', 'widget_postrank_init');

function postrank_js_widget($feed_hash, $num, $theme)
{
?>

<script type="text/javascript" src="http://api.postrank.com/static/widget-v2.js"></script>
<script type="text/javascript">
//<![CDATA[
	var options = {
		"feed_hash": "<?php echo $feed_hash ?>", // Unique feed identifier
		"num": <?php echo $num ?>, // Number of top posts to display
		"theme": "<?php echo $theme ?>" // blueAndBlue, blackAndGray
	}   
	new PostRankWidget(options);
//]]>
</script>

<?php
}



/*****************
 * Template Tags *
 *****************/

// This can be called with any or all of the parameters.
// To use default feed_hash and num, but change theme, use the_postrank_widget('hawt');
// To change the number shown, but not the theme, use the_postrank_widget(NULL,10);
function the_postrank_widget($theme = NULL, $num = NULL, $feed_hash = NULL)
{
	if(!($num && $theme))
	{
		$options = get_option('widget_postrank');
		if(!$num)
			$num = $options['num'];
		if(!$theme)
			$theme = $options['theme'];
	}
	if(!$feed_hash)
		$feed_hash = get_option('postrank_feed_hash');
	postrank_js_widget($feed_hash, $num, $theme);
}


function the_postrank_badge($post_id = false)
{
	global $post;
	$pr = get_postrank();
	if(!$pr)
		return;
	$permalink = get_permalink($post);
	$entry_hash = md5($permalink);
	$link = "http://www.postrank.com/feed/" . get_option('postrank_feed_hash');
	echo "<div style='float:right'><a id='entry-$entry_hash' class='postrank postrank-badge' style='position:relative;z-index:9;background-color:#" . $pr->color() . ";' href='$link'>" . $pr->value() . "<span class='topLeft'></span><span class='topRight'></span><span class='bottomLeft'></span><span class='bottomRight'></span><span style='display:none;'>$permalink</span></a></div>";
}

function the_postrank($post_id = false)
{
	$pr = get_postrank();
	if(!$pr)
		return;
	echo $pr->value();	
}

function the_postrank_color($post_id = false)
{
	$pr = get_postrank();
	if(!$pr)
		return;
	echo $pr->color();	
}


/********************
 * Javascript & CSS *
 ********************/

function postrank_badge_javascript() {
?>
	<script type="text/javascript">
	//<![CDATA[
	if( document.all && !document.getElementsByTagName )
		document.getElementsByTagName = function( nodeName )
		{
			if( nodeName == '*' ) return document.all;
			var result = [], rightName = new RegExp( nodeName, 'i' ), i;
			for( i=0; i<document.all.length; i++ )
				if( rightName.test( document.all[i].nodeName ) )
					result.push( document.all[i] );
			return result;
		};
	if(!document.getElementsByClassName)
		document.getElementsByClassName = function( className, nodeName )
		{
			var result = [], tag = nodeName||'*', node, seek, i;
			var rightClass = new RegExp( '(^| )'+ className +'( |$)' );
			seek = document.getElementsByTagName( tag );
			for( i=0; i<seek.length; i++ )
				if( rightClass.test( (node = seek[i]).className ) )
					result.push( seek[i] );
			return result;
		};

	function postrank_badge_step2(data)
	{
		// %r is raw url, %e is encoded url
		var sources = {"comments":"%r", "twitter":"", "reddit":"",
		"rocket":"http://blogs.icerocket.com/search?q=%e&ql=en&s=f&pop=l&news=m", "digg":"http://digg.com/submit?phase=2&url=%e",
		"bloglines":"http://www.bloglines.com/search?q=Bcite:%e&ql=en&s=f&pop=l&news=m", "technorati":"http://www.technorati.com/search/%e", 
		"magnolia":"http://ma.gnolia.com/meta/get?url=%e&title=title", "google":"http://blogsearch.google.com/blogsearch?q=link:%e",
		"delicious":"http://del.icio.us/url/?url=%e"};
		var metrics = document.getElementById('postrank_metrics');
		metrics.innerHTML = '<table><tr></tr><tr></tr><tr></tr></table>';
		var rows = metrics.getElementsByTagName('tr');
		var length = 0;
		var currentRow = 0;
		for(var source in data)
		{
			if(!data[source] || data[source] < 1) {continue;}
			var sourceName = source.substr(0,1).toUpperCase() + source.substr(1).replace(/_/,' ');
			var el = document.createElement('td');
			if(sources[source]) {
				el.innerHTML = '<a style="position:relative;z-index:9;" title="' + sourceName + '" class="metric ' + source + '" href="' + sources[source].replace('%r', metrics.getAttribute('url')).replace('%e', encodeURIComponent(metrics.getAttribute('url'))) + '">' + data[source] + '</a>';
			} else {
				el.innerHTML = '<span style="position:relative;z-index:9;" title="' + sourceName + '" class="metric ' + source + '">' + data[source] + '</span>';
			}
			rows[currentRow].appendChild(el);
			currentRow++;
			if(currentRow >= rows.length) currentRow = 0;
			length++;
		}
		if(length < 1) metrics.innerHTML = '<div style="padding:10px;">No Data</div>';
		var wrapper = document.createElement('div');
		wrapper.style.position = 'absolute';
		wrapper.style.top = '-2px';
		wrapper.style.bottom = '0px';
		wrapper.style.left = '0px';
		wrapper.style.right = '0px';
		wrapper.className = 'metric';
		wrapper.style.zIndex = 5;
		wrapper.onmouseout = function(e)
		{
			if(!e.relatedTarget.className.match(/metric/))
				this.parentNode.parentNode.removeChild(this.parentNode);
		};
		metrics.appendChild(wrapper);
		metrics.style.marginLeft = (38 - metrics.offsetWidth) + 'px';
		//wrapper.style.top = (-1*wrapper.parentNode.previousSibling.offsetHeight) + 'px'; // extend wrapper to cover postrank badge
		document.getElementById('postrank_metrics').style.display = 'block';
	}

	function postrank_badge_setup()
	{
		var badges = document.getElementsByClassName('postrank-badge');
		for(var i = 0; i < badges.length; i++)
		{
			badges[i].onmouseover = function()
			{
				this.onmouseout = function(e) {
					if(!e.relatedTarget.className.match(/metric/))
						document.getElementById('postrank_metrics').parentNode.removeChild(document.getElementById('postrank_metrics'));
				};
				var metrics = document.getElementById('postrank_metrics') || document.createElement('div');
				metrics.style.position = 'absolute';
				metrics.style.marginTop = this.offsetHeight + 'px';
				metrics.style.zIndex = 10;
				metrics.id = 'postrank_metrics';
				metrics.setAttribute('url', this.lastChild.innerHTML);
				this.parentNode.appendChild(metrics);
				var script = document.createElement('script');
				script.type = 'text/javascript';
				script.src = 'http://api.postrank.com/v2/entry/' + this.id.match(/^entry-(.*)$/)[1] + '/metrics?format=json&appkey=wordpress&callback=postrank_badge_step2';
				document.body.appendChild(script);
			};
		}
	}

	//adds an onload event to the current page
	function addLoadEvent(func)
	{
		var oldonload = window.onload;
		if (typeof(window.onload) != 'function')
		{
			window.onload = func;
		}
		else
		{
			window.onload = function()
			{
				oldonload();
				func();
			}
		}
	}
	addLoadEvent(postrank_badge_setup);
	//]]>
	</script>
<?php
}

function postrank_badge_css()
{
	$image = get_postrank_path() . '/images/corners.png';
?>
	<style type="text/css">
	a.postrank {
		border:1px solid #333333;
		color:#000000;
		display:block;
		float:right;
		font-size:1.2em;
		font-weight:bold;
		margin: 0 0.2em;
		position:relative;
		text-align:center;
		width:34px;
	}

	a.postrank span.topLeft, a.postrank span.topRight, a.postrank span.bottomLeft, a.postrank span.bottomRight {
		background:transparent url(<?php echo $image ?>) no-repeat scroll 0 0;
		display:block;
		height:3px;
		position:absolute;
		width:3px;
	}

	a.postrank span.topLeft {
		background-position:0 0;
		left:-1px;
		top:-1px;
	}

	a.postrank span.topRight {
		background-position:-3px 0;
		right:-1px;
		top:-1px;
	}

	a.postrank span.bottomLeft {
		background-position:0 -3px;
		left:-1px;
		bottom:-1px;
	}

	a.postrank span.bottomRight {
		background-position:-3px -3px;
		right:-1px;
		bottom:-1px;
	}

	/* metrics */
	#postrank_metrics { margin: 3px; background-color: #f6faff; border: 1px #c3d9ff solid; }
	#postrank_metrics table{ margin: 5px 0; font-size: 1.3em; border-collapse: collapse; }
	#postrank_metrics table td{ padding: 7px 11px 9px 14px; border: #c3d9ff solid; border-width: 0 0 0 1px; }
	#postrank_metrics table td:first-child{ border: 0; padding-left: 11px; }
	#postrank_metrics table td a, #postrank_metrics table td span{ padding: 1px 0 0 21px; background: url( http://postrank.com/graphics/spriteSet_3.png ) no-repeat; height: 15px; display: block; }
	#postrank_metrics .bloglines{ background-position: 0 -16px; }
	#postrank_metrics .reddit, #postrank_metrics .reddit_votes { background-position: 0 -32px; }
	#postrank_metrics .technorati{ background-position: 0 -48px; }
	#postrank_metrics .reddit_comments { background-position: 0 -416px; }
	#postrank_metrics .magnolia{ background-position: 0 -64px; }
	#postrank_metrics .digg{ background-position: 0 -80px; }
	#postrank_metrics .twitter{ background-position: 0 -96px; }
	#postrank_metrics .comments{ background-position: 0 -112px; }
	#postrank_metrics .icerocket{ background-position: 0 -128px; }
	#postrank_metrics .delicious{ background-position: 0 -144px; }
	#postrank_metrics .google{ background-position: 0 -160px; }
	#postrank_metrics .pownce{ background-position: 0 -176px; }
	#postrank_metrics .views{ background-position: 0 -192px; }
	#postrank_metrics .bookmarks{ background-position: 0 -208px; }
	#postrank_metrics .clicks{ background-position: 0 -224px; }
	#postrank_metrics .jaiku{ background-position: 0 -240px; }
	#postrank_metrics .identica{ background-position: 0 -352px; }
	#postrank_metrics .digg_comments{ background-position: 0 -256px; }
	#postrank_metrics .twitarmy{ background-position: 0 -336px; }
	#postrank_metrics .diigo{ background-position: 0 -272px; }
	#postrank_metrics .furl{ background-position: 0 -320px; }
	#postrank_metrics .brightkite{ background-position: 0 -304px; }
	#postrank_metrics .feecle{ background-position: 0 -288px; }
	#postrank_metrics .friendfeed_like{ background-position: 0 -368px; }
	#postrank_metrics .friendfeed_comm{ background-position: 0 -432px; }
	#postrank_metrics .blip{ background-position: 0 -384px; }
	#postrank_metrics .tumblr { background-position: 0 -400px; }
	</style>
<?php
}


/******************************
 * PostRank Library Functions *
 ******************************/

function get_postrank($post_id = false)
{
	if(get_option('postrank_db_version') != POSTRANK_DB_VERSION)
	{
		update_postrank_db();
	}

	global $post;

	$this_post = $post_id ? get_post($post_id) : $post;

	if($this_post->post_status != 'publish')
		return;

	$pr = get_postrank_from_cache($this_post->ID);
	if(!$pr)
		$pr = fetch_and_cache_postrank($this_post->ID);
	return $pr;
}

function postrank_guess_feed_hash()
{
	if(!get_option('postrank_feed_hash'))
	{
		global $postrank_json;
		$blogurl = get_bloginfo('url');
		postrank_debug("Getting feed_hash for $blogurl");
		$request_url = "http://api.postrank.com/v2/feed/info?appkey=" . POSTRANK_APPKEY . "&id={$blogurl}";
		$info = $postrank_json->decode(wp_remote_fopen($request_url),true);
		if($info['error'])
		{
			postrank_debug($info['error']);
			if($info['error'] == 'Collecting data')
			{
				add_option('postrank_importing',true);
				postrank_debug('Got collecting data message.  Will show importing  message');
			}
		}
		else
		{
			add_option('postrank_feed_hash',$info['id']);
			delete_option('postrank_importing');
			postrank_debug('Got feed_hash: ' . $info['id']);
		}
	}
}

// Retrieve previously cached PostRank
// Returns PostRank object, or null if PostRank is not cached
function get_postrank_from_cache($post_id)
{
	global $wpdb;
	$query = "SELECT postrank,color,seen FROM " . POSTRANK_TABLE_NAME . " WHERE post_id = {$post_id}";
	postrank_debug($query);
	$row = $wpdb->get_row($query, ARRAY_A);
	if($row)
	{
		$pr = new PostRank($row['postrank'],$row['color']);
		if($row['seen'] != '1')//Update seen status, since we just "saw" this PostRank
		{
			$query = "UPDATE " . POSTRANK_TABLE_NAME . " SET seen = true WHERE post_id = {$post_id}";
			postrank_debug($query);
			$result = $wpdb->query($query);
		}
	}
	return $pr;
}

// Get PostRank from api.postrank.com, then cache and return it.
function fetch_and_cache_postrank($post_id)
{
	$prs = bulk_fetch_and_cache_postrank(array($post_id));
	if(!$prs)
		return;
	return $prs[$post_id];
}


//Accepts an array of post ids
//Returns a hash of post ids => PostRank objects
function bulk_fetch_and_cache_postrank($post_ids)
{
	if(!function_exists('curl_init'))
		return;
	if(empty($post_ids))
		return;
	global $postrank_json;
	$feed_hash = get_option('postrank_feed_hash');
	if(!$feed_hash)
		return;

	foreach($post_ids as $post_id)
	{
		$post = get_post($post_id);
		$link = urlencode(get_permalink($post));
		$links .= "url[]=$link&";
	}

	$ch = curl_init('http://api.postrank.com/v1/postrank?appkey=' . POSTRANK_APPKEY);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,"{$links}feed_hash[]=$feed_hash");
	postrank_debug("Hitting PostRank API: {$links}feed_hash[]=$feed_hash");
	curl_setopt($ch, CURLOPT_HEADER,0);  // DO NOT RETURN HTTP HEADERS
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);  // RETURN THE CONTENTS OF THE CALL
	$data = $postrank_json->decode(curl_exec($ch),true);
	postrank_debug('PostRank API Returned: ' . print_r($data,true));

	$prs = Array();
	foreach($post_ids as $post_id)
	{
		$post = get_post($post_id);
		$link = get_permalink($post);
		if($data[$link] && $data[$link]['postrank'] && $data[$link]['postrank_color'])
		{
			$postrank = $data[$link]['postrank'];
			$postrank = sprintf("%.1f",$postrank);
			$color = $data[$link]['postrank_color'];
			$color = substr($color,1); // #abcabc -> abcabc
			$pr = new PostRank($postrank, $color);
			$prs[$post_id] = $pr;
			cache_postrank($post_id,$pr);
		}
		else
		{
			postrank_debug('Bad data returned by PostRank API');
		}
	}
	return $prs;
}

// Cache given PostRank
function cache_postrank($post_id,$pr)
{
	global $wpdb;
	$query = "REPLACE INTO " . POSTRANK_TABLE_NAME . " (post_id, postrank, color, seen) VALUES (" . $wpdb->escape($post_id) . "," . $wpdb->escape($pr->value()) . ",'" . $wpdb->escape($pr->color()) . "',false)";
	postrank_debug($query);
	$wpdb->query( $query );
}

function update_postrank_cache($all = false)
{
	global $wpdb;
	$query = "SELECT post_id from " . POSTRANK_TABLE_NAME . ($all ? "" : " where seen = true");
	postrank_debug($query);
	$col = $wpdb->get_col($query);

	if(empty($col))
		return;

	bulk_fetch_and_cache_postrank($col);		
}

function postrank_debug($message)
{
	if(!POSTRANK_DEBUG)
		return;

	$fh = @fopen(dirname(__FILE__) . '/postrank.log', 'a');
	if($fh)
	{
		fwrite($fh, date(DATE_RFC822) . ': ');
		fwrite($fh, $message . "\n");
		fclose($fh);
	}
}


/***********
 * Options *
 ***********/

function add_postrank_options()
{
	add_options_page('PostRank Options', 'PostRank', 8, __FILE__, 'postrank_options');
}

function postrank_options()
{
	?>
	<div class="wrap">

	<?php if(function_exists('screen_icon')) screen_icon(); ?>
	<h2>PostRank</h2>

	<form method="post" action="options.php">

	<?php wp_nonce_field('update-options'); ?>
	<table class="form-table">

	<tr valign="top">
	<th scope="row"><label for="postrank_feed_hash"><?php _e('Feed Hash'); ?></label></th>
	<td><input name="postrank_feed_hash" type="text" id="postrank_feed_hash" value="<?php echo attribute_escape(get_option('postrank_feed_hash')); ?>" class="code" size="32" />
	<span class="setting-description"><?php _e('If something is not working with the plugin, let us know on <a href="http://getsatisfaction.com/aiderss/">Get Satisfaction</a>, and we\'ll help you figure out what to put here.'); ?></span>
	</td>
	</tr>

	<tr>
	<th scope="row" colspan="2" class="th-full">
	<label for="postrank_display_admin">
	<input name="postrank_display_admin" type="checkbox" id="postrank_display_admin" value="1"<?php checked('1', get_option('postrank_display_admin')); ?> />
	<?php _e('Display PostRanks on Admin Pages') ?>
	</label>
	</th>
	</tr>

	</table>

	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="postrank_feed_hash,postrank_display_admin" />

	<p class="submit">
	<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
	</p>

	</form>

	</div>
	<?php
}


/*********************************
 * Installation & Uninstallation *
 *********************************/

function postrank_install()
{
	// Create or update the database table
	global $wpdb;
	if($wpdb->get_var("SHOW TABLES LIKE '" . POSTRANK_TABLE_NAME . "'") != POSTRANK_TABLE_NAME || get_option('postrank_db_version') != POSTRANK_DB_VERSION)
	{
		update_postrank_db();
	}

	// Set default options
	global $postrank_widget_options;
	$options = get_option('widget_postrank');
	if(is_array($options))
	{
		$options = array_merge($postrank_widget_options,$options);
	}
	else
	{
		$options = $postrank_widget_options;
	}
	update_option('widget_postrank', $options);

	if(function_exists('curl_init'))
	{
		add_option('postrank_display_admin',true);
	}
	else
	{
		// We can't directly display a message here, since the $postrank_messages global variable will be reset before postrank_show_messages can get to it.
		add_option('postrank_no_curl',true);
		delete_option('postrank_display_admin');
		postrank_debug('No cURL, will show message');
	}

	postrank_guess_feed_hash();
	
	wp_schedule_event(time(), 'hourly', 'postrank_hourly_cache_check');
}

function postrank_uninstall()
{
	wp_clear_scheduled_hook('postrank_hourly_cache_check');

	global $wpdb;
	$sql = "DROP TABLE " . POSTRANK_TABLE_NAME . ";";
	$wpdb->query($sql);
	delete_option('postrank_db_version');
}


/*********
 * Extra *
 *********/

function get_postrank_path()
{
	if(function_exists('plugins_url'))
		return plugins_url(dirname(plugin_basename(__FILE__)));

	return get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__));
}

function update_postrank_db()
{
	// http://codex.wordpress.org/Creating_Tables_with_Plugins
	$sql = "CREATE TABLE " . POSTRANK_TABLE_NAME . " (
		post_id bigint(20) UNSIGNED NOT NULL,
		postrank decimal(3,1) NOT NULL,
		color varchar(6),
		seen bool,
		UNIQUE KEY post_id (post_id)
	);";
	// Thanks to http://lesterchan.net
	if(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		require_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		require_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} else {
		die('Error finding \'/wp-admin/upgrade-functions.php\' or \'/wp-admin/includes/upgrade.php\'');
	}
	dbDelta($sql);
	add_option("postrank_db_version", POSTRANK_DB_VERSION);
}

/* Theme */
add_action('wp_head','postrank_badge_css');
add_action('wp_head','postrank_badge_javascript');

/* WP-Cron */
add_action('postrank_hourly_cache_check', 'update_postrank_cache');

/* Install */
register_activation_hook(__FILE__,'postrank_install');
register_deactivation_hook(__FILE__,'postrank_uninstall');
add_action('admin_head','postrank_check_status');
add_action('admin_notices','postrank_show_messages');

/* Admin PostRanks */
if(get_option('postrank_display_admin'))
{
	add_action('admin_head', 'postrank_admin_css');
	add_action('admin_head','postrank_badge_css');
	add_action('admin_head','postrank_badge_javascript');
	add_filter('manage_posts_columns', 'postrank_admin_add_column');
	add_action('manage_posts_custom_column','postrank_admin_show',10,2);
}

/* Options Page */
add_action('admin_menu', 'add_postrank_options');
?>
