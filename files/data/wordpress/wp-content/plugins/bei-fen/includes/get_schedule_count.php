<?php

function wp_beifen_process_ajax_request($request_options)
{
	$scheduled_backups = get_option('WP_BEIFEN_SCHEDULED_BACKUPS');

	$result['status'] = __("Success", WP_BEIFEN_DOMAIN);
	$result['count'] = count($scheduled_backups);
	return $result;
}

?>