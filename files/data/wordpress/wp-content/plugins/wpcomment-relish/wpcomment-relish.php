<?php
/*
Plugin Name: Comment Relish
Plugin URI: http://www.justinshattuck.com/comment-relish/
Description: Increases your readership and RSS subscription rate by simply sending a short 'Thank You' message to users when they first comment on your site. Originally by <a href="http://justinshattuck.com">Justin Shattuck</a> Redux'd by <a href="http://bavotasan.com">c.bavota</a>.
Author: Justin Shattuck & C. Bavota
Version: 2.0
Author URI: http://www.justinshattuck.com/
*/


//Hook the admin_menu action to call the cr_admin_option function
add_action('admin_menu', 'cr_admin_option');

//Adds the Comment Relish option to the administration menu
function cr_admin_option() {
	$plugin_page = add_options_page('Comment Relish', 'Comment Relish', 10, __FILE__, 'cr_admin_panel');
	add_action( 'admin_head-'. $plugin_page, 'cr_add_script' );
}

// Add Javscript file to plugin page
function cr_add_script() {
	echo '<script type="text/javascript" src="'  . get_option('siteurl') . '/wp-content/plugins/wpcomment-relish/js/cr.js"></script>'."\n";
}

//Add a settings link on the plugins admin page
function cr_add_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=wpcomment-relish.php">Settings</a>'; 
  array_unshift( $links, $settings_link ); 
  return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'cr_add_settings_link' );

//Builds the administration panel to configure the plugin
function cr_admin_panel() {
	// Update the options
    if ($_POST['stage'] == 'process') {
        update_option('cr_enabled',$_POST['cr_enabled']);
        update_option('cr_relish_from_name',stripslashes($_POST['cr_relish_from_name']));
        update_option('cr_relish_from_email',$_POST['cr_relish_from_email']);
        update_option('cr_relish_subject',stripslashes($_POST['cr_relish_subject']));
        update_option('cr_relish_message',stripslashes($_POST['cr_relish_message']));
		?> <div class="updated"><p>Preferences saved!</p></div> <?php
    }
    if ($_POST['stage'] != 'process' && $_REQUEST['test']) {
		cr_test_email();	
	?>
    <?php }
    ?>

    <div class="wrap">
        <h2 id="write-post">Comment Relish</h2>
        <form method="post" name="cr" id="cr">
            <input type="hidden" name="stage" value="process" />
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row" style="width: 200px;">
                            <label for="cr_enabled">Enable Comment Relish:</label>
                        </th>
                        <td>
                            <input type="radio" name="cr_enabled" value="yes" <?php if (get_option("cr_enabled") == "yes") echo "checked"; ?> />&nbsp;Yes&nbsp;&nbsp;
                            <input type="radio" name="cr_enabled" value="no" <?php if (get_option("cr_enabled") == "no") echo "checked"; ?> />&nbsp;No
                        </td>                       
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="width: 200px;">
                            <label for="cr_relish_from_name">From:</label>
                        </th>
                        <td>
                        	<input type="text" name="cr_relish_from_name" id="cr_relish_from_name" value="<?=get_option("cr_relish_from_name");?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="width: 200px;">
                            <label for="cr_relish_from_email">Email address:</label>
                        </th>
                        <td>
                            <input type="text" name="cr_relish_from_email" id="cr_relish_from_email" value="<?=get_option("cr_relish_from_email");?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="width: 200px;">
                            <label for="cr_relish_subject">Subject:</label>
                        </th>
                        <td>
                            <input type="text" name="cr_relish_subject" id="cr_relish_subject" value="<?=get_option("cr_relish_subject");?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="width: 200px;">
                            <label for="cr_relish_message">Message:</label>
                        </th>
                        <td>
                        	<input type="button" onclick="addTags('%AUTHOR%')" class="button" value="Author's Name">
                           	<input type="button" onclick="addTags('%AUTHOR_URL%')" class="button" value="Author's Website">
                           	<input type="button" onclick="addTags('%AUTHOR_EMAIL%')" class="button" value="Author's Email">
                           	<input type="button" onclick="addTags('%COMMENT%')" class="button" value="Author's Comment">
                           	<input type="button" onclick="addTags('%COMMENT_ID%')" class="button" value="Comment ID">
                           	<input type="button" onclick="addTags('%ARTICLE%')" class="button" value="Post URL"><br />
                           	<input type="button" onclick="addTags('%ARTICLENAME%')" class="button" value="Post Name">
                           	<input type="button" onclick="addTags('%FEED_RSS2.0%')" class="button" value="RSS Feed">
                           	<input type="button" onclick="addTags('%DATE_SHORT%')" class="button" value="Short Date">
                           	<input type="button" onclick="addTags('%DATE_LONG%')" class="button" value="Long Date">
                           	<input type="button" onclick="addTags('%DATETIME_SHORT%')" class="button" value="Short Date & Time">
                           	<input type="button" onclick="addTags('%DATETIME_LONG%')" class="button" value="Long Date & Time">
                            <br />
                            <textarea name="cr_relish_message" id="cr_relish_message" rows="10" cols="75"><?=get_option("cr_relish_message");?></textarea>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="width: 200px;">
                        </th>
                        <td>
                        	<p><a href="options-general.php?page=wpcomment-relish/wpcomment-relish.php&test=true">Send a Test Email</a></p>
                        </td>
                    </tr>
                </table>
           <p class="submit"><input type="submit" name="submit" class="button-primary" value="Save Changes" /></p>
        </form> 
    <small>Comment Relish originally created by <a href="http://www.justinshattuck.com/comment-relish/">Justin Shattuck</a>. Comment Relish modifications and redux by <a href="http://bavotasan.com">c.bavota</a>.</small>
    </div>
<?php           
}

// Hook the Deactivate option to disable the plugin
register_deactivation_hook(__FILE__,'cr_uninstall');

// Runs uninstallation
function cr_uninstall() {
	// Disable the plugin to prevent instant emailing on reactivation
	update_option('cr_enabled','no');
}

// Only run if enabled
if (get_option("cr_enabled") == "yes") {
	//Because some of our functions are dependent on later loaded modules, hook init and run later
	add_action('comment_post','cr_send_emails',10,2);
	add_action('wp_set_comment_status','cr_send_emails',10,2);

	// Find and email new commentors
	function cr_send_emails($comment_id,$comment_status) {
		global $wpdb;
		
		if($comment_status!=1 && $comment_status!="approve")return ;

		// Find new commentors
		$author = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_approved = '1' and comment_ID=$comment_id limit 1");
		$comments = $wpdb->get_results("SELECT comment_author_email email FROM $wpdb->comments");

		$newAuthor = $author[0]->comment_author_email;
		
		foreach ($comments as $email) {
			if($email->email == $newAuthor) {
				$x++;
				if($x==2) { $newCommentAuthor = false; break; }
			}
		}   
		if($x==1) { $newCommentAuthor = true; }
		if($newCommentAuthor == true) {
			// Fetch the Comment Relish options
			$l_RelishSubject = get_option("cr_relish_subject");
			$l_RelishMessage = get_option("cr_relish_message");
			$l_RelishFromName = get_option("cr_relish_from_name");
			$l_RelishFromEmail = get_option("cr_relish_from_email");
			$l_Headers = "MIME-Version: 1.0\n" .
			  "From: " . __($l_RelishFromName) . " <" . $l_RelishFromEmail . ">\n";
	
			// Send the email
			$finalMessage = mb_convert_encoding(cr_format_email($l_RelishMessage, $author), 'UTF-8', 'HTML-ENTITIES');
			$finalSubject = mb_convert_encoding(cr_format_email($l_RelishSubject, $author), 'UTF-8', 'HTML-ENTITIES');
			mail($newAuthor, $finalSubject, $finalMessage, $l_Headers);
		}
	}

	// Swaps the keys with the values in the email message
	function cr_format_email($p_Message, $author) {
		// Figure out the feed URL
		if ( '' != get_option('permalink_structure') )
			$url = get_option('home') . '/feed/';
		else
			$url = get_option('home') . "/$commentsrssfilename?feed=rss2";

		// Build a list of values to change
		$l_SwapValues = array( "%AUTHOR%"			=> $author[0]->comment_author,
							   "%AUTHOR_URL%"		=> $author[0]->comment_author_url,
							   "%AUTHOR_EMAIL%"		=> $author[0]->comment_author_email,
							   "%COMMENT%"			=> $author[0]->comment_content,
							   "%ARTICLE%"			=> get_permalink($author[0]->comment_post_ID),
							   "%ARTICLENAME%"		=> get_the_title($author[0]->comment_post_ID),
							   "%COMMENT_ID%"		=> $author[0]->comment_ID,
							   "%FEED_RSS2.0%"		=> $url,
							   "%DATE_SHORT%"		=> date("n/j/Y"),
							   "%DATE_LONG%"		=> date("F jS, Y"),
							   "%DATETIME_SHORT%"	=> date("n/j/Y h:i:sA"),
							   "%DATETIME_LONG%"	=> date("F jS, Y h:i:sA"));

		// Swap out old with new
		$p_Message = str_replace(array_keys($l_SwapValues), array_values($l_SwapValues), $p_Message);

		return $p_Message;
	}
}

// Send a test email
function cr_test_email() {
	$message = get_option('cr_relish_message');					   
	$email = get_option('cr_relish_from_email');
	$from = get_option('cr_relish_from_name');
	$subject = get_option('cr_relish_subject');

	if($message == "" || $email == "" || $from == "" || $subject == "") {
		echo '<div class="updated"><p>Please fill in all of the fields below and click "Save Options" before you try to send a test email.</p></div>';
	} else {
		$site = get_option('siteurl');
		$feed = $site.'/?feed=rss2';
		
		$l_SwapValues = array( "%AUTHOR%"			=> 'Comment Author',
							   "%AUTHOR_URL%"		=> 'http://their-website.com',
							   "%AUTHOR_EMAIL%"		=> 'commentauthor@theirwebsite.com',
							   "%COMMENT%"			=> 'This is what they said.',
							   "%ARTICLE%"			=> $site.'/the-name-of-your-post',
							   "%ARTICLENAME%"		=> 'The Name of Your Article',
							   "%COMMENT_ID%"		=> 'Comment ID',
							   "%FEED_RSS2.0%"		=> $feed,
							   "%DATE_SHORT%"		=> date("n/j/Y"),
							   "%DATE_LONG%"		=> date("F jS, Y"),
							   "%DATETIME_SHORT%"	=> date("n/j/Y h:i:sA"),
							   "%DATETIME_LONG%"	=> date("F jS, Y h:i:sA")
							 );	
		
		$message = str_replace(array_keys($l_SwapValues), array_values($l_SwapValues), $message);
		
		mail($email,get_option('cr_relish_subject'), $message, "From: $from <$email>");
		echo '<div class="updated"><p>Test email sent!</p></div>';
	}
}
?>