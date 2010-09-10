<?php
// Produces links for every page just below the header
function veryplaintxt_globalnav() {
	echo "<div id=\"globalnav\"><ul id=\"menu\">";
	if ( !is_front_page() ) { ?><li class="page_item_home home-link"><a href="<?php bloginfo('home'); ?>/" title="<?php echo wp_specialchars(get_bloginfo('name'), 1) ?>" rel="home"><?php _e('Home', 'veryplaintxt') ?></a></li><?php }
	$menu = wp_list_pages('title_li=&sort_column=menu_order&echo=0'); // Params for the page list in header.php
	echo str_replace(array("\r", "\n", "\t"), '', $menu);
	echo "</ul></div>\n";
}

// Produces an hCard for the "admin" user
function veryplaintxt_admin_hCard() {
	global $wpdb, $user_info;
	$user_info = get_userdata(1);
	echo '<span class="vcard"><a class="url fn n" href="' . $user_info->user_url . '"><span class="given-name">' . $user_info->first_name . '</span> <span class="family-name">' . $user_info->last_name . '</span></a></span>';
}

// Produces an hCard for post authors
function veryplaintxt_author_hCard() {
	global $wpdb, $authordata;
	echo '<span class="entry-author author vcard"><a class="url fn n" href="' . get_author_link(false, $authordata->ID, $authordata->user_nicename) . '" title="View all posts by ' . $authordata->display_name . '">' . get_the_author() . '</a></span>';
}

// Produces semantic classes for the body element; Originally from the Sandbox, http://www.plaintxt.org/themes/sandbox/
function veryplaintxt_body_class( $print = true ) {
	global $wp_query, $current_user;

	$c = array('wordpress');

	veryplaintxt_date_classes(time(), $c);

	is_home()       ? $c[] = 'home'       : null;
	is_archive()    ? $c[] = 'archive'    : null;
	is_date()       ? $c[] = 'date'       : null;
	is_search()     ? $c[] = 'search'     : null;
	is_paged()      ? $c[] = 'paged'      : null;
	is_attachment() ? $c[] = 'attachment' : null;
	is_404()        ? $c[] = 'four04'     : null;

	if ( is_single() ) {
		the_post();
		$c[] = 'single';
		if ( isset($wp_query->post->post_date) )
			veryplaintxt_date_classes(mysql2date('U', $wp_query->post->post_date), $c, 's-');
		foreach ( (array) get_the_category() as $cat )
			$c[] = 's-category-' . $cat->category_nicename;
			$c[] = 's-author-' . get_the_author_login();
		rewind_posts();
	}

	else if ( is_author() ) {
		$author = $wp_query->get_queried_object();
		$c[] = 'author';
		$c[] = 'author-' . $author->user_nicename;
	}
	
	else if ( is_category() ) {
		$cat = $wp_query->get_queried_object();
		$c[] = 'category';
		$c[] = 'category-' . $cat->category_nicename;
	}

	else if ( is_page() ) {
		the_post();
		$c[] = 'page';
		$c[] = 'page-author-' . get_the_author_login();
		rewind_posts();
	}

	if ( $current_user->ID )
		$c[] = 'loggedin';
		
	$c = join(' ', apply_filters('body_class',  $c));

	return $print ? print($c) : $c;
}

// Produces semantic classes for the each individual post div; Originally from the Sandbox, http://www.plaintxt.org/themes/sandbox/
function veryplaintxt_post_class( $print = true ) {
	global $post, $veryplaintxt_post_alt;

	$c = array('hentry', "p$veryplaintxt_post_alt", $post->post_type, $post->post_status);

	$c[] = 'author-' . get_the_author_login();

	if ( is_attachment() )
		$c[] = 'attachment';

	foreach ( (array) get_the_category() as $cat )
		$c[] = 'category-' . $cat->category_nicename;

	veryplaintxt_date_classes(mysql2date('U', $post->post_date), $c);

	if ( ++$veryplaintxt_post_alt % 2 )
		$c[] = 'alt';
		
	$c = join(' ', apply_filters('post_class', $c));

	return $print ? print($c) : $c;
}
$veryplaintxt_post_alt = 1;

// Produces semantic classes for the each individual comment li; Originally from the Sandbox, http://www.plaintxt.org/themes/sandbox/
function veryplaintxt_comment_class( $print = true ) {
	global $comment, $post, $veryplaintxt_comment_alt;

	$c = array($comment->comment_type);

	if ( $comment->user_id > 0 ) {
		$user = get_userdata($comment->user_id);

		$c[] = "byuser commentauthor-$user->user_login";

		if ( $comment->user_id === $post->post_author )
			$c[] = 'bypostauthor';
	}

	veryplaintxt_date_classes(mysql2date('U', $comment->comment_date), $c, 'c-');
	if ( ++$veryplaintxt_comment_alt % 2 )
		$c[] = 'alt';

	$c[] = "c$veryplaintxt_comment_alt";

	if ( is_trackback() ) {
		$c[] = 'trackback';
	}

	$c = join(' ', apply_filters('comment_class', $c));

	return $print ? print($c) : $c;
}

// Produces date-based classes for the three functions above; Originally from the Sandbox, http://www.plaintxt.org/themes/sandbox/
function veryplaintxt_date_classes($t, &$c, $p = '') {
	$t = $t + (get_option('gmt_offset') * 3600);
	$c[] = $p . 'y' . gmdate('Y', $t);
	$c[] = $p . 'm' . gmdate('m', $t);
	$c[] = $p . 'd' . gmdate('d', $t);
	$c[] = $p . 'h' . gmdate('h', $t);
}

// Returns other categories except the current one (redundant); Originally from the Sandbox, http://www.plaintxt.org/themes/sandbox/
function veryplaintxt_other_cats($glue) {
	$current_cat = single_cat_title('', false);
	$separator = "\n";
	$cats = explode($separator, get_the_category_list($separator));

	foreach ( $cats as $i => $str ) {
		if ( strstr($str, ">$current_cat<") ) {
			unset($cats[$i]);
			break;
		}
	}

	if ( empty($cats) )
		return false;

	return trim(join($glue, $cats));
}

// Returns other tags except the current one (redundant); Originally from the Sandbox, http://www.plaintxt.org/themes/sandbox/
function veryplaintxt_other_tags($glue) {
	$current_tag = single_tag_title('', '',  false);
	$separator = "\n";
	$tags = explode($separator, get_the_tag_list("", "$separator", ""));

	foreach ( $tags as $i => $str ) {
		if ( strstr($str, ">$current_tag<") ) {
			unset($tags[$i]);
			break;
		}
	}

	if ( empty($tags) )
		return false;

	return trim(join($glue, $tags));
}

// Produces an avatar image with the hCard-compliant photo class
function veryplaintxt_commenter_link() {
	$commenter = get_comment_author_link();
	if ( ereg( '<a[^>]* class=[^>]+>', $commenter ) ) {
		$commenter = ereg_replace( '(<a[^>]* class=[\'"]?)', '\\1url ' , $commenter );
	} else {
		$commenter = ereg_replace( '(<a )/', '\\1class="url "' , $commenter );
	}
	$email = get_comment_author_email();
	$avatar_size = get_option('veryplaintxt_avatarsize');
	if ( empty($avatar_size) ) $avatar_size = '40';
	$avatar = str_replace( "class='avatar", "class='photo avatar", get_avatar( "$email", "$avatar_size" ) );
	echo $avatar . ' <span class="fn n">' . $commenter . '</span>';
}

// Function to filter the default gallery shortcode
function veryplaintxt_gallery($attr) {
	global $post;
	if ( isset($attr['orderby']) ) {
		$attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
		if ( !$attr['orderby'] )
			unset($attr['orderby']);
	}

	extract(shortcode_atts( array(
		'orderby'    => 'menu_order ASC, ID ASC',
		'id'         => $post->ID,
		'itemtag'    => 'dl',
		'icontag'    => 'dt',
		'captiontag' => 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
	), $attr ));

	$id           =  intval($id);
	$orderby      =  addslashes($orderby);
	$attachments  =  get_children("post_parent=$id&post_type=attachment&post_mime_type=image&orderby={$orderby}");

	if ( empty($attachments) )
		return null;

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $id => $attachment )
			$output .= wp_get_attachment_link( $id, $size, true ) . "\n";
		return $output;
	}

	$listtag     =  tag_escape($listtag);
	$itemtag     =  tag_escape($itemtag);
	$captiontag  =  tag_escape($captiontag);
	$columns     =  intval($columns);
	$itemwidth   =  $columns > 0 ? floor(100/$columns) : 100;

	$output = apply_filters( 'gallery_style', "\n" . '<div class="gallery">', 9 ); // Available filter: gallery_style

	foreach ( $attachments as $id => $attachment ) {
		$img_lnk = get_attachment_link($id);
		$img_src = wp_get_attachment_image_src( $id, $size );
		$img_src = $img_src[0];
		$img_alt = $attachment->post_excerpt;
		if ( $img_alt == null )
			$img_alt = $attachment->post_title;
		$img_rel = apply_filters( 'gallery_img_rel', 'attachment' ); // Available filter: gallery_img_rel
		$img_class = apply_filters( 'gallery_img_class', 'gallery-image' ); // Available filter: gallery_img_class

		$output  .=  "\n\t" . '<' . $itemtag . ' class="gallery-item gallery-columns-' . $columns .'">';
		$output  .=  "\n\t\t" . '<' . $icontag . ' class="gallery-icon"><a href="' . $img_lnk . '" title="' . $img_alt . '" rel="' . $img_rel . '"><img src="' . $img_src . '" alt="' . $img_alt . '" class="' . $img_class . ' attachment-' . $size . '" /></a></' . $icontag . '>';

		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "\n\t\t" . '<' . $captiontag . ' class="gallery-caption">' . $attachment->post_excerpt . '</' . $captiontag . '>';
		}

		$output .= "\n\t" . '</' . $itemtag . '>';
		if ( $columns > 0 && ++$i % $columns == 0 )
			$output .= "\n</div>\n" . '<div class="gallery">';
	}
	$output .= "\n</div>\n";

	return $output;
}


// Loads veryplaintxt-style Search widget
function widget_veryplaintxt_search($args) {
	extract($args);
	$options = get_option('widget_veryplaintxt_search');
	$title = empty($options['title']) ? __( 'Search', 'veryplaintxt' ) : $options['title'];
	$button = empty($options['button']) ? __( 'Find', 'veryplaintxt' ) : $options['button'];
?>
		<?php echo $before_widget ?>
				<?php echo $before_title ?><label for="s"><?php echo $title ?></label><?php echo $after_title ?>
			<form id="searchform" method="get" action="<?php bloginfo('home') ?>">
				<div>
					<input id="s" name="s" class="text-input" type="text" value="<?php the_search_query() ?>" size="10" tabindex="1" accesskey="S" />
					<input id="searchsubmit" name="searchsubmit" class="submit-button" type="submit" value="<?php echo $button ?>" tabindex="2" />
				</div>
			</form>
		<?php echo $after_widget ?>
<?php
}

// Widget: Search; element controls for customizing text within Widget plugin
function widget_veryplaintxt_search_control() {
	$options = $newoptions = get_option('widget_veryplaintxt_search');
	if ( $_POST['search-submit'] ) {
		$newoptions['title'] = strip_tags( stripslashes( $_POST['search-title'] ) );
		$newoptions['button'] = strip_tags( stripslashes( $_POST['search-button'] ) );
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option( 'widget_veryplaintxt_search', $options );
	}
	$title = attribute_escape( $options['title'] );
	$button = attribute_escape( $options['button'] );
?>
			<p><label for="search-title"><?php _e( 'Title:', 'veryplaintxt' ) ?> <input class="widefat" id="search-title" name="search-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="search-button"><?php _e( 'Button Text:', 'veryplaintxt' ) ?> <input class="widefat" id="search-button" name="search-button" type="text" value="<?php echo $button; ?>" /></label></p>
			<input type="hidden" id="search-submit" name="search-submit" value="1" />
<?php
}

// Loads veryplaintxt-style Meta widget
function widget_veryplaintxt_meta($args) {
	extract($args);
	$options = get_option('widget_meta');
	$title = empty($options['title']) ? __('Meta', 'veryplaintxt') : $options['title'];
?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul>
				<?php wp_register() ?>
				<li><?php wp_loginout() ?></li>
				<?php wp_meta() ?>
			</ul>
		<?php echo $after_widget; ?>
<?php
}

function widget_veryplaintxt_homelink($args) {
	extract($args);
	$options = get_option('widget_veryplaintxt_homelink');
	$title = empty($options['title']) ? __( 'Home', 'veryplaintxt' ) : $options['title'];
	if ( !is_front_page() || is_paged() ) {
?>
			<?php echo $before_widget; ?>
				<?php echo $before_title; ?><a href="<?php bloginfo('home'); ?>/" title="<?php echo wp_specialchars(get_bloginfo('name'), 1) ?>" rel="home"><?php echo $title; ?></a><?php echo $after_title; ?>
			<?php echo $after_widget; ?>
<?php }
}

// Loads the control functions for the Home Link, allowing control of its text
function widget_veryplaintxt_homelink_control() {
	$options = $newoptions = get_option('widget_veryplaintxt_homelink');
	if ( $_POST['homelink-submit'] ) {
		$newoptions['title'] = strip_tags( stripslashes( $_POST['homelink-title'] ) );
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option( 'widget_veryplaintxt_homelink', $options );
	}
	$title = attribute_escape( $options['title'] );
?>
			<p><?php _e('Adds a link to the home page on every page <em>except</em> the home.', 'veryplaintxt'); ?></p>
			<p><label for="homelink-title"><?php _e( 'Title:', 'veryplaintxt' ) ?> <input class="widefat" id="homelink-title" name="homelink-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<input type="hidden" id="homelink-submit" name="homelink-submit" value="1" />
<?php
}

function widget_veryplaintxt_rsslinks($args) {
	extract($args);
	$options = get_option('widget_veryplaintxt_rsslinks');
	$title = empty($options['title']) ? __( 'RSS Links', 'veryplaintxt' ) : $options['title'];
?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul>
				<li><a href="<?php bloginfo('rss2_url') ?>" title="<?php echo wp_specialchars( get_bloginfo('name'), 1 ) ?> <?php _e( 'Posts RSS feed', 'veryplaintxt' ); ?>" rel="alternate" type="application/rss+xml"><?php _e( 'All posts', 'veryplaintxt' ) ?></a></li>
				<li><a href="<?php bloginfo('comments_rss2_url') ?>" title="<?php echo wp_specialchars(bloginfo('name'), 1) ?> <?php _e( 'Comments RSS feed', 'veryplaintxt' ); ?>" rel="alternate" type="application/rss+xml"><?php _e( 'All comments', 'veryplaintxt' ) ?></a></li>
			</ul>
		<?php echo $after_widget; ?>
<?php
}

// Loads the control functions for the RSS Links, allowing control of its text
function widget_veryplaintxt_rsslinks_control() {
	$options = $newoptions = get_option('widget_veryplaintxt_rsslinks');
	if ( $_POST['rsslinks-submit'] ) {
		$newoptions['title'] = strip_tags( stripslashes( $_POST['rsslinks-title'] ) );
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option( 'widget_veryplaintxt_rsslinks', $options );
	}
	$title = attribute_escape( $options['title'] );
?>
			<p><label for="rsslinks-title"><?php _e( 'Title:', 'veryplaintxt' ) ?> <input class="widefat" id="rsslinks-title" name="rsslinks-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<input type="hidden" id="rsslinks-submit" name="rsslinks-submit" value="1" />
<?php
}

// Loads, checks that Widgets are loaded and working
function veryplaintxt_widgets_init() {
	if ( !function_exists('register_sidebars') )
		return;

	$p = array(
		'before_title' => "<h3 class='widgettitle'>",
		'after_title' => "</h3>\n",
	);

	register_sidebars(1, $p);

	// Finished intializing Widgets plugin, now let's load the veryplaintxt default widgets; first, veryplaintxt search widget
	$widget_ops = array(
		'classname'    =>  'widget_search',
		'description'  =>  __( "A search form for your blog (veryplaintxt)", "veryplaintxt" )
	);
	wp_register_sidebar_widget( 'search', __( 'Search', 'veryplaintxt' ), 'widget_veryplaintxt_search', $widget_ops );
	unregister_widget_control('search');
	wp_register_widget_control( 'search', __( 'Search', 'veryplaintxt' ), 'widget_veryplaintxt_search_control' );

	// veryplaintxt Meta widget
	$widget_ops = array(
		'classname'    =>  'widget_meta',
		'description'  =>  __( "Log in/out and administration links (veryplaintxt)", "veryplaintxt" )
	);
	wp_register_sidebar_widget( 'meta', __( 'Meta', 'veryplaintxt' ), 'widget_veryplaintxt_meta', $widget_ops );
	unregister_widget_control('meta');
	wp_register_widget_control( 'meta', __('Meta'), 'wp_widget_meta_control' );

	//veryplaintxt Home Link widget
	$widget_ops = array(
		'classname'    =>  'widget_home_link',
		'description'  =>  __( "Link to the front page when elsewhere (veryplaintxt)", "veryplaintxt" )
	);
	wp_register_sidebar_widget( 'home_link', __( 'Home Link', 'veryplaintxt' ), 'widget_veryplaintxt_homelink', $widget_ops );
	wp_register_widget_control( 'home_link', __( 'Home Link', 'veryplaintxt' ), 'widget_veryplaintxt_homelink_control' );

	//veryplaintxt RSS Links widget
	$widget_ops = array(
		'classname'    =>  'widget_rss_links',
		'description'  =>  __( "RSS links for both posts and comments (veryplaintxt)", "veryplaintxt" )
	);
	wp_register_sidebar_widget( 'rss_links', __( 'RSS Links', 'veryplaintxt' ), 'widget_veryplaintxt_rsslinks', $widget_ops );
	wp_register_widget_control( 'rss_links', __( 'RSS Links', 'veryplaintxt' ), 'widget_veryplaintxt_rsslinks_control' );
}

// Loads the admin menu; sets default settings for each
function veryplaintxt_add_admin() {
	if ( $_GET['page'] == basename(__FILE__) ) {
		if ( 'save' == $_REQUEST['action'] ) {
			check_admin_referer('veryplaintxt_save_options');
			update_option( 'veryplaintxt_basefontsize', strip_tags( stripslashes( $_REQUEST['vp_basefontsize'] ) ) );
			update_option( 'veryplaintxt_basefontfamily', strip_tags( stripslashes( $_REQUEST['vp_basefontfamily'] ) ) );
			update_option( 'veryplaintxt_headingfontfamily', strip_tags( stripslashes( $_REQUEST['vp_headingfontfamily'] ) ) );
			update_option( 'veryplaintxt_posttextalignment', strip_tags( stripslashes( $_REQUEST['vp_posttextalignment'] ) ) );
			update_option( 'veryplaintxt_layoutwidth', strip_tags( stripslashes( $_REQUEST['vp_layoutwidth'] ) ) );
			update_option( 'veryplaintxt_maxwidth', strip_tags( stripslashes( $_REQUEST['vp_maxwidth'] ) ) );
			update_option( 'veryplaintxt_minwidth', strip_tags( stripslashes( $_REQUEST['vp_minwidth'] ) ) );
			update_option( 'veryplaintxt_sidebarposition', strip_tags( stripslashes( $_REQUEST['vp_sidebarposition'] ) ) );
			update_option( 'veryplaintxt_sidebartextalignment', strip_tags( stripslashes( $_REQUEST['vp_sidebartextalignment'] ) ) );
			update_option( 'veryplaintxt_avatarsize', strip_tags( stripslashes( $_REQUEST['vp_avatarsize'] ) ) );
			header("Location: themes.php?page=functions.php&saved=true");
			die;
		} else if( 'reset' == $_REQUEST['action'] ) {
			check_admin_referer('veryplaintxt_reset_options');
			delete_option('veryplaintxt_basefontsize');
			delete_option('veryplaintxt_basefontfamily');
			delete_option('veryplaintxt_headingfontfamily');
			delete_option('veryplaintxt_posttextalignment');
			delete_option('veryplaintxt_layoutwidth');
			delete_option('veryplaintxt_maxwidth');
			delete_option('veryplaintxt_minwidth');
			delete_option('veryplaintxt_sidebarposition');
			delete_option('veryplaintxt_sidebartextalignment');
			delete_option('veryplaintxt_avatarsize');
			header("Location: themes.php?page=functions.php&reset=true");
			die;
		}
		add_action('admin_head', 'veryplaintxt_admin_head');
	}
	add_theme_page( __( 'veryplaintxt Theme Options', 'veryplaintxt' ), __( 'Theme Options', 'veryplaintxt' ), 'edit_themes', basename(__FILE__), 'veryplaintxt_admin' );
}

function veryplaintxt_donate() { 
	$form = '<form id="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<div id="donate">
			<input type="hidden" name="cmd" value="_s-xclick" />
			<input type="image" name="submit" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" alt="Donate with PayPal - it\'s fast, free and secure!" />
			<img src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" alt="Donate with PayPal" />
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----" />
		</div>
	</form>' . "\n\t";
	echo $form;
}

function veryplaintxt_admin_head() {
// Additional CSS styles for the theme options menu
?>
<style type="text/css" media="screen,projection">
/*<![CDATA[*/
	p.info span{font-weight:bold;}
	label.arial,label.courier,label.georgia,label.lucida-console,label.lucida-unicode,label.tahoma,label.times,label.trebuchet,label.verdana{font-size:1.2em;line-height:175%;}
	.arial{font-family:arial,helvetica,sans-serif;}
	.courier{font-family:'courier new',courier,monospace;}
	.georgia{font-family:georgia,times,serif;}
	.lucida-console{font-family:'lucida console',monaco,monospace;}
	.lucida-unicode{font-family:'lucida sans unicode','lucida grande',sans-serif;}
	.tahoma{font-family:tahoma,geneva,sans-serif;}
	.times{font-family:'times new roman',times,serif;}
	.trebuchet{font-family:'trebuchet ms',helvetica,sans-serif;}
	.verdana{font-family:verdana,geneva,sans-serif;}
	form#paypal{float:right;margin:1em 0 0.5em 1em;}
/*]]>*/
</style>
<?php
}

function veryplaintxt_admin() { // Theme options menu 
	if ( $_REQUEST['saved'] ) { ?><div id="message1" class="updated fade"><p><?php printf(__('Veryplaintxt theme options saved. <a href="%s">View site.</a>', 'veryplaintxt'), get_bloginfo('home') . '/'); ?></p></div><?php }
	if ( $_REQUEST['reset'] ) { ?><div id="message2" class="updated fade"><p><?php _e('Veryplaintxt theme options reset.', 'veryplaintxt'); ?></p></div><?php }

	$check = ' checked="checked"';
	$basefont = get_option('veryplaintxt_basefontfamily');
	$headfont = get_option('veryplaintxt_headingfontfamily');
?>

<div class="wrap">

	<h2><?php _e('Veryplaintxt Theme Options', 'veryplaintxt'); ?></h2>
	<?php printf( __('%1$s<p>Thanks for selecting the <a href="http://www.plaintxt.org/themes/veryplaintxt/" title="Veryplaintxt theme for WordPress">veryplaintxt</a> theme by <span class="vcard"><a class="url fn n" href="http://scottwallick.com/" title="scottwallick.com" rel="me designer"><span class="given-name">Scott</span> <span class="additional-name">Allan</span> <span class="family-name">Wallick</span></a></span>. Please read the included <a href="%2$s" title="Open the readme.html" rel="enclosure" id="readme">documentation</a> for more information about veryplaintxt and its advanced features. <strong>If you find this theme useful, please consider <label for="paypal">donating</label>.</strong> You must click on <i><u>S</u>ave Options</i> to save any changes. You can also discard your changes and reload the default settings by clicking on <i><u>R</u>eset</i>.</p>', 'veryplaintxt'), veryplaintxt_donate(), get_template_directory_uri() . '/readme.html' ); ?>

	<form action="<?php echo wp_specialchars( $_SERVER['REQUEST_URI'] ) ?>" method="post">
		<?php wp_nonce_field('veryplaintxt_save_options'); echo "\n"; ?>
		<h3><?php _e('Typography', 'veryplaintxt'); ?></h3>
		<table class="form-table" summary="Veryplaintxt typography options">
			<tr valign="top">
				<th scope="row"><label for="vp_basefontsize"><?php _e('Base font size', 'veryplaintxt'); ?></label></th> 
				<td>
					<input id="vp_basefontsize" name="vp_basefontsize" type="text" class="text" value="<?php if ( get_option('veryplaintxt_basefontsize') == "" ) { echo "90%"; } else { echo attribute_escape( get_option('veryplaintxt_basefontsize') ); } ?>" tabindex="1" size="10" />
					<p class="info"><?php _e('The base font size globally affects the size of text throughout your blog. This can be in any unit (e.g., px, pt, em), but I suggest using a percentage (%). Default is 90%.', 'veryplaintxt'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Base font family', 'veryplaintxt'); ?></th> 
				<td>
					<input id="vp_basefontArial" name="vp_basefontfamily" type="radio" class="radio" value="1"<?php if ( $basefont == 1 ) echo $check; ?> tabindex="2" /> <label for="vp_basefontArial" class="arial">Arial</label><br />
					<input id="vp_basefontCourier" name="vp_basefontfamily" type="radio" class="radio" value="2"<?php if ( $basefont == 2 ) echo $check; ?> tabindex="3" /> <label for="vp_basefontCourier" class="courier">Courier</label><br />
					<input id="vp_basefontGeorgia" name="vp_basefontfamily" type="radio" class="radio" value="3"<?php if ( $basefont == 3 ) echo $check; ?> tabindex="4" /> <label for="vp_basefontGeorgia" class="georgia">Georgia</label><br />
					<input id="vp_basefontLucidaConsole" name="vp_basefontfamily" type="radio" class="radio" value="4"<?php if ( $basefont == 4 ) echo $check; ?> tabindex="5" /> <label for="vp_basefontLucidaConsole" class="lucida-console">Lucida Console</label><br />
					<input id="vp_basefontLucidaUnicode" name="vp_basefontfamily" type="radio" class="radio" value="5"<?php if ( $basefont == 5 ) echo $check; ?> tabindex="6" /> <label for="vp_basefontLucidaUnicode" class="lucida-unicode">Lucida Sans Unicode</label><br />
					<input id="vp_basefontTahoma" name="vp_basefontfamily" type="radio" class="radio" value="6"<?php if ( $basefont == 6 ) echo $check; ?> tabindex="7" /> <label for="vp_basefontTahoma" class="tahoma">Tahoma</label><br />
					<input id="vp_basefontTimes" name="vp_basefontfamily" type="radio" class="radio" value="7"<?php if ( ( $basefont == '' ) || ( $basefont == 7 ) ) echo $check; ?> tabindex="8" /> <label for="vp_basefontTimes" class="times">Times</label><br />
					<input id="vp_basefontTrebuchetMS" name="vp_basefontfamily" type="radio" class="radio" value="8"<?php if ( $basefont == 8 ) echo $check; ?> tabindex="9" /> <label for="vp_basefontTrebuchetMS" class="trebuchet">Trebuchet MS</label><br />
					<input id="vp_basefontVerdana" name="vp_basefontfamily" type="radio" class="radio" value="9"<?php if ( $basefont == 9 ) echo $check; ?> tabindex="10" /> <label for="vp_basefontVerdana" class="verdana">Verdana</label>
					<p class="info"><?php printf(__('The base font family sets the font for everything except content headings. The selection is limited to %1$s fonts, as they will display correctly. Default is <span class="times">Times</span>.', 'veryplaintxt'), '<cite><a href="http://en.wikipedia.org/wiki/Web_safe_fonts" title="Web safe fonts - Wikipedia">web safe</a></cite>'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Heading font family', 'veryplaintxt'); ?></th> 
				<td>
					<input id="vp_headingfontArial" name="vp_headingfontfamily" type="radio" class="radio" value="1"<?php if ( ( $headfont == '' ) || ( $headfont == 1 ) ) echo $check; ?> tabindex="2" /> <label for="vp_headingfontArial" class="arial">Arial</label><br />
					<input id="vp_headingfontCourier" name="vp_headingfontfamily" type="radio" class="radio" value="2"<?php if ( $headfont == 2 ) echo $check; ?> tabindex="3" /> <label for="vp_headingfontCourier" class="courier">Courier</label><br />
					<input id="vp_headingfontGeorgia" name="vp_headingfontfamily" type="radio" class="radio" value="3"<?php if ( $headfont == 3 ) echo $check; ?> tabindex="4" /> <label for="vp_headingfontGeorgia" class="georgia">Georgia</label><br />
					<input id="vp_headingfontLucidaConsole" name="vp_headingfontfamily" type="radio" class="radio" value="4"<?php if ( $headfont == 4 ) echo $check; ?> tabindex="5" /> <label for="vp_headingfontLucidaConsole" class="lucida-console">Lucida Console</label><br />
					<input id="vp_headingfontLucidaUnicode" name="vp_headingfontfamily" type="radio" class="radio" value="5"<?php if ( $headfont == 5 ) echo $check; ?> tabindex="6" /> <label for="vp_headingfontLucidaUnicode" class="lucida-unicode">Lucida Sans Unicode</label><br />
					<input id="vp_headingfontTahoma" name="vp_headingfontfamily" type="radio" class="radio" value="6"<?php if ( $headfont == 6 ) echo $check; ?> tabindex="7" /> <label for="vp_headingfontTahoma" class="tahoma">Tahoma</label><br />
					<input id="vp_headingfontTimes" name="vp_headingfontfamily" type="radio" class="radio" value="7"<?php if ( $headfont == 7 ) echo $check; ?> tabindex="8" /> <label for="vp_headingfontTimes" class="times">Times</label><br />
					<input id="vp_headingfontTrebuchetMS" name="vp_headingfontfamily" type="radio" class="radio" value="8"<?php if ( $headfont == 8 ) echo $check; ?> tabindex="9" /> <label for="vp_headingfontTrebuchetMS" class="trebuchet">Trebuchet MS</label><br />
					<input id="vp_headingfontVerdana" name="vp_headingfontfamily" type="radio" class="radio" value="9"<?php if ( $headfont == 9 ) echo $check; ?> tabindex="10" /> <label for="vp_headingfontVerdana" class="verdana">Verdana</label>
					<p class="info"><?php printf(__('The heading font family sets the font for all content headings. The selection is limited to %1$s fonts, as they will display correctly. Default is <span class="arial">Arial</span>. ', 'veryplaintxt'), '<cite><a href="http://en.wikipedia.org/wiki/Web_safe_fonts" title="Web safe fonts - Wikipedia">web safe</a></cite>'); ?></p>
				</td>
			</tr>
		</table>
		<h3><?php _e('Layout', 'veryplaintxt'); ?></h3>
		<table class="form-table" summary="Veryplaintxt layout options">
			<tr valign="top">
				<th scope="row"><label for="vp_layoutwidth"><?php _e('Layout width', 'veryplaintxt'); ?></label></th> 
				<td>
					<input id="vp_layoutwidth" name="vp_layoutwidth" type="text" class="text" value="<?php if ( get_option('veryplaintxt_layoutwidth') == "" ) { echo "80%"; } else { echo attribute_escape( get_option('veryplaintxt_layoutwidth') ); } ?>" tabindex="20" size="10" />
					<p class="info"><?php _e('The layout width determines the normal width of the entire layout. This can be in any unit (e.g., px, pt, em), but I suggest using a percentage (%). Default is <span>80%</span>.', 'veryplaintxt'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="vp_maxwidth"><?php _e('Maximum width', 'veryplaintxt'); ?></label></th> 
				<td>
					<input id="vp_maxwidth" name="vp_maxwidth" type="text" class="text" value="<?php if ( get_option('veryplaintxt_maxwidth') == "" ) { echo "55em"; } else { echo attribute_escape( get_option('veryplaintxt_maxwidth') ); } ?>" tabindex="21" size="10" />
					<p class="info"><?php _e('The maximum width determines how wide the layout can be. When viewed in a large screen, this keeps text lines from running long (i.e., difficult hard to read). Note that this has no effect in Internet Explorer 6 and below. You may leave this blank. Default is <span>55em</span>.', 'veryplaintxt'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="vp_minwidth"><?php _e('Minimum width', 'veryplaintxt'); ?></label></th> 
				<td>
					<input id="vp_minwidth" name="vp_minwidth" type="text" class="text" value="<?php if ( get_option('veryplaintxt_minwidth') == "" ) { echo "35em"; } else { echo attribute_escape( get_option('veryplaintxt_minwidth') ); } ?>" tabindex="22" size="10" />
					<p class="info"><?php _e('The minimum width determines how narrow the layout can be. When viewed in a small area, this keeps the layout from becoming too narrow. Note that this has no effect in Internet Explorer 6 and below. You may leave this blank. Default is <span>35em</span>.', 'veryplaintxt'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="vp_posttextalignment"><?php _e('Post text alignment', 'veryplaintxt'); ?></label></th> 
				<td>
					<select id="vp_posttextalignment" name="vp_posttextalignment" tabindex="23" class="dropdown">
						<option value="center" <?php if ( get_option('veryplaintxt_posttextalignment') == "center" ) { echo 'selected="selected"'; } ?>><?php _e('Centered', 'veryplaintxt'); ?> </option>
						<option value="justify" <?php if ( get_option('veryplaintxt_posttextalignment') == "justify" ) { echo 'selected="selected"'; } ?>><?php _e('Justified', 'veryplaintxt'); ?> </option>
						<option value="left" <?php if ( ( get_option('veryplaintxt_posttextalignment') == "") || ( get_option('veryplaintxt_posttextalignment') == "left") ) { echo 'selected="selected"'; } ?>><?php _e('Left', 'veryplaintxt'); ?> </option>
						<option value="right" <?php if ( get_option('veryplaintxt_posttextalignment') == "right" ) { echo 'selected="selected"'; } ?>><?php _e('Right', 'veryplaintxt'); ?> </option>
					</select>
					<p class="info"><?php _e('Choose one of the options for the alignment of the post entry text. Default is <span>left</span>.', 'veryplaintxt'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="vp_sidebarposition"><?php _e('Sidebar position', 'veryplaintxt'); ?></label></th> 
				<td>
					<select id="vp_sidebarposition" name="vp_sidebarposition" tabindex="24" class="dropdown">
						<option value="left" <?php if ( get_option('veryplaintxt_sidebarposition') == "left" ) { echo 'selected="selected"'; } ?>><?php _e('Left of content', 'veryplaintxt'); ?> </option>
						<option value="right" <?php if ( ( get_option('veryplaintxt_sidebarposition') == "") || ( get_option('veryplaintxt_sidebarposition') == "right") ) { echo 'selected="selected"'; } ?>><?php _e('Right of content', 'veryplaintxt'); ?> </option>
					</select>
					<p class="info"><?php _e('The sidebar can be positioned to the left or the right of the main content column. Default is <span>right of content</span>.', 'veryplaintxt'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="vp_sidebartextalignment" class="dropdown"><?php _e('Sidebar text alignment', 'veryplaintxt'); ?></label></th> 
				<td>
					<select id="vp_sidebartextalignment" name="vp_sidebartextalignment" tabindex="25" class="dropdown">
						<option value="center" <?php if ( ( get_option('veryplaintxt_sidebartextalignment') == "") || ( get_option('veryplaintxt_sidebartextalignment') == "center") ) { echo 'selected="selected"'; } ?>><?php _e('Centered', 'veryplaintxt'); ?> </option>
						<option value="left" <?php if ( get_option('veryplaintxt_sidebartextalignment') == "left" ) { echo 'selected="selected"'; } ?>><?php _e('Left', 'veryplaintxt'); ?> </option>
						<option value="right" <?php if ( get_option('veryplaintxt_sidebartextalignment') == "right" ) { echo 'selected="selected"'; } ?>><?php _e('Right', 'veryplaintxt'); ?> </option>
					</select>
					<p class="info"><?php _e('Choose one of the options for the alignment of the sidebar text. Default is <span>centered</span>.', 'veryplaintxt'); ?></p>
				</td>
			</tr>
		</table>
		<h3><?php _e('Content', 'veryplaintxt'); ?></h3>
		<table class="form-table" summary="Veryplaintxt content options">
			<tr valign="top">
				<th scope="row"><label for="vp_avatarsize"><?php _e('Avatar size', 'plaintxtblog'); ?></label></th> 
				<td>
					<input id="vp_avatarsize" name="vp_avatarsize" type="text" class="text" value="<?php if ( get_option('veryplaintxt_avatarsize') == "" ) { echo "40"; } else { echo attribute_escape( get_option('veryplaintxt_avatarsize') ); } ?>" size="6" />
					<p class="info"><?php _e('Sets the avatar size in pixels, if avatars are enabled. Default is <span>40</span>.', 'veryplaintxt'); ?></p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input name="save" type="submit" value="<?php _e('Save Options', 'veryplaintxt'); ?>" tabindex="26" accesskey="S" />  
			<input name="action" type="hidden" value="save" />
			<input name="page_options" type="hidden" value="vp_basefontsize,vp_basefontfamily,vp_headingfontfamily,vp_posttextalignment,vp_layoutwidth,vp_maxwidth,vp_minwidth,vp_sidebarposition,vp_sidebartextalignment,vp_avatarsize" />
		</p>
	</form>
	<h3 id="reset"><?php _e('Reset Options', 'veryplaintxt'); ?></h3>
	<p><?php _e('Resetting deletes all stored veryplaintxt options from your database. After resetting, default options are loaded but are not stored until you click <i>Save Options</i>. A reset does not affect the actual theme files in any way. If you are uninstalling veryplaintxt, please reset before removing the theme files to clear your databse.', 'veryplaintxt'); ?></p>
	<form action="<?php echo wp_specialchars( $_SERVER['REQUEST_URI'] ) ?>" method="post">
		<?php wp_nonce_field('veryplaintxt_reset_options'); echo "\n"; ?>
		<p class="submit">
			<input name="reset" type="submit" value="<?php _e('Reset Options', 'veryplaintxt'); ?>" onclick="return confirm('<?php _e('Click OK to reset. Any changes to these theme options will be lost!', 'veryplaintxt'); ?>');" tabindex="27" accesskey="R" />
			<input name="action" type="hidden" value="reset" />
			<input name="page_options" type="hidden" value="vp_basefontsize,vp_basefontfamily,vp_headingfontfamily,vp_posttextalignment,vp_layoutwidth,vp_maxwidth,vp_minwidth,vp_sidebarposition,vp_sidebartextalignment,vp_avatarsize" />
		</p>
	</form>
</div>
<?php
}
// Loads settings for the theme options to use
function veryplaintxt_wp_head() {
	// Our Web-safe fonts
	$arial     = "arial,helvetica,sans-serif";
	$courier   = "'courier new',courier,monospace";
	$georgia   = "georgia,times,serif";
	$lconsole  = "'lucida console',monaco,monospace";
	$lunicode  = "'lucida sans unicode','lucida grande',sans-serif";
	$tahoma    = "tahoma,geneva,sans-serif";
	$times     = "'times new roman',times,serif";
	$trebuchet = "'trebuchet ms',helvetica,sans-serif";
	$verdana   = "verdana,geneva,sans-serif";
	// Let's start inserting options
	if ( get_option('veryplaintxt_basefontsize') == '' ) {
		$basefontsize = '90%';
	} else {
		$basefontsize = attribute_escape( stripslashes( get_option('veryplaintxt_basefontsize') ) ); 
	}
	$vp_basefontfamily = get_option('veryplaintxt_basefontfamily');
	if ( $vp_basefontfamily == '' ) {
		$basefontfamily = $times;
	} else {
		if ( $vp_basefontfamily == 1 ) {
			$basefontfamily = $arial;
		} elseif ( $vp_basefontfamily == 2 ) {
			$basefontfamily = $courier;
		} elseif ( $vp_basefontfamily == 3 ) {
			$basefontfamily = $georgia;
		} elseif ( $vp_basefontfamily == 4 ) {
			$basefontfamily = $lconsole;
		} elseif ( $vp_basefontfamily == 5 ) {
			$basefontfamily = $lunicode;
		} elseif ( $vp_basefontfamily == 6 ) {
			$basefontfamily = $tahoma;
		} elseif ( $vp_basefontfamily == 7 ) {
			$basefontfamily = $times;
		} elseif ( $vp_basefontfamily == 8 ) {
			$basefontfamily = $trebuchet;
		} elseif ( $vp_basefontfamily == 9 ) {
			$basefontfamily = $verdana;
		}
	}
	$vp_headfontfamily = get_option('veryplaintxt_headingfontfamily');
	if ( $vp_headfontfamily == '' ) {
		$headingfontfamily = $arial;
	} else {
		if ( $vp_headfontfamily == 1 ) {
			$headingfontfamily = $arial;
		} elseif ( $vp_headfontfamily == 2 ) {
			$headingfontfamily = $courier;
		} elseif ( $vp_headfontfamily == 3 ) {
			$headingfontfamily = $georgia;
		} elseif ( $vp_headfontfamily == 4 ) {
			$headingfontfamily = $lconsole;
		} elseif ( $vp_headfontfamily == 5 ) {
			$headingfontfamily = $lunicode;
		} elseif ( $vp_headfontfamily == 6 ) {
			$headingfontfamily = $tahoma;
		} elseif ( $vp_headfontfamily == 7 ) {
			$headingfontfamily = $times;
		} elseif ( $vp_headfontfamily == 8 ) {
			$headingfontfamily = $trebuchet;
		} elseif ( $vp_headfontfamily == 9 ) {
			$headingfontfamily = $verdana;
		}
	}
	if ( get_option('veryplaintxt_layoutwidth') == "" ) {
		$layoutwidth = '80%';
	} else {
		$layoutwidth = attribute_escape( stripslashes( get_option('veryplaintxt_layoutwidth') ) ); 
	}
	if ( get_option('veryplaintxt_maxwidth') == "" ) {
		$maxwidth = '55em';
	} else {
		$maxwidth = attribute_escape( stripslashes( get_option('veryplaintxt_maxwidth') ) ); 
	}
	if ( get_option('veryplaintxt_minwidth') == "" ) {
		$minwidth = '35em';
	} else {
		$minwidth = attribute_escape( stripslashes( get_option('veryplaintxt_minwidth') ) ); 
	}
	if ( get_option('veryplaintxt_posttextalignment') == "" ) {
		$posttextalignment = 'left';
	} else {
		$posttextalignment = attribute_escape( stripslashes( get_option('veryplaintxt_posttextalignment') ) ); 
	}
	if ( get_option('veryplaintxt_sidebarposition') == "" ) {
		$sidebarposition = 'body div#container { float: left; margin: 0 -200px 2em 0; } body div#content { margin: 3em 200px 0 0; } body div.sidebar { float: right; }
';
	} elseif ( get_option('veryplaintxt_sidebarposition') =="left" ) {
		$sidebarposition = 'body div#container { float: right; margin: 0 0 2em -200px; } body div#content { margin: 3em 0 0 200px; } body div.sidebar { float: left; }
';
	} elseif ( get_option('veryplaintxt_sidebarposition') =="right" ) {
		$sidebarposition = 'body div#container { float: left; margin: 0 -200px 2em 0; } body div#content { margin: 3em 200px 0 0; } body div.sidebar { float: right; }
';
	}
	if ( get_option('veryplaintxt_sidebartextalignment') == "" ) {
		$sidebartextalignment = 'center';
	} else {
		$sidebartextalignment = attribute_escape( stripslashes( get_option('veryplaintxt_sidebartextalignment') ) ); 
	}
?>

<style type="text/css" media="all">
/*<![CDATA[*/
/* CSS inserted by theme options */
body{font-family:<?php echo $basefontfamily; ?>;font-size:<?php echo $basefontsize; ?>;}
<?php echo $sidebarposition; ?>
body div#content div.hentry{text-align:<?php echo $posttextalignment; ?>;}
body div#content h2,div#content h3,div#content h4,div#content h5,div#content h6{font-family:<?php echo $headingfontfamily; ?>;}
body div#wrapper{max-width:<?php echo $maxwidth; ?>;min-width:<?php echo $minwidth; ?>;width:<?php echo $layoutwidth; ?>;}
body div.sidebar{text-align:<?php echo $sidebartextalignment; ?>;}
/*]]>*/
</style>
<?php // Checks that everything has loaded properly
}

add_action('admin_menu', 'veryplaintxt_add_admin');
add_action('wp_head', 'veryplaintxt_wp_head');
add_action('init', 'veryplaintxt_widgets_init');

add_filter('archive_meta', 'wptexturize');
add_filter('archive_meta', 'convert_smilies');
add_filter('archive_meta', 'convert_chars');
add_filter('archive_meta', 'wpautop');

add_shortcode('gallery', 'veryplaintxt_gallery', $attr);

// Readies for translation.
load_theme_textdomain('veryplaintxt');
?>