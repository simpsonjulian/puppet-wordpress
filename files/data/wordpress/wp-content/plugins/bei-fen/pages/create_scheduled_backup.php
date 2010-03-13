<?php
// Get options
$options = get_option(WP_BEIFEN_OPTIONS);
// Nonce for AJAX activity
$nonce = wp_create_nonce(WP_BEIFEN_NONCE);

// Get current user info
global $current_user;
get_currentuserinfo();

?>
<script  type='text/javascript'>
window.onerror = js_error_handler;

function schedule_check()
{
	if(jQuery('#schedule_type option:selected').val()=='Single')
	{
		jQuery("#schedule_frequence_row").hide();
		jQuery("#schedule_single_row").show();
		jQuery("#replace_old_backup_row").hide();
	}
	else
	{
		jQuery("#replace_old_backup_row").show();
		jQuery("#schedule_frequence_row").show();
		jQuery("#schedule_single_row").hide();
		if(jQuery('#schedule_frequence option:selected').val()=='Daily')
		{
			jQuery("#schedule_frequence_weekly").hide();
			jQuery("#schedule_frequence_monthly").hide();
		}
		else if(jQuery('#schedule_frequence option:selected').val()=='Weekly')
		{
			jQuery("#schedule_frequence_weekly").show();
			jQuery("#schedule_frequence_monthly").hide();
		}
		else
		{
			jQuery("#schedule_frequence_weekly").hide();
			jQuery("#schedule_frequence_monthly").show();
		}
	}
}

function js_error_handler(message, line, file)
{
	alert(message + line + file);
}

jQuery(document).ready(function(){
	jQuery("div.updated").hide();
	jQuery("#schedule_frequence_row").hide();
	jQuery("#schedule_frequence_weekly").hide();
	jQuery("#schedule_frequence_monthly").hide();
	schedule_check();

	jQuery(".send_email_confirmation").change(function() {
		switch (jQuery(".send_email_confirmation:checked").attr("value")) {
			case "Yes":
				jQuery("#email_row").show();
				break;
			case "No":
				jQuery("#email_row").hide();
				break;
		}
    });

	jQuery("#schedule_type").change(function() {
		schedule_check();
    });

	jQuery("#schedule_frequence").change(function() {
		schedule_check();
    });

	jQuery("#schedule_single_date_month").change(function() {

    });

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
					+ 'compress_backup=' + jQuery('.compress_backup:checked').attr("value")
					+ '&'
					+ 'schedule_type=' + jQuery('#schedule_type option:selected').val()
					+ '&'
					+ 'schedule_frequence=' + jQuery('#schedule_frequence option:selected').val()
					+ '&'
					+ 'schedule_frequence_weekly=' + jQuery('#schedule_frequence_weekly option:selected').val()
					+ '&'
					+ 'schedule_frequence_monthly=' + jQuery('#schedule_frequence_monthly option:selected').val()
					+ '&'
					+ 'schedule_single_date_day=' + jQuery('#schedule_single_date_day option:selected').val()
					+ '&'
					+ 'schedule_single_date_month=' + jQuery('#schedule_single_date_month option:selected').val()
					+ '&'
					+ 'schedule_single_date_year=' + jQuery('#schedule_single_date_year option:selected').val()
					+ '&'
					+ 'schedule_time_hour=' + jQuery('#schedule_time_hour option:selected').val()
					+ '&'
					+ 'schedule_time_minute=' + jQuery('#schedule_time_minute option:selected').val()
					+ '&'
					+ 'replace_old_backup=' + jQuery('.replace_old_backup:checked').attr("value")
					+ '&'
					+ 'send_email_confirmation=' + jQuery(".send_email_confirmation:checked").attr("value")
					+ '&'
					+ 'email_confirmation_address=' + jQuery("#email_confirmation_address").attr("value");

		// send ajax request to save new settings
		send_request('schedule_backup', options, display_backup_result);
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
			<h2><?php _e("Schedule New Backup", WP_BEIFEN_DOMAIN); ?></h2>
			<?php
				if($options['backup_schedule']==false)
				{
					?>
						<div class="error"><p>
							<?php echo __("Scheduling is deactivated! Please activate scheduling!", WP_BEIFEN_DOMAIN) . ' '. __("You can do so", WP_BEIFEN_DOMAIN);
						?>
						<a href="admin.php?page=<?php echo basename($options['plugin_location']); ?>/pages/create_scheduled_backup.php"><?php _e("here", WP_BEIFEN_DOMAIN); ?></a>!
						</p></div>
					<?php
				}
			?>
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
								<tr valign="top">
									<th scope="row"><label for="schedule_type"><?php _e("Schedule Type", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>
										<select name="schedule_type" id="schedule_type">
											<option value="Single" selected="selected"><?php _e("Single Backup", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Frequent"><?php _e("Frequent Backup", WP_BEIFEN_DOMAIN); ?> </option>
										</select>
									</td>
								</tr>
								<tr valign="top" id="schedule_frequence_row">
									<th scope="row"><label for="schedule_frequence"><?php _e("Schedule Frequence", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>
										<select name="schedule_frequence" id="schedule_frequence">
											<option value="Daily"><?php _e("Daily", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Weekly"><?php _e("Weekly", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Monthly"><?php _e("Monthly", WP_BEIFEN_DOMAIN); ?> </option>
										</select>
										<select name="schedule_frequence_weekly" id="schedule_frequence_weekly">
											<option value="Monday"><?php _e("Monday", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Tuesday"><?php _e("Tuesday", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Wednesday"><?php _e("Wednesday", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Thursday"><?php _e("Thursday", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Friday"><?php _e("Friday", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Saturday"><?php _e("Saturday", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="Sunday"><?php _e("Sunday", WP_BEIFEN_DOMAIN); ?> </option>
										</select>
										<select name="schedule_frequence_monthly" id="schedule_frequence_monthly">
											<option value="first"><?php _e("First day of month", WP_BEIFEN_DOMAIN); ?> </option>
											<option value="last"><?php _e("Last day of month", WP_BEIFEN_DOMAIN); ?> </option>
										</select>
									</td>
								</tr>
								<tr valign="top" id="schedule_single_row">
									<th scope="row"><?php _e("Create Date", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>
										<select name="schedule_single_date_day" id="schedule_single_date_day">
											<?php
												for($x=1; $x<=date('t'); $x++)
												{
													if($x==date('j'))
														$selected = ' selected="selected"';
													else
														 $selected = '';
													if($x<10)
														echo '<option value="' . $x . '"' . $selected .'>0' . $x . '</option>';
													else
														echo '<option value="' . $x . '"' . $selected .'>' . $x . '</option>';
												}
											?>
										</select>
										<select name="schedule_single_date_month" id="schedule_single_date_month">
											<option value="1"<?php if(date('m')==1) echo ' selected="selected"'; ?>><?php _e("January", WP_BEIFEN_DOMAIN); ?></option>
											<option value="2"<?php if(date('m')==2) echo ' selected="selected"'; ?>><?php _e("February", WP_BEIFEN_DOMAIN); ?></option>
											<option value="3"<?php if(date('m')==3) echo ' selected="selected"'; ?>><?php _e("March", WP_BEIFEN_DOMAIN); ?></option>
											<option value="4"<?php if(date('m')==4) echo ' selected="selected"'; ?>><?php _e("April", WP_BEIFEN_DOMAIN); ?></option>
											<option value="5"<?php if(date('m')==5) echo ' selected="selected"'; ?>><?php _e("May", WP_BEIFEN_DOMAIN); ?></option>
											<option value="6"<?php if(date('m')==6) echo ' selected="selected"'; ?>><?php _e("June", WP_BEIFEN_DOMAIN); ?></option>
											<option value="7"<?php if(date('m')==7) echo ' selected="selected"'; ?>><?php _e("July", WP_BEIFEN_DOMAIN); ?></option>
											<option value="8"<?php if(date('m')==8) echo ' selected="selected"'; ?>><?php _e("August", WP_BEIFEN_DOMAIN); ?></option>
											<option value="9"<?php if(date('m')==9) echo ' selected="selected"'; ?>><?php _e("September", WP_BEIFEN_DOMAIN); ?></option>
											<option value="10"<?php if(date('m')==10) echo ' selected="selected"'; ?>><?php _e("October", WP_BEIFEN_DOMAIN); ?></option>
											<option value="11"<?php if(date('m')==11) echo ' selected="selected"'; ?>><?php _e("November", WP_BEIFEN_DOMAIN); ?></option>
											<option value="12"<?php if(date('m')==12) echo ' selected="selected"'; ?>><?php _e("December", WP_BEIFEN_DOMAIN); ?></option>
										</select>
										<select name="schedule_single_date_year" id="schedule_single_date_year">
											<?php
												for($x=date('Y'); $x<(date('Y')+5); $x++)
													echo '<option value="' . $x . '">' . $x . '</option>';
											?>
										</select>
									</td>
								</tr>
								<tr valign="top" id="schedule_time_row">
									<th scope="row"><label for="schedule_time_hour"><?php _e("Schedule Time", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>
										<select name="schedule_time_hour" id="schedule_time_hour">
											<?php
												for($x = 0; $x < 24; $x++)
												{
													if($x<10)
														echo '<option value="' . $x . '">0' . $x . '</option>';
													else
														echo '<option value="' . $x . '">' . $x . '</option>';
												}
											?>
										</select>:
										<select name="schedule_time_minute" id="schedule_time_minute">
											<?php
												for($x = 0; $x < 60; $x++)
												{
													if($x<10)
														echo '<option value="' . $x . '">0' . $x . '</option>';
													else
														echo '<option value="' . $x . '">' . $x . '</option>';
												}
											?>
										</select>
									</td>
								</tr>
								<tr valign="top" id="replace_old_backup_row">
									<th scope="row"><label for="replace_old_backup"><?php _e("Delete old backup", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>
										<input type="radio" class="replace_old_backup" name="replace_old_backup" value="No" checked="checked" /><?php _e("No", WP_BEIFEN_DOMAIN); ?><br/>
										<input type="radio" class="replace_old_backup" name="replace_old_backup" value="Yes" /><?php _e("Yes", WP_BEIFEN_DOMAIN); ?>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="send_email_confirmation"><?php _e("Send confirmation/failure message of backup via email", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>

										<input type="radio" class="send_email_confirmation" name="send_email_confirmation" value="Yes" checked="checked" /><?php _e("Yes", WP_BEIFEN_DOMAIN); ?><br/>
										<input type="radio" class="send_email_confirmation" name="send_email_confirmation" value="No" /><?php _e("No", WP_BEIFEN_DOMAIN); ?>
									</td>
								</tr>
								<tr valign="top" id="email_row">
									<th scope="row"><label for="email_confirmation_address"><?php _e("Send message to the following email address", WP_BEIFEN_DOMAIN); ?></label></th>
									<td>
										<input name="email_confirmation_address" type="text" id="email_confirmation_address" value="<?php echo $current_user->user_email; ?>" class="regular-text" />
									</td>
								</tr>
							</table>
						</form>
						<p class="submit">
							<input type="submit" id="wp_beifen_submit" class="button-primary" value="<?php _e("Schedule New Backup", WP_BEIFEN_DOMAIN); ?>" />
						</p>
					<?php
				}
			?>
		</div>
		<div class="clear"></div>
	</div>
</div>