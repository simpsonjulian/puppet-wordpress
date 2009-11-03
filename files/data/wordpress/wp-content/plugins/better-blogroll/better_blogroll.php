<?php
/*
Plugin Name: Better Blogroll
Plugin URI: http://www.dyers.org/blog/better-blogroll-widget-for-wordpress/
Description: Pulls a configurable number of links and their categories from the WordPress Link Manager and gives you more control of your blogroll.
Author: Jon Dyer
Version: 2.9
Author URI: http://www.dyers.org/blog/
*/

function widget_betterblogroll_init() {

	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') ) 
		return;

	function widget_betterblogroll($args) {
	
		// "$args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys." - These are set up by the theme
		extract($args);

		// These are our own options
		$options = get_option('widget_betterblogroll');
		$bbw_title = $options['title'];  // Title in sidebar for widget
		$bbw_number = $options['show'];  // # of Posts we are showing
		$bbw_explanation = $options['explanation']; //a small explanation that the blogroll is rolling
		$bbw_use_cat = $options['use_cat'] ? '1': '0';//should the category be shown in the list?
		$bbw_limit_cat=$options['limit_cat']; //Limit the blogroll to a particular category
		$bbw_limit_cat_not=$options['limit_cat_not'] ? 'NOT': ''; //saying NOT in query
		$bbw_use_images = $options['use_images'] ? '1': '0';//should the link's image be shown?
		$bbw_use_link_name = $options['use_link_name'] ? '1': '0';//should the link name be shown in the list?
		$bbw_use_nofollow = $options['use_nofollow'] ? '1': '0';//If links are not trusted (paid links), they can be set to nofollow.
		$bbw_separate_cats = $options['separate_cats'] ? '1': '0';//If links are not trusted (paid links), they can be set to nofollow.
		if (!$bbw_number || $bbw_number<1) $bbw_number = 10;
		if (!$bbw_title) $bbw_title = 'A Better Blogroll';
		
		$bbw_clean_limit_cat = implode ("','",explode(",",$bbw_limit_cat));

		// Output

		echo $before_widget . $before_title . $bbw_title . $after_title;
		if(!empty($bbw_explanation)){echo '<p><div style="width: 140px;margin-bottom:10px;"><small>'.$bbw_explanation.'</small></div></p>';}			

		global $wpdb;

		if (!$bbw_separate_cats){//if the user wants a single list...
			$querystr = "SELECT DISTINCT link_url, name, link_name, link_target, link_image, link_description, link_rel FROM $wpdb->links INNER JOIN ($wpdb->term_relationships INNER JOIN( $wpdb->terms INNER JOIN $wpdb->term_taxonomy ON $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id) ON $wpdb->term_taxonomy.term_taxonomy_id=$wpdb->term_relationships.term_taxonomy_id)ON $wpdb->links.link_id=$wpdb->term_relationships.object_id WHERE $wpdb->term_taxonomy.taxonomy='link_category' AND $wpdb->links.link_visible = 'Y'";
							
			if ($bbw_limit_cat){//if the user limits the blogroll to specific categories, add it to the query
						$querystr .= " AND $wpdb->terms.name $bbw_limit_cat_not IN ('$bbw_clean_limit_cat')";	
			}
				
			$querystr .= " ORDER BY rand() LIMIT $bbw_number";
			echo bbw_get_data($querystr,$bbw_use_nofollow,$bbw_use_images,$bbw_use_link_name,$bbw_use_cat,$bbw_cat,$bbw_separate_cats);
		
		}else{//if  the user wants multiple lists...
			$bbw_clean_limit_cat_array = explode("','",$bbw_clean_limit_cat);	
			if ($bbw_limit_cat && !$bbw_limit_cat_not){
			}else{
				$bbw_temp_cats = get_categories('type=link');
				$bbw_all_cats = array();
				foreach ($bbw_temp_cats as $bbw_cat){
					array_push($bbw_all_cats, $bbw_cat->cat_name);
				}
				if (!$bbw_limit_cat){
					$bbw_clean_limit_cat_array = $bbw_all_cats;
				}else{
					$bbw_clean_limit_cat_array = array_diff($bbw_all_cats,$bbw_clean_limit_cat_array);
				}
			}		
			foreach ($bbw_clean_limit_cat_array as $bbw_cat){
				$querystr = "SELECT DISTINCT link_url, name, link_name, link_target, link_image, link_description, link_rel FROM $wpdb->links INNER JOIN ($wpdb->term_relationships INNER JOIN( $wpdb->terms INNER JOIN $wpdb->term_taxonomy ON $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id) ON $wpdb->term_taxonomy.term_taxonomy_id=$wpdb->term_relationships.term_taxonomy_id)ON $wpdb->links.link_id=$wpdb->term_relationships.object_id WHERE $wpdb->term_taxonomy.taxonomy='link_category' AND $wpdb->links.link_visible = 'Y' AND $wpdb->terms.name IN ('$bbw_cat') ORDER BY rand() LIMIT $bbw_number";	
			echo bbw_get_data($querystr,$bbw_use_nofollow,$bbw_use_images,$bbw_use_link_name,$bbw_use_cat,$bbw_cat,$bbw_separate_cats);
			}
		}
		echo $after_widget;	
	}

	function bbw_get_data($querystr,$bbw_use_nofollow,$bbw_use_images,$bbw_use_link_name,$bbw_use_cat,$bbw_cat,$bbw_separate_cats){
		global $wpdb;

		$bbw_links = $wpdb->get_results($querystr, OBJECT);
		$bbw_result='';
		if ($bbw_separate_cats){
			$bbw_result.="<h3>$bbw_cat</h3>";
		}
		$bbw_result.= "<ul>";
		if (!empty($bbw_links)) {
			foreach ($bbw_links as $bbwlink) {
				$bbw_link_url = $bbwlink->link_url;
				$bbw_link_cat = $bbwlink->name;
				$bbw_link_name = $bbwlink->link_name;
				$bbw_link_desc = $bbwlink->link_description;
				$bbw_link_image = $bbwlink->link_image;
				$bbw_link_target = $bbwlink->link_target;
				$bbw_link_rel = $bbwlink->link_rel;

				$bbw_result.= '<li><a';
				if ($bbw_use_nofollow){
					$bbw_result.= ' rel="nofollow"';
				}elseif($bbw_link_rel){
					$bbw_result.=' rel="'.$bbw_link_rel.'"';
				}else{
				}
				if ($bbw_link_target){
					$bbw_result.= ' target="'.$bbw_link_target.'"';}
				$bbw_result.= ' href="'.$bbw_link_url.'" title="'.$bbw_link_desc.'">';
				if (($bbw_use_images)&&($bbw_link_image)){
					$bbw_result.= '<img src="'.$bbw_link_image.'" alt="Click to visit '.$bbw_link_name.'" /><br />';}
				if ($bbw_use_link_name){
					$bbw_result.= $bbw_link_name;}
				$bbw_result.= '</a>';
				if ($bbw_use_cat) {$bbw_result.= ' <small>('.$bbw_link_cat.')</small>';}
				$bbw_result.= '</li>';
			}
		}else $bbw_result.= "<li>No Blogroll Links</li>";
		$bbw_result.= '</ul>';
		return ($bbw_result);
	}


		// Settings form
	function widget_betterblogroll_control() {

		// Get options
		$options = get_option('widget_betterblogroll');
		// options exist? if not set defaults
		if ( !is_array($options) )
			$options = array('title'=>'Recent Posts', 'show'=>10,'explanation'=>'This random selection from my daily reads changes each time the page is refreshed.');
		
		// form posted?
		if ( $_POST['betterblogroll-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['betterblogroll-title']));
			$options['show'] = strip_tags(stripslashes($_POST['betterblogroll-show']));
			$options['explanation'] = strip_tags(stripslashes($_POST['betterblogroll-explanation']));
			$options['use_cat'] = isset($_POST['betterblogroll-use_cat']);
			$options['limit_cat'] = strip_tags(stripslashes($_POST['betterblogroll-limit_cat']));
			$options['limit_cat_not'] = isset($_POST['betterblogroll-limit_cat_not']);
			$options['use_images'] = isset($_POST['betterblogroll-use_images']);
			$options['use_link_name'] = isset($_POST['betterblogroll-use_link_name']);
			$options['use_nofollow'] = isset($_POST['betterblogroll-use_nofollow']);
			$options['separate_cats'] = isset($_POST['betterblogroll-separate_cats']);
			update_option('widget_betterblogroll', $options);
		}

		// Get options for form fields to show
		$bbw_title = htmlspecialchars($options['title'], ENT_QUOTES);
		$bbw_number = htmlspecialchars($options['show'], ENT_QUOTES);
		$bbw_explanation = htmlspecialchars($options['explanation'], ENT_QUOTES);
		$bbw_use_cat = $options['use_cat'] ? 'checked="checked"' : '';
		$bbw_limit_cat = htmlspecialchars($options['limit_cat'], ENT_QUOTES);
		$bbw_limit_cat_not = $options['limit_cat_not'] ? 'checked="checked"' : '';
		$bbw_use_images = $options['use_images'] ? 'checked="checked"' : '';
		$bbw_use_link_name = $options['use_link_name'] ? 'checked="checked"' : '';
		$bbw_use_nofollow = $options['use_nofollow'] ? 'checked="checked"' : '';
		$bbw_separate_cats = $options['separate_cats'] ? 'checked="checked"' : '';
		// The form fields
		echo '<p style="text-align:right;">
				<label for="betterblogroll-title">' . __('Title:') . ' 
				<input style="width: 200px;" id="betterblogroll-title" name="betterblogroll-title" type="text" value="'.$bbw_title.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="betterblogroll-explanation">' . __('Explanation:') . ' 
				<input style="width: 200px; id="betterblogroll-explanation" name="betterblogroll-explanation" type="text" value="'.$bbw_explanation.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="betterblogroll-show">' . __('Number of Links to Show:') . ' 
				<input style="width: 25px;" id="betterblogroll-show" name="betterblogroll-show" type="text" value="'.$bbw_number.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="betterblogroll-use_link_name">' . __('Show Text Links?:') . ' 
				<input class="checkbox" type="checkbox" '.$bbw_use_link_name.' id="betterblogroll-use_link_name" name="betterblogroll-use_link_name" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="betterblogroll-use_images">' . __('Show Images?:') . ' 
				<input class="checkbox" type="checkbox" '.$bbw_use_images.' id="betterblogroll-use_images" name="betterblogroll-use_images" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="betterblogroll-use_cat">' . __('Show Link Categories?:') . ' 
				<input class="checkbox" type="checkbox" '.$bbw_use_cat.' id="betterblogroll-use_cat" name="betterblogroll-use_cat" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="betterblogroll-limit_cat">' . __('Show Only Links From These Categories:<br/>(comma separated, blank for all)') . ' 
				<input style="width: 200px; id="betterblogroll-limit_cat" name="betterblogroll-limit_cat" type="text" value="'.$bbw_limit_cat.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="betterblogroll-limit_cat_not">' . __('Check Here To Hide The Above Categories:') . ' 
				<input class="checkbox" type="checkbox" '.$bbw_limit_cat_not.' id="betterblogroll-limit_cat_not" name="betterblogroll-limit_cat_not" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="betterblogroll-use_nofollow">' . __('Set Links To Nofollow?:') . ' 
				<input class="checkbox" type="checkbox" '.$bbw_use_nofollow.' id="betterblogroll-use_nofollow" name="betterblogroll-use_nofollow" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="betterblogroll-separate_cats">' . __('Separate Links By Category?:') . ' 
				<input class="checkbox" type="checkbox" '.$bbw_separate_cats.' id="betterblogroll-separate_cats" name="betterblogroll-separate_cats" />
				</label></p>';
		
		echo '<input type="hidden" id="betterblogroll-submit" name="betterblogroll-submit" value="1" />';
	}
	
	// Register widget for use
	register_sidebar_widget(array('Better Blogroll', 'widgets'), 'widget_betterblogroll');

	// Register settings for use, 300x500 pixel form
	register_widget_control(array('Better Blogroll', 'widgets'), 'widget_betterblogroll_control', 325, 350);
}

// Run code and init
add_action('widgets_init', 'widget_betterblogroll_init');

?>