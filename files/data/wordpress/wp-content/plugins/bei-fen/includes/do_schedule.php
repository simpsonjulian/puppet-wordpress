<pre>
<?php

	require_once($options['plugin_location'] . 'classes' . DS . 'class.schedule.php');
	require_once($options['plugin_location'] . 'classes' . DS . 'class.backup.php');

	foreach($scheduled_backups as $scheduled_backup)
	{
		$bkp = new BeiFenBackup($scheduled_backup);

		if($scheduled_backup['schedule_type']=='Single') // Single backups first
		{
			if($bkp->Schedule->isPastDue())
			{
				echo "Preparing single backup: " . $scheduled_backup['backup_name'] . "<br>";
				$backup_details['backup_name'] = $scheduled_backup['backup_name'];
				$backup_details['backup_type'] = $scheduled_backup['backup_type'];
				$backup_details['backup_timeout'] = $scheduled_backup['compress_backup'];
				$backup_details['backup_name'] = $scheduled_backup['backup_name'];
			}
		}
		else // Frequent backups last
		{
			if($scheduled_backup['next_backup']<time())
			{
				echo "Preparing frequent backup: " . $scheduled_backup['backup_name'] . "<br>";
			}
		}
		var_dump($scheduled_backup);
	}

function create_scheduled_backup($request_options)
{
	// Modify time limit
	set_time_limit($request_options['backup_timeout']);

	// Get options
	$options = get_option(WP_BEIFEN_OPTIONS);

	// A shortcut for DIRECTORY_SEPARATOR
	if(!defined('DS'))
	{
		define('DS', DIRECTORY_SEPARATOR);
	}

	// Defines for recursive function use
	define('WP_BEIFEN_DIR', $options['plugin_backup_directory']);
	define('WP_BEIFEN_CURRENT_BACKUP', WP_BEIFEN_DIR . $request_options['backup_name'] . DS);
	define('WP_BEIFEN_CURRENT_DB_DIR', WP_BEIFEN_CURRENT_BACKUP . 'database' . DS);

	// Load required class definitions and create instances
	require_once($options['plugin_location'] . 'classes' . DS . 'file.php');
	require_once($options['plugin_location'] . 'classes' . DS . 'database.php');
	$bkp_db = new XinitBackupDatabaseHelper();
	$bkp_file = new XinitBackupFileHelper();

	// Check for existing backups with same name
	if($bkp_db->existingBackup($request_options['backup_name']))
	{
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
		$result['message'] = __("There is already a backup with this name. Please change the name!", WP_BEIFEN_DOMAIN);
		return $result;
	}

	// Check if main backup directory is writable
	if(!is_writable(WP_BEIFEN_DIR))
	{
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
		$result['message'] = __("The backup folder is not writable. Please check write-permissions!", WP_BEIFEN_DOMAIN);
		return $result;
	}

	// Check if backup destination directory is writable and existing
	if(!@mkdir(WP_BEIFEN_CURRENT_BACKUP) && !file_exists(WP_BEIFEN_CURRENT_BACKUP))
	{
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
		$result['message'] = __("Could not create backup folder. Please check write-permissions!", WP_BEIFEN_DOMAIN);
		return $result;
	}

	// if DB backup, check db destionation
	if(($request_options['backup_type'] == 'Complete') || ($request_options['backup_type'] == 'DB'))
	{
		if(!file_exists(WP_BEIFEN_CURRENT_DB_DIR) && !mkdir(WP_BEIFEN_CURRENT_DB_DIR))
		{
			$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
			$result['message'] = __("The database backup folder is not writable. Please check write-permissions!", WP_BEIFEN_DOMAIN);
			return $result;
		}
	}

	// Windows path hack
	if(DS=='/')
	{
		define('ABSPATH_CUSTOM', ABSPATH);
	}
	else
	{
		$custom_abspath = substr(ABSPATH, 0, -1);
		define('ABSPATH_CUSTOM', $custom_abspath);
	}

	// Exclude backup directories
	$exclude = array();
	// Exclude the backup itself
	$exclude[] = WP_BEIFEN_CURRENT_BACKUP;
	// Exclude existing backups?
	if(!$options['include_backup_directory'])
	{
		$exclude[] = WP_BEIFEN_DIR;
	}

	// Do it!
	switch($request_options['backup_type'])
	{
		case 'Complete':  // Complete backup
			// Database backup
			foreach($bkp_db->getTableNames() as $table_name)
			{
				$dump = $bkp_db->getTableDump($table_name);
				if($dump!='')
				{
					$bkp_file->writeTextToFile(WP_BEIFEN_CURRENT_DB_DIR . $table_name . '.sql', $dump);
				}
				unset($dump);
			}
			// File Backup
			$bkp_file->copyFolderRec(ABSPATH_CUSTOM,WP_BEIFEN_CURRENT_BACKUP . 'root' . DS, $exclude);
			break;
		case 'Files':  // files only backup
			$bkp_file->copyFolderRec(ABSPATH_CUSTOM,WP_BEIFEN_CURRENT_BACKUP . 'root' . DS, $exclude);
			break;
		case 'DB':	// DB only backup
			foreach($bkp_db->getTableNames() as $table_name)
			{
				$dump = $bkp_db->getTableDump($table_name);
				if($dump!='')
				{
					$bkp_file->writeTextToFile(WP_BEIFEN_CURRENT_DB_DIR . $table_name . '.sql', $dump);
				}
				unset($dump);
			}
			break;
		default:
			$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
			$result['message'] = __("This is not a valid backup type!", WP_BEIFEN_DOMAIN);
			return $result;
	}

	// Zip backup, if requested
	if($request_options["compress_backup"]=='Yes')
	{
		require_once($options['plugin_location'] . 'classes' . DS . 'zip.php');
		$zipper = new XinitBackupZipHelper();
		$zipper->createZip(WP_BEIFEN_CURRENT_BACKUP . $request_options['backup_name'].'.zip', WP_BEIFEN_CURRENT_BACKUP);
	}

	// Create index.html and .htaccess
	$empty_html = '<html><body></body></html>';
	$htaccess_l1 = "RewriteEngine on\n";
	$htaccess_l2 = "RewriteRule (.*) " . get_bloginfo('url') . "/ [R=301,L]";
	$bkp_file->writeTextToFile(WP_BEIFEN_CURRENT_BACKUP . 'index.html', $empty_html, 'a');
	$bkp_file->writeTextToFile(WP_BEIFEN_CURRENT_BACKUP . '.htaccess', $htaccess_l1, 'w');
	$bkp_file->writeTextToFile(WP_BEIFEN_CURRENT_BACKUP . '.htaccess', $htaccess_l2, 'a');
	$bkp_file->writeTextToFile(WP_BEIFEN_CURRENT_DB_DIR . 'index.html', $empty_html, 'a');
	$bkp_file->writeTextToFile(WP_BEIFEN_CURRENT_DB_DIR . '.htaccess', $htaccess_l1, 'w');
	$bkp_file->writeTextToFile(WP_BEIFEN_CURRENT_DB_DIR . '.htaccess', $htaccess_l2, 'a');

	// If everythings ok ...
	$zipped = ($request_options["compress_backup"]=='Yes')  ? "TRUE" : "FALSE";
	$bkp_db->insertNewBackupEntry($request_options['backup_name'], WP_BEIFEN_CURRENT_BACKUP, $request_options['backup_type'], $zipped);
	$result['status'] = __("Success", WP_BEIFEN_DOMAIN);
	$result['message'] = __("Backup has been successfully created!", WP_BEIFEN_DOMAIN);
	if($options['enable_debugging']==true)
	{
		$memory_usage = memory_get_usage()/1048576;
		$result['message'] .= '<br/>Memory usage: ' . number_format($memory_usage, 2) . ' MB';
	}
	return $result;
}

?>