<?php

function wp_beifen_process_ajax_request($request_options)
{
	$scheduled_backups = get_option('WP_BEIFEN_SCHEDULED_BACKUPS');
	$schedule_name = substr($request_options['id'],7);
	$remaining_scheduled_backups = array();
	for($x=0; $x<count($scheduled_backups); $x++)
	{
		if($scheduled_backups[$x]['backup_name']!=$schedule_name)
		{
			$remaining_scheduled_backups[] = $scheduled_backups[$x];
		}
	}
	update_option('WP_BEIFEN_SCHEDULED_BACKUPS', $remaining_scheduled_backups);

	$result['status'] = __("Success", WP_BEIFEN_DOMAIN);
	$result['message'] = __("Backup deleted", WP_BEIFEN_DOMAIN);
	$result['id'] = $schedule_name;
	return $result;
}

?>