<?php
/*
Plugin Name: wpLicense-reloaded
Plugin URI: http://estebanglas.com/2009/07/creative-commons-plugin/
Description: Based on <a href="http://wiki.creativecommons.org/WpLicense">wpLicense</a> Allows selection of a <a href="http://creativecommons.org">Creative Commons</a> license for blog content on a per-post basis. Work done originally for <a href="http://www.socialbrite.org">SocialBrite</a>
Version: 0.1.1
Author: Esteban Panzeri Glas (based on the work by Nathan R. Yergler>)
Author URI: http://estebanglas.com
*/

/*  Copyright 2009,
    Esteban A. Panzeri Glas (email : esteban.glas@gmail.com)
    Creative Commons (email : software@creativecommons.org), 
    Nathan R. Yergler (email : nathan@creativecommons.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/* Template Functions */

function licenseHtml($content) {

   global $post;
   $license_uri = get_post_meta($post->ID, 'cc_content_license_uri',true);
   $license_img = get_post_meta($post->ID, 'cc_content_license_img',true);
   $license_name = get_post_meta($post->ID, 'cc_content_license',true);
   $CC_logo = get_bloginfo("wpurl") . "/wp-content/plugins/wp-license-reloaded/images/cclogo.gif";
 
 if (($license_uri) && $license_uri != ""){
		$image = <<< END_OF_STMT
<img src="${license_img}" alt="${license_name}" class="alignleft" style="margin-top:4px;" />

END_OF_STMT;
 

	
  $result = <<< END_OF_STMT
  
<div class="wp_license">
<p><a rel="license" href="${license_uri}">$image</a>This work ${attrib} is licensed under a <a rel="license" href="${license_uri}">${license_name}</a>.</p>
</div>
END_OF_STMT;

$content = $content . $result;

}

return $content;

} // licenseHtml


/* Admin functions */

function license_options() {
   
   global $post;
   

$slart = $_GET['post'];
   
$defaults = array("license_name" => get_post_meta($slart, 'cc_content_license',true),
	     	  "license_uri"  => get_post_meta($slart, 'cc_content_license_uri',true),
	    );
$wp_url = get_bloginfo('wpurl');
$license_url = get_post_meta($slart, 'cc_content_license_uri',true);

if ($license_url == '') { $jswidget_extra = "want_a_license=no_license_by_default"; }
else { $jswidget_extra = ""; }



echo <<< END_OF_ADMIN
<div class="wrap">	
<p>This page allows you to choose a 
<a href="http://creativecommons.org">Creative Commons</a> license 
for your content. </p>



<script type="text/javascript" src="http://api.creativecommons.org/jswidget/tags/0.92/complete.js?${jswidget_extra}">
</script>

<input id="cc_js_seed_uri" type="hidden" value="${license_url}" />
<input name="blog_url" id="blog_url" type="hidden" value="${wp_url}" />
<input name="remove_license" type="hidden" value="false" />
<input name="submitted" type="hidden" value="wplicense" />


<br/>


</div>
</div>

END_OF_ADMIN;

} // license_options



// Include the necessary java-script libraries 	 
function wplicense_header() {
   $css_url = get_bloginfo("wpurl") . "/wp-content/plugins/wp-license-reloaded/wplicense.css"; 	 
	  	
   echo "<link rel=\"stylesheet\" href=\"${css_url}\" />";
   add_meta_box('cc_license_control', 'Creative Commons Licensing', 'license_options', 'post', 'normal', 'high');
} // wplicense_header


// Initialize the WordPress content variables
function init_content_license($fullreset=false, $just_license_reset=false) {

global $post;



  // if reset is True, destructively reset the values
  if ($fullreset == true) {
     update_post_meta($_POST['post_ID'],'cc_content_license', '');
     update_post_meta($_POST['post_ID'],'cc_content_license_uri', '');
     update_post_meta($_POST['post_ID'],'cc_content_license_img', '');

  } // if resetting

  if ($just_license_reset) {
    update_post_meta($_POST['post_ID'],'cc_content_license', '');
    update_post_meta($_POST['post_ID'],'cc_content_license_uri', '');
    update_post_meta($_POST['post_ID'],'cc_content_license_img', '');
  } // if just resetting license details but not other prefs
  
  
} // init_content_license

function post_form() {
    global $post_msg;
	global $post;

	
			//update_post_meta($slart,'cc_content_license_img', $_POST['cc_js_result_img']);
			
	// check for standard return (using web services
    if ( (isset($_POST['submitted'])) && ($_POST['submitted'] == 'wplicense')) {
        // check if the license should be removed
        if ($_POST['remove_license'] == '__remove' || 
           ($_POST['cc_js_result_uri'] == '' && get_post_meta($_POST['post_ID'],'cc_content_license_uri') != '')) {
           init_content_license(false, true);

           $post_msg = "<h3>License information removed.</h3>";
	   return;
        } // remove license
	
  	// check if the license was changed
	if ($_POST['cc_js_result_uri'] != get_post_meta($_POST['post_ID'],'cc_content_license_uri')) {
           // store the new license information
		   
           update_post_meta($_POST['post_ID'],'cc_content_license', $_POST['cc_js_result_name']);
           update_post_meta($_POST['post_ID'],'cc_content_license_uri', $_POST['cc_js_result_uri']);
           update_post_meta($_POST['post_ID'],'cc_content_license_img', $_POST['cc_js_result_img']);
        }

        // store the settings
		
        $post_msg = "<h3>License information updated.</h3>";
    } // standard web services post 			
			
} // post_form

/* admin interface action registration */


add_action('admin_head', 'wplicense_header');

/* content action/filter registration */


add_action('save_post', 'post_form');
add_action('edit_post', 'post_form');
add_action('publish_post', 'post_form');
add_action('admin_head', 'post_form');
add_filter('the_content','licenseHtml');

?>
