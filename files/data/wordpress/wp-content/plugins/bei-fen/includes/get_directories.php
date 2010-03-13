<?php

function get_sub_directories($path)
{
	// Prepare result array
	$result = array();
	// Get dir handle
	$directory = dir($path);
	// Read directory
	while (false !== ($entry = $directory->read()))
	{
		// if file is directory and not . or .., add it to result
		if(is_dir($directory->path . $entry) && $entry != '.' && $entry != '..')
			$result[] = wp_beifen_clean_path($directory->path . $entry. DS);
	}
	// Close handle
	$directory->close();
	// Return result
	return $result;
}

function wp_beifen_process_ajax_request($request_options)
{
	if(file_exists($request_options['backup_directory']) && is_dir($request_options['backup_directory']))
	{
		$directories = get_sub_directories($request_options['backup_directory']);
		// Return success message and subdirectories
		$result['status'] = __("Success", WP_BEIFEN_DOMAIN);
		$result['directories'] = $directories;
		return $result;
	}
	else
	{
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);;
		return $result;
	}
}

?>