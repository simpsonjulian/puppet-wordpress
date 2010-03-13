<?php

function wp_beifen_process_ajax_request($request_options)
{
	// Check if directory exists
	$request_options['backup_directory'] = wp_beifen_clean_path($request_options['backup_directory']);
	if(!file_exists($request_options['backup_directory']))
	{
		// No, can create it?
		if(!@mkdir($request_options['backup_directory']))
		{
			// No, return error message
			$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
			$result['message'] = __("The backup directory does not exist and cannot be created!", WP_BEIFEN_DOMAIN);
			return $result;
		}
	}
	// Is file a directory ?
	if(!is_dir($request_options['backup_directory']))
	{
		// No, return error message
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
		$result['message'] = __("The path is not a valid directory!", WP_BEIFEN_DOMAIN);
		return $result;
	}
	// Is directory writable?
	if(!is_writable($request_options['backup_directory']))
	{
		// No, return error message
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
		$result['message'] = __("The backup directory is not writable!", WP_BEIFEN_DOMAIN);
		return $result;
	}
	// Is execution time valid?
	if($request_options['default_timeout']<0 && $request_options['default_timeout']>299)
	{
		// No, return error message
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
		$result['message'] = __("The execution time limit is invalid!", WP_BEIFEN_DOMAIN);
		return $result;
	}
	
	// All checks ok, update options
	if(!defined('DS')) define('DS',DIRECTORY_SEPARATOR);
	// Check for trailing slash
	if($request_options['backup_directory'][strlen($request_options['backup_directory'])-1] != DS)
	{
		$request_options['backup_directory'] .= DS;
	}
	
	//get and change options
	$options = get_option(WP_BEIFEN_OPTIONS);
	$options['plugin_backup_directory'] = $request_options['backup_directory'];
	$options['plugin_ready'] = true;
	$options['default_timeout'] = $request_options['default_timeout'];
	if($request_options['enable_debugging']=='yes')
	{
		$options['enable_debugging'] = true;
	}
	else
	{
		$options['enable_debugging'] = false;
	}
	if($request_options['include_backup_directory']=='yes')
	{
		$options['include_backup_directory'] = true;
	}
	else
	{
		$options['include_backup_directory'] = false;
	}
	if($request_options['backup_schedule']=='yes')
	{
		$options['backup_schedule'] = true;
		wp_schedule_event(time(), 'hourly', 'beifen_hourly_backup_check');
	}
	else
	{
		$options['backup_schedule'] = false;
		wp_clear_scheduled_hook('beifen_hourly_backup_check');
	}
	
	// update options	
	update_option(WP_BEIFEN_OPTIONS ,$options);
	
	// Create index.php and .htaccess
	require_once($options['plugin_location'] . 'classes' . DS . 'file.php');
	$empty_html = '<html><body></body></html>';
	$htaccess_l1 = "RewriteEngine on\n";
	$htaccess_l2 = "RewriteRule (.*) " . get_bloginfo('url') . "/ [R=301,L]";
	$file = new XinitBackupFileHelper();
	$file->writeTextToFile($request_options['backup_directory'] . 'index.html', $empty_html, 'a');
	$file->writeTextToFile($request_options['backup_directory'] . '.htaccess', $htaccess_l1, 'w');
	$file->writeTextToFile($request_options['backup_directory'] . '.htaccess', $htaccess_l2, 'a');
		
	// Return success message
	$result['status'] = __("Success", WP_BEIFEN_DOMAIN);
	$result['message'] = __("All changes were saved successfully!", WP_BEIFEN_DOMAIN);	
	return $result;
}

?>