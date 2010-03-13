<?php
function format_schedule_frequence($backup_details)
{
	switch($backup_details['schedule_frequence'])
	{
		case "Daily":
			$schedule_frequence = __("Daily", WP_BEIFEN_DOMAIN)
								. ' ('
								.  str_pad($backup_details['schedule_time_hour'], 2 ,'0', STR_PAD_LEFT)
								.  ':'
								.  str_pad($backup_details['schedule_time_minute'], 2 ,'0', STR_PAD_LEFT)
								. ')';
			break;
		case "Weekly":
			$schedule_frequence = __("Weekly", WP_BEIFEN_DOMAIN)
								. ', '
								. __("every", WP_BEIFEN_DOMAIN)
								. ' ';
			switch($backup_details['schedule_frequence_weekly'])
			{
				case "Monday":
					$schedule_frequence .= __("Monday", WP_BEIFEN_DOMAIN);
					break;
				case "Tuesday":
					$schedule_frequence .= __("Tuesday", WP_BEIFEN_DOMAIN);
					break;
				case "Wednesday":
					$schedule_frequence .= __("Wednesday", WP_BEIFEN_DOMAIN);
					break;
				case "Thursday":
					$schedule_frequence .= __("Thursday", WP_BEIFEN_DOMAIN);
					break;
				case "Friday":
					$schedule_frequence .= __("Friday", WP_BEIFEN_DOMAIN);
					break;
				case "Saturday":
					$schedule_frequence .= __("Saturday", WP_BEIFEN_DOMAIN);
					break;
				case "Sunday":
					$schedule_frequence .= __("Sunday", WP_BEIFEN_DOMAIN);
					break;
			}
			$schedule_frequence .= '('
								.  str_pad($backup_details['schedule_time_hour'], 2 ,'0', STR_PAD_LEFT)
								.  ':'
								.  str_pad($backup_details['schedule_time_minute'], 2 ,'0', STR_PAD_LEFT)
								. ')';
			break;
		case "Monthly":
			$schedule_frequence = __("Monthly", WP_BEIFEN_DOMAIN)
								. ', ';

			if($backup_details['schedule_frequence_monthly']=='first')
			{
				$schedule_frequence .= __("first day of month", WP_BEIFEN_DOMAIN);
			}
			else
			{
				$schedule_frequence .= __("last day of month", WP_BEIFEN_DOMAIN);
			}

			$schedule_frequence .= ' ('
								.  str_pad($backup_details['schedule_time_hour'], 2 ,'0', STR_PAD_LEFT)
								.  ':'
								.  str_pad($backup_details['schedule_time_minute'], 2 ,'0', STR_PAD_LEFT)
								. ')';
			break;
		default:
			$schedule_frequence = "0";
			break;
	}
	return $schedule_frequence;
}


// Get options
$options = get_option(WP_BEIFEN_OPTIONS);
$date_options = get_option('date_format');
$time_options = get_option('time_format');
// Nonce for AJAX activity
$nonce = wp_create_nonce(WP_BEIFEN_NONCE);
?>
<script  type='text/javascript'>
/* JQuery stuff for AJAX request and form modification */
jQuery(document).ready(function(){

	// hide update message and directory list
	jQuery("#update_message").hide();

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

	// Add delete event to all backup_delete buttons
	jQuery("input.backup_delete").click(function () {
		var options = "id=" + jQuery(this).attr('id');
		jQuery("div.updated").html("<p><img src=\"<?php echo admin_url(); ?>images/loading.gif\" /><?php _e("Deleting backup", WP_BEIFEN_DOMAIN); ?></p>");
		jQuery("div.updated").show();
		send_request('delete_backup',options,delete_backup_entry);
	});

	// Add restore event to all backup_restore buttons
	jQuery("input.backup_restore").click(function () {
		var options = "id=" + jQuery(this).attr('id').substring(7);
		jQuery("div.updated").html("<p><img src=\"<?php echo admin_url(); ?>images/loading.gif\" /><?php _e("Restoring backup", WP_BEIFEN_DOMAIN); ?></p>");
		jQuery("div.updated").show();
		send_request('restore_backup',options,restore_backup);
	});

	// Add delete event to all schedule_delete buttons
	jQuery("input.schedule_delete").click(function () {
		var options = "id=" + jQuery(this).attr('id');
		jQuery("div.updated").html("<p><img src=\"<?php echo admin_url(); ?>images/loading.gif\" /><?php _e("Deleting schedule", WP_BEIFEN_DOMAIN); ?></p>");
		jQuery("div.updated").show();
		send_request('delete_schedule',options,delete_schedule_entry);
	});

	// Display result
	function restore_backup()
	{
		try
		{
			var result = eval("(" + response + ')');
			var message = '<h3>' + result.status + '!</h3>'
						+ '<p>' + result.message + '!</p>';
			// Change update message
			jQuery("#update_message").html(message);
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

	// Remove schedule entry from table
	function delete_schedule_entry(response)
	{
		try
		{
			var result = eval("(" + response + ')');
			var message = '<h3>' + result.status + '!</h3>'
						+ '<p>' + result.message + '!</p>';
			// Change update message
			jQuery("#update_message").html(message);
			jQuery("#schedule-row-" + result.id).fadeOut("slow", function()
			{
				jQuery("#schedule-row-" + result.id).remove();
			});
			get_schedule_count();
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

	// Remove backup entry from table
	function delete_backup_entry(response)
	{
		try
		{
			var result = eval("(" + response + ')');
			var message = '<h3>' + result.status + '!</h3>'
						+ '<p>' + result.message + '!</p>';
			// Change update message
			jQuery("#update_message").html(message);
			jQuery("#row-" + result.id).fadeOut("slow", function()
			{
				jQuery("#row-" + result.id).remove();
			});
			get_backup_count();
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

	// Get count of backups and remove table if necessary
	function get_backup_count()
	{
		send_request('get_backup_count','null=null',update_backup_table);
	}

	// Get count of backups and remove table if necessary
	function get_schedule_count()
	{
		send_request('get_schedule_count','null=null',update_schedule_table);
	}

	function update_backup_table(response)
	{
		try
		{
			var result = eval('(' + response + ')');
			if(result.count=="0")
			{
				jQuery("#backup_table").replaceWith("<p><?php _e("No backups created so far!", WP_BEIFEN_DOMAIN); ?> <?php _e("You can do so", WP_BEIFEN_DOMAIN); ?> <a href=\"admin.php?page=<?php echo basename($options['plugin_location']); ?>/pages/create_backup.php\"><?php _e("here", WP_BEIFEN_DOMAIN); ?></a>.</p>");
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

	function update_schedule_table(response)
	{
		try
		{
			var result = eval('(' + response + ')');
			if(result.count=="0")
			{
				jQuery("#schedule_table").replaceWith('<p><?php _e("No backups scheduled so far!", WP_BEIFEN_DOMAIN); ?>  <?php _e("You can do so", WP_BEIFEN_DOMAIN); ?> <a href="admin.php?page=<?php echo basename($options['plugin_location']); ?>/pages/create_scheduled_backup.php"><?php _e("here", WP_BEIFEN_DOMAIN); ?></a>.</p>');
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
})
</script>
<div class="wrap">
	<div id="beifen_wrap">
		<div id="beifen_sidebar">
			<?php require_once("bf-info.php"); ?>
		</div>
		<div id="beifen_content">
			<h2><?php _e("Manage Backups", WP_BEIFEN_DOMAIN); ?></h2>
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
					global $wpdb;
					$table_name = $wpdb->prefix . "beifen";
					$sql = "SELECT id,name,DATE_FORMAT(created,'%Y-%m-%d-%k-%i') as created,type,zipped FROM $table_name";
					$backup_list = $wpdb->get_results($sql, OBJECT );
					if($wpdb->num_rows>0)
					{
						?>
							<table id="backup_table" class="widefat">
								<thead>
									<tr>
										<th><?php _e("Backup Name", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Create Date", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Type", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Is Zipped?", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Actions", WP_BEIFEN_DOMAIN); ?></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th><?php _e("Backup Name", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Create Date", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Type", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Is Zipped?", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Actions", WP_BEIFEN_DOMAIN); ?></th>
									</tr>
								</tfoot>
						<?php
						foreach($backup_list as $backup_entry)
						{
							$backup_date_parts = explode('-', $backup_entry->created);
							$backup_date_timestamp = mktime($backup_date_parts[3],
								$backup_date_parts[4],
								0,
								$backup_date_parts[1],
								$backup_date_parts[2],
								$backup_date_parts[0]);

							?>
								<tr id="row-<?php echo $backup_entry->id; ?>">
									<td><?php echo $backup_entry->name; ?></td>
									<td><?php echo date($date_options, $backup_date_timestamp) . ', ' . date($time_options, $backup_date_timestamp); ?></td>
									<td><?php _e($backup_entry->type, WP_BEIFEN_DOMAIN); ?></td>
									<td>
										<?php
											if($backup_entry->zipped==true)
											{
												_e("Yes", WP_BEIFEN_DOMAIN);
											}
											else
											{
												_e("No", WP_BEIFEN_DOMAIN);
											}
										?>
									<td>
										<input class="backup_delete" type="submit" id="backup-<?php echo $backup_entry->id; ?>" value="<?php _e("Delete", WP_BEIFEN_DOMAIN); ?>" />
										<input class="backup_restore" type="submit" id="backup-<?php echo $backup_entry->id; ?>" value="<?php _e("Restore", WP_BEIFEN_DOMAIN); ?>" />
									</td>
								</tr>
							<?php
						}
						echo "</table>";
					}
					else
					{
						?>
							<p>
								<?php
									echo __("No backups created so far!", WP_BEIFEN_DOMAIN)
										. ' '
										. __("You can do so", WP_BEIFEN_DOMAIN);
								?>
								<a href="admin.php?page=<?php echo basename($options['plugin_location']); ?>/pages/create_backup.php"><?php _e("here", WP_BEIFEN_DOMAIN); ?></a>.
							</p>
						<?php
					}
					?>
						<h2><?php _e("Scheduled Backups", WP_BEIFEN_DOMAIN); ?></h2>
						<?php

						$scheduled_backups = get_option('WP_BEIFEN_SCHEDULED_BACKUPS');

						if($options['backup_schedule']==false && count($scheduled_backups)>0)
						{
							?>
								<div class="error"><p>
									<?php echo __("Scheduling is deactivated! Please activate scheduling!", WP_BEIFEN_DOMAIN) . ' '. __("You can do so", WP_BEIFEN_DOMAIN);
								?>
								<a href="admin.php?page=<?php echo basename($options['plugin_location']); ?>/pages/create_scheduled_backup.php"><?php _e("here", WP_BEIFEN_DOMAIN); ?></a>!
								</p></div>
							<?php
						}

						if(count($scheduled_backups)==0 || !is_array($scheduled_backups)) : ?>
							<p>
								<?php
									echo __("No backups scheduled so far!", WP_BEIFEN_DOMAIN)
										. ' '
										. __("You can do so", WP_BEIFEN_DOMAIN);
								?>
								<a href="admin.php?page=<?php echo basename($options['plugin_location']); ?>/pages/create_scheduled_backup.php"><?php _e("here", WP_BEIFEN_DOMAIN); ?></a>.
							</p>
						<?php else : ?>
							<table id="schedule_table" class="widefat">
								<thead>
									<tr>
										<th><?php _e("Backup Name", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Schedule Type", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Next Backup", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Actions", WP_BEIFEN_DOMAIN); ?></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th><?php _e("Backup Name", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Schedule Type", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Next Backup", WP_BEIFEN_DOMAIN); ?></th>
										<th><?php _e("Actions", WP_BEIFEN_DOMAIN); ?></th>
									</tr>
								</tfoot>
								<?php
									if(count($scheduled_backups)>0)
									{
										for($x=0; $x < count($scheduled_backups); $x++)
										{
											?>
												<tr id="schedule-row-<?php echo $scheduled_backups[$x]['backup_name']; ?>">
													<td><?php echo $scheduled_backups[$x]['backup_name']; ?></td>
													<td>
														<?php
															if($scheduled_backups[$x]['schedule_type']=='Single')
																_e("Single", WP_BEIFEN_DOMAIN);
															else
																_e("Frequent", WP_BEIFEN_DOMAIN);
														?>
													</td>
													<td><?php echo date($date_options, $scheduled_backups[$x]['next_backup']) . ', ' . date($time_options, $scheduled_backups[$x]['next_backup']); ?></td>
													<td>
														<input class="schedule_delete" type="submit" id="delete-<?php echo $scheduled_backups[$x]['backup_name']; ?>" value="<?php _e("Delete", WP_BEIFEN_DOMAIN); ?>" />
													</td>
												</tr>
											<?php
										}
									}
								?>
							</table>
						<?php endif; ?>
					<?php
				}
			?>
		</div>
		<div class="clear"></div>
	</div>
</div>