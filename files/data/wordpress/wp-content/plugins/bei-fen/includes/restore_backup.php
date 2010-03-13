<?php

function wp_beifen_process_ajax_request($request_options)
{
	// Get options and schedules, need to rewrite them after restore
	$options = get_option(WP_BEIFEN_OPTIONS);
	$scheduled_backups = get_option('WP_BEIFEN_SCHEDULED_BACKUPS');
	
	// Load required class definitions and create instances
	require_once($options['plugin_location'] . 'classes' . DS . 'file.php');
	require_once($options['plugin_location'] . 'classes' . DS . 'database.php');
	$bkp_db = new XinitBackupDatabaseHelper();
	$bkp_file = new XinitBackupFileHelper();
	
	global $wpdb;
	$table_name = $wpdb->prefix . "beifen";
	$sql = "SELECT id,name,DATE_FORMAT(created,'%Y-%m-%d-%k-%i') as created,type,zipped FROM $table_name LIMIT 1";
	$backup_details = $wpdb->get_row($sql, OBJECT );
	if($wpdb->num_rows==1)
	{	
		$backup_directory = $options['plugin_backup_directory'] . $backup_details->name . DS;
		// Source
		$source = $backup_directory . 'root' . DS;
		// Destination
		// Windows path hack
		if(DS=='/')
		{
			$custom_abspath =  ABSPATH;
		}
		else
		{
			$custom_abspath = substr(ABSPATH, 0, -1);;
		}
		$destination = $custom_abspath;
		$error_list = array();
		if(($backup_details->type=='Complete') || ($backup_details->type=='Files'))
		{
			// Is destination writable?
			$error_list = $bkp_file->destination_writable($destination);
		}
		if(count($error_list)==0)
		{
			// Blog is writable
			switch($backup_details->type)
			{
				case 'Complete':
					$bkp_file->copyFolderRec($source, $destination);
					$dump = readDumpFolder($backup_directory . 'database' . DS);
					if(!restoreDump($dump))
					{
						$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
						$result['message'] = __("Could not restore database!", WP_BEIFEN_DOMAIN);
						$result['message'] .= mysql_error();
						return $result;
					}
					break;
				case 'Files':
					$bkp_file->copyFolderRec($source, $destination);
					break;
				case 'DB':
					$dump = readDumpFolder($backup_directory . 'database' . DS);
					if(!restoreDump($dump))
					{
						$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
						$result['message'] = __("Could not restore database!", WP_BEIFEN_DOMAIN);
						return $result;
					}
					break;
				case 'Custom':
					break;
				default:
					$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
					$result['message'] = __("This is not a valid backup type!", WP_BEIFEN_DOMAIN);
					return $result;
			}
			$result['status'] = __("Success", WP_BEIFEN_DOMAIN);
			$result['message'] = __("Backup was successfully restored!", WP_BEIFEN_DOMAIN);
			return $result;
		}
		else
		{
			$result['message'] .= 'Blog not writable!<br/>';
			foreach($error_list as $error_file)
			{
				$result['message'] .= 'File: ' . $error_file . ' not writable<br/>';
			}
			$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
			return $result;
		}
	}
	else
	{
	
		update_option(WP_BEIFEN_OPTIONS,$options);
		update_option('WP_BEIFEN_SCHEDULED_BACKUPS',$scheduled_backups);
		//$bkp_file->destination_writable($test);
		// Return success message
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
		$result['message'] =  __("Invalid backup ID!", WP_BEIFEN_DOMAIN);
		return $result;
	}
}

function readDumpFolder($folder)
{
	// Get options
	$options = get_option(WP_BEIFEN_OPTIONS);
	require_once($options['plugin_location'] . 'classes' . DS . 'file.php');
	$bkp_file = new XinitBackupFileHelper();
	$dump = '';
	if(file_exists($folder))
	{
		$folderhandle = opendir($folder);
		if($folderhandle)
		{
			while (($entry = readdir($folderhandle)) !== false)
			{
				if($entry == false || $entry =='..' || $entry == '.' || $entry == '.htaccess' || $entry == 'index.html') continue;
				$dump .= $bkp_file->readTextFromFile($folder . $entry);
			}
		}
		else
		{
			die("Cannot read Folder!");
		}
	}
	else
	{
		die("Folder does not exist!");
	}
	return $dump;
}

function restoreDump($dump)
{
	$link = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
	if(!$link)
	{
		return false;
	}
	if(!mysql_select_db(DB_NAME))
	{
		return false;
	}
	$queries = explode("-- End of query\n", $dump);
	unset($dump);
	foreach($queries as $query)
	{
		if(strlen($query)>3)
		{
			// Windows MySQL Hack
			str_replace('\'', '\"',$query);
			if(!mysql_query($query, $link))
			{
				return false;
			}
		}
	}
	unset($queries);
	mysql_close($link);
	return true;
}
?>