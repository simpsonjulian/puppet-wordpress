<?php

function wp_beifen_process_ajax_request($request_options)
{
	// Get options
	$options = get_option(WP_BEIFEN_OPTIONS);
	// Load required class definition and create instance
	require_once($options['plugin_location'] . 'classes' . DS . 'database.php');
	$bkp_db = new XinitBackupDatabaseHelper();

	$result['status'] = __("Success", WP_BEIFEN_DOMAIN);
	$result['count'] = $bkp_db->getBackupCount();
	return $result;
}

?>