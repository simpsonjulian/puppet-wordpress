<?php
// Get options
$options = get_option(WP_BEIFEN_OPTIONS);
// Nonce for AJAX activity
$nonce = wp_create_nonce(WP_BEIFEN_NONCE);
?>
<script  type='text/javascript'>
/* JQuery stuff for AJAX request and form modification */
jQuery(document).ready(function(){

	// hide update message and directory list
	jQuery("#update_message").hide();
	jQuery("#directory_list_row").hide();
	
	
	// add click event to submit button
	jQuery("#wp_beifen_submit").click(function () {
		// hide directory list
		jQuery("#directory_list_row").hide();
		// prepare form data
		var options = 'backup_directory=' + jQuery("#backup_directory").attr("value")
					+ '&'
					+ 'backup_schedule=' + jQuery('.backup_schedule:checked').attr("value")
					+ '&'
					+ 'include_backup_directory=' + jQuery('.include_backup_directory:checked').attr("value")
					+ '&'
					+ 'default_timeout=' + jQuery('#default_timeout option:selected').val()
					+ '&'
					+ 'enable_debugging=' + jQuery('.enable_debugging:checked').attr("value");
		// Disable form inputs
		jQuery('#wp_beifen_form :input').attr('disabled', true);
		// Change update message
		jQuery("#update_message").html("<p><img src=\"<?php echo admin_url(); ?>images/loading.gif\" /><?php _e("Saving settings, please don't leave this page!", WP_BEIFEN_DOMAIN); ?></p>");
		// show update message
		jQuery("#update_message").show();
		// send ajax request to save new settings
		send_request('change_settings', options, display_update_result);
    });
	
	// On backup_directory look for existing directories
	jQuery("#backup_directory").keyup(function (e) {
		jQuery("#update_message").hide();
		get_directories();
    });

	// get directories and display them
	function get_directories()
	{
		//prepare form data
		var options = 'backup_directory=' + jQuery("#backup_directory").attr("value");
		send_request('get_directories', options, display_directories);
	}
	
	// send AJAX request
	function send_request(requested_task, options, callback)
	{
		jQuery.ajax({
			type: "post",url: "admin-ajax.php",data: { action: 'wp_beifen_ajax_handler', task:requested_task, parameters:options, _ajax_nonce: '<?php echo $nonce; ?>' },
			beforeSend: function() {},
			complete: function() {},
			success: function(html){
				callback(html);
			}
		})
	}
	
	// display directory list
	function display_directories(response)
	{
		try
		{
			directories = eval("(" + response + ')');
			if(directories.status=='<?php _e("Success", WP_BEIFEN_DOMAIN); ?>') // The path does exist
			{
				// Clear directory list
				jQuery("#directory_list").empty();		
				// Add close button
				jQuery("#directory_list").append('<li id="directory_list_close"><strong><?php _e("Close", WP_BEIFEN_DOMAIN); ?> (X)</strong></li>');
				// Close directory list on clicking directory_list_close
				jQuery("#directory_list_close").click(function () {
					jQuery("#directory_list_row").hide();
				});
				
				if(directories.directories.length>0) // current directory has sub directories?
				{
					for(var i = 0; i < directories.directories.length; i++)
					{
						// Display all subdirectories in the directory list
						jQuery("#directory_list").append('<li class ="directory_list_entry" id="directory_' + i + '">' + directories.directories[i] + '</li>');
					}
					// Add click event to directory list entries
					jQuery("li.directory_list_entry").click(function () {
						jQuery("#backup_directory").attr("value", jQuery(this).html());
						jQuery("#directory_list_row").hide();
						get_directories();
					});
				}
				else // no sub directories
				{
						jQuery("#directory_list").append('<li class ="directory_list_entry" id="directory_empty"><?php _e("Directory is empty!", WP_BEIFEN_DOMAIN); ?></li>');
				}
				
				// Show directory list
				jQuery("#directory_list_row").show();
				// Get sub-directories of new directory
			}
			else // The path does not exist
			{
				jQuery("#directory_list_row").hide();
			}
		}
		catch(e)
		{
			var message = '<h3><?php _e("Error", WP_BEIFEN_DOMAIN); ?>!</h3>'
						+ '<p><?php _e("The server response was invalid!", WP_BEIFEN_DOMAIN); ?></p>';
			<?php if($options['enable_debugging']) : ?>
				message += '<p><?php _e("Server Response", WP_BEIFEN_DOMAIN); ?>:<pre>' + response + '</pre></p>';
			<?php endif; ?>
			// Change update message
			jQuery("#update_message").html(message);
			// show update message
			jQuery("#update_message").show();
		}
		
	}
	
	// display update list
	function display_update_result(response)
	{
		var message;
		try
		{
			var result = eval("(" + response + ')');
			message = '<h3>' + result.status + '!</h3>'
						+ '<p>' + result.message + '!</p>';
			// Change update message
			jQuery("#update_message").html(message);
			// Enable form inputs
			jQuery('#wp_beifen_form :input').attr('disabled', false);
		}
		catch(e)
		{
			alert(e);
			message = '<h3><?php _e("Error", WP_BEIFEN_DOMAIN); ?>!</h3>'
						+ '<p><?php _e("The server response was invalid!", WP_BEIFEN_DOMAIN); ?></p>';
			<?php if($options['enable_debugging']) : ?>
				message += '<p><?php _e("Server Response", WP_BEIFEN_DOMAIN); ?>:<pre>' + response + '</pre></p>';
			<?php endif; ?>
		}
		finally
		{
			// Change update message
			jQuery("#update_message").html(message);
			// Enable form inputs
			jQuery('#wp_beifen_form :input').attr('disabled', false);
		}
	}
})

</script>
<div class="wrap">
	<div id="beifen_wrap">
		<div id="beifen_sidebar">
			<?php require_once("bf-info.php"); ?>
		</div>
		<div id="beifen_content">
			<h2><?php _e("Settings", WP_BEIFEN_DOMAIN); ?></h2>
			<div id="update_message" class="updated"></div>
			<form id="wp_beifen_form">
				<table class="form-table">
					<tbody>
						<tr valign="top" id="backup_directory_row" style="position:relative;">
							<th scope="row"><label for="backup_directory"><?php _e("Backup directory", WP_BEIFEN_DOMAIN); ?></label></th>
							<td>
								<input name="backup_directory" id="backup_directory" value="<?php echo wp_beifen_clean_path($options['plugin_backup_directory']); ?>" class="regular-text" type="text">
							</td>
						</tr>
						<tr valign="top" id="directory_list_row">
							<th scope="row"><?php _e("Directory List", WP_BEIFEN_DOMAIN); ?></th>
							<td id="directory_list_cell">
								<div>
									<ul id="directory_list">
									</ul>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="include_backup_directory"><?php _e("Include backup directory, when creating new backups", WP_BEIFEN_DOMAIN); ?></label></th>
							<td>
								<input type="radio" class="include_backup_directory" name="include_backup_directory" value="no" 
								<?php if(!$options['include_backup_directory']) echo 'checked="checked"'; ?> /><?php _e("No", WP_BEIFEN_DOMAIN); ?><br/>
								<input type="radio" class="include_backup_directory" name="include_backup_directory" value="yes" 
								<?php if($options['include_backup_directory']) echo 'checked="checked"'; ?> /><?php _e("Yes", WP_BEIFEN_DOMAIN); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="backup_schedule"><?php _e("Activate backup schedule", WP_BEIFEN_DOMAIN); ?></label></th>
							<td>
								<input type="radio" class="backup_schedule" name="backup_schedule" value="no" 
								<?php if(!$options['backup_schedule']) echo 'checked="checked"'; ?> /><?php _e("No", WP_BEIFEN_DOMAIN); ?><br/>
								<input type="radio" class="backup_schedule" name="backup_schedule" value="yes" 
								<?php if($options['backup_schedule']) echo 'checked="checked"'; ?> /><?php _e("Yes", WP_BEIFEN_DOMAIN); ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="default_timeout"><?php _e("Default timeout (seconds)", WP_BEIFEN_DOMAIN); ?></label></th>
							<td>
								<select name="default_timeout" id="default_timeout">
									<option value="10"<?php if($options['default_timeout']==10) echo ' selected="selected"'; ?>>10 </option>
									<option value="20"<?php if($options['default_timeout']==20) echo ' selected="selected"'; ?>>20 </option>
									<option value="30"<?php if($options['default_timeout']==30) echo ' selected="selected"'; ?>>30 </option>
									<option value="40"<?php if($options['default_timeout']==40) echo ' selected="selected"'; ?>>40 </option>
									<option value="50"<?php if($options['default_timeout']==50) echo ' selected="selected"'; ?>>50 </option>
									<option value="60"<?php if($options['default_timeout']==60) echo ' selected="selected"'; ?>>60 </option>
									<option value="90"<?php if($options['default_timeout']==90) echo ' selected="selected"'; ?>>90 </option>
									<option value="120"<?php if($options['default_timeout']==120) echo ' selected="selected"'; ?>>120 </option>
									<option value="180"<?php if($options['default_timeout']==180) echo ' selected="selected"'; ?>>180 </option>
									<option value="240"<?php if($options['default_timeout']==240) echo ' selected="selected"'; ?>>240 </option>
									<option value="299"<?php if($options['default_timeout']==299) echo ' selected="selected"'; ?>>300 </option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="enable_debugging"><?php _e("Enable debugging", WP_BEIFEN_DOMAIN); ?></label></th>
							<td>
								<input type="radio" class="enable_debugging" name="enable_debugging" value="no" 
								<?php if(!$options['enable_debugging']) echo 'checked="checked"'; ?> /><?php _e("No", WP_BEIFEN_DOMAIN); ?><br/>
								<input type="radio" class="enable_debugging" name="enable_debugging" value="yes" 
								<?php if($options['enable_debugging']) echo 'checked="checked"'; ?> /><?php _e("Yes", WP_BEIFEN_DOMAIN); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			<p class="submit">
				<input name="Submit" id="wp_beifen_submit" class="button-primary" value="<?php _e("Save Changes", WP_BEIFEN_DOMAIN); ?>" type="submit">
			</p>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div style="clear:both;"></div>