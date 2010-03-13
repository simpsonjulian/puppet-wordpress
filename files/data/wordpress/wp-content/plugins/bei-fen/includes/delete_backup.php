<?php

function wp_beifen_process_ajax_request($request_options)
{
	// Get options
	$options = get_option(WP_BEIFEN_OPTIONS);
	$bkp_id = substr($request_options['id'],7);

	// Load required class definitions and create instances
	require_once($options['plugin_location'] . 'classes' . DS . 'file.php');
	require_once($options['plugin_location'] . 'classes' . DS . 'database.php');
	$bkp_db = new XinitBackupDatabaseHelper();
	$bkp_file = new XinitBackupFileHelper();

	$the_backup = $bkp_db->getBackupByID($bkp_id);
	$bkp_file->deleteFolder($the_backup->location);
	$bkp_db->deleteBackupEntry($bkp_id);

	$result['status'] = __("Success", WP_BEIFEN_DOMAIN);
	$result['message'] = __("Backup deleted", WP_BEIFEN_DOMAIN);
	$result['id'] = $bkp_id;
	return $result;
}

?>