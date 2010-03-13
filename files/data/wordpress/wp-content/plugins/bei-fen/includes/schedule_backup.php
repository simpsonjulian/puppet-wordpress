<?php

function wp_beifen_process_ajax_request($request_options)
{
	// Get options
	$options = get_option(WP_BEIFEN_OPTIONS);

	// Load required class definitions and create instances
	require_once($options['plugin_location'] . 'classes' . DS . 'database.php');
	require_once($options['plugin_location'] . 'classes' . DS . 'class.schedule.php');
	require_once($options['plugin_location'] . 'classes' . DS . 'class.backup.php');

	$bkp_db = new XinitBackupDatabaseHelper();

	// Check for existing backups with same name
	if($bkp_db->existingBackup($request_options['backup_name']))
	{
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
		$result['message'] = __("There is already a backup with this name. Please change the name!", WP_BEIFEN_DOMAIN);
		return $result;
	}

	$scheduled_backup = new BeiFenBackup($request_options);
	if($request_options['schedule_type']=='Single')
	{
		if($scheduled_backup->Schedule->isPastDue())
		{
			$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
			$result['message'] =__("The schedule is past due!", WP_BEIFEN_DOMAIN);
			$result['message'] .= date('j.n.Y H:i',$scheduled_backup->Schedule->nextBackup);
			return $result;
		}
	}
	$scheduled_backups = get_option('WP_BEIFEN_SCHEDULED_BACKUPS');
	foreach($scheduled_backups as $backup)
	{
		if($backup['backup_name']==$request_options['backup_name'])
		{
			$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
			$result['message'] =__("There is already a schedule with this name!", WP_BEIFEN_DOMAIN);
			return $result;
		}
	}
	$request_options['prev_backup'] = null;
	$request_options['next_backup'] = $scheduled_backup->Schedule->nextBackup;
	$scheduled_backups[] = $request_options;
	update_option('WP_BEIFEN_SCHEDULED_BACKUPS', $scheduled_backups);

	$result['status'] = __("Success", WP_BEIFEN_DOMAIN);
	$result['message'] =__("Backup schedule was successfully saved!", WP_BEIFEN_DOMAIN);
	return $result;
}

?>