<?php
// Get options
$options = get_option(WP_BEIFEN_OPTIONS);
// Nonce for AJAX activity
$nonce = wp_create_nonce(WP_BEIFEN_NONCE);
?>
<script  type='text/javascript'>
window.onerror = js_error_handler;

function js_error_handler(message, line, file)
{
	alert(message + line + file);
}

jQuery(document).ready(function(){
	jQuery("div.updated").hide();
	
	jQuery("#wp_beifen_submit").click(function () {
		// Disable form inputs
		jQuery('#wp_beifen_form :input').attr('disabled', true);
		// Change update message
		jQuery("#update_message").html("<p><img src=\"<?php echo admin_url(); ?>images/loading.gif\" /><?php _e("Creating backup, please don't leave this page!", WP_BEIFEN_DOMAIN); ?></p>");
		// show update message
		jQuery("#update_message").show();
		// prepare form data
		var options = 'backup_name=' + jQuery("#backup_name").attr("value")
					+ '&'
					+ 'backup_type=' + jQuery('#backup_type option:selected').val()
					+ '&'
					+ 'backup_timeout=' + jQuery('#backup_timeout option:selected').val()					
					+ '&'
					+ 'compress_backup=' + jQuery('.compress_backup:checked').attr("value");
		// send ajax request to save new settings
		send_request('create_backup', options, display_backup_result);
    });
	
	
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
	
	function display_backup_result(response)
	{
		try
		{
			result = eval("(" + response + ')');
			var message = '<h3>' + result.status + '!</h3>'
						+ '<p>' + result.message + '!</p>';
		}
		catch(e)
		{
			var message = '<h3><?php _e("Error", WP_BEIFEN_DOMAIN); ?>!</h3>'
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
			<h2><?php _e("Create New Backup", WP_BEIFEN_DOMAIN); ?></h2>
			<div id="update_message" class="updated"></div>
			<?php
				if(!$options['plugin_ready'])
				{
					?>
						<p><?php _e("Please review your settings before first use.", WP_BEIFEN_DOMAIN); ?> <?php _e("You can do so", WP_BEIFEN_DOMAIN); ?> <a href="<?php echo admin_url(); ?>admin.php?page=<?php echo basename($options['plugin_location']); ?>/beifen.php"><?php _e("here", WP_BEIFEN_DOMAIN); ?></a>.
					<?php
				}
				else
				{
					?>
						<form id="wp_beifen_form">
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><label for="backup_name"><?php _e("Backup Name", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>
										<input name="backup_name" type="text" id="backup_name" value="<?php echo date('Ymd-Hi'); ?>" class="regular-text" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="backup_type"><?php _e("Backup Type", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>
										<select name="backup_type" id="backup_type">
											<option value="Complete"><?php _e("Complete", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Files"><?php _e("Files Only", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="DB"><?php _e("Database Only", WP_BEIFEN_DOMAIN); ?> </option>
										</select>
									</td>
								</tr>
								<?php if(class_exists(ZipArchive)) : ?>
									<tr valign="top">
										<th scope="row"><label for="compress_backup"><?php _e("Compress files with GZIP", WP_BEIFEN_DOMAIN); ?></label></th>
										<td>
											<input type="radio" class="compress_backup" name="compress_backup" value="No" checked="checked" /><?php _e("No", WP_BEIFEN_DOMAIN); ?><br/>
											<input type="radio" class="compress_backup" name="compress_backup" value="Yes" /><?php _e("Yes", WP_BEIFEN_DOMAIN); ?>
										</td>
									</tr>
								<?php endif; ?>
								<tr valign="top">
									<th scope="row"><label for="backup_timeout"><?php _e("Timeout (seconds)", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>
										<select name="backup_timeout" id="backup_timeout">
											<option value="10"<?php if($options['default_timeout']==10) echo ' selected="selected"'; ?>>10</option>
											<option value="20"<?php if($options['default_timeout']==20) echo ' selected="selected"'; ?>>20</option>
											<option value="30"<?php if($options['default_timeout']==30) echo ' selected="selected"'; ?>>30</option>
											<option value="40"<?php if($options['default_timeout']==40) echo ' selected="selected"'; ?>>40</option>
											<option value="50"<?php if($options['default_timeout']==50) echo ' selected="selected"'; ?>>50</option>
											<option value="60"<?php if($options['default_timeout']==60) echo ' selected="selected"'; ?>>60</option>
											<option value="90"<?php if($options['default_timeout']==90) echo ' selected="selected"'; ?>>90</option>
											<option value="120"<?php if($options['default_timeout']==120) echo ' selected="selected"'; ?>>120</option>
											<option value="180"<?php if($options['default_timeout']==180) echo ' selected="selected"'; ?>>180</option>
											<option value="240"<?php if($options['default_timeout']==240) echo ' selected="selected"'; ?>>240</option>
											<option value="299"<?php if($options['default_timeout']==299) echo ' selected="selected"'; ?>>300</option>
										</select>
									</td>
								</tr>
							</table>
						</form>
						<p class="submit">
							<input type="submit" id="wp_beifen_submit" class="button-primary" value="<?php _e("Create New Backup", WP_BEIFEN_DOMAIN); ?>" />
						</p>
					<?php
				}
			?>
		</div>
		<div class="clear"></div>
	</div>
</div>