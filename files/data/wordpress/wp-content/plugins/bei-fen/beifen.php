<?php
/*
Plugin Name: Bei Fen
Plugin URI: http://www.beifen.info/
Description: A backup plugin for Wordpress. You can create full, files-only, and database-only backups! Scheduled backups are also possible!
Version: 1.4.2
Author: David Schneider
Author URI: http://www.david-schneider.name/
Text Domain: beifen
Domain Path: i18n
*/

/***********/
/* Defines */
/***********/

// A shortcut for DIRECTORY_SEPARATOR
if(!defined(DS))
{
	define('DS', DIRECTORY_SEPARATOR);
}

// Name of the plugin options. Change it, if some hero is using the same name
if(!defined(WP_BEIFEN_OPTIONS))
{
	define('WP_BEIFEN_OPTIONS', 'WP_BEIFEN_OPTIONS');
}

// Plugin version
if(!defined(WP_BEIFEN_VERSION))
{
	define('WP_BEIFEN_VERSION', '142');
}

// Debug option for developers, to enable it set to 1
if(!defined(WP_BEIFEN_DEV_DEBUG))
{
	define('WP_BEIFEN_DEV_DEBUG', '0');
}

// Nonce for AJAX activities
if(!defined(WP_BEIFEN_NONCE))
{
	define('WP_BEIFEN_NONCE', 'WP_BEIFEN_NONCE');
}

// Nonce for AJAX activities
if(!defined(WP_BEIFEN_DOMAIN))
{
	define('WP_BEIFEN_DOMAIN', 'beifen');
}

// Scheduled backup
if(!defined(WP_BEIFEN_SCHEDULED_BACKUPS))
{
	define('WP_BEIFEN_SCHEDULED_BACKUPS', 'WP_BEIFEN_SCHEDULED_BACKUPS');
}

/*************/
/* Functions */
/*************/

// Activate plugin
function wp_beifen_activate()
{
	// Update necessary?
	$old_version = get_option('WP_BEIFEN_VERSION');
	// Add version number, won't be deleted, if plugin is deactivated
	if(!$old_version)
	{
		add_option('WP_BEIFEN_VERSION',WP_BEIFEN_VERSION);
	}
	else
	{
		update_option('WP_BEIFEN_VERSION',WP_BEIFEN_VERSION);
	}
	// Create base options, will be deleted, if plugin is deactivated
	$backup_options = array();
	$backup_options['plugin_location'] = dirname(__FILE__) . DS;
	$backup_options['backup_schedule'] = false;
	$backup_options['plugin_backup_directory'] = WP_CONTENT_DIR . DS . 'backups' . DS;
	$backup_options['include_backup_directory'] = false;
	$backup_options['enable_debugging'] = true;
	$backup_options['default_timeout'] = ini_get('max_execution_time');
	if(@mkdir($backup_options['plugin_backup_directory']))
	{
		$backup_options['plugin_ready'] = true;
	}
	else
	{
		$backup_options['plugin_ready'] = false;
	}
	add_option(WP_BEIFEN_OPTIONS,$backup_options);
	// Create MySQL table for storing backup information
	global $wpdb;
	$table_name = $wpdb->prefix . 'beifen';
	// If there is no table, create one
	$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
		`id` tinyint(8) unsigned NOT NULL auto_increment,
		`name` varchar(255) NOT NULL,
		`location` varchar(255) NOT NULL,
		`created` timestamp NOT NULL default CURRENT_TIMESTAMP,
		`type` enum('Complete', 'DB', 'Files', 'Custom') NOT NULL,
		`zipped` tinyint(1) NOT NULL,
		PRIMARY KEY  (`id`)
	)";
	$wpdb->query($sql);
	if(!$old_version) // PRE 1.2.5, so update the the table, if there was an old on
	{
		$sql = "ALTER TABLE `$table_name` CHANGE `type` `type` ENUM( 'Complete', 'DB', 'Files', 'Custom' )";
		$wpdb->query($sql);
		$sql = "UPDATE `$table_name` SET `type` = `Complete` WHERE `type` = ``";
		$wpdb->query($sql);
	}
}

// Deactivate plugin
function wp_beifen_deactivate()
{
	// Delete options
	delete_option(WP_BEIFEN_OPTIONS);
}

// Create Settings Link for the Plugins page
function wp_beifen_settings_link($links)
{
	$plugin_dir = basename(dirname(__FILE__));
	$settings_link = '<a href="admin.php?page=' . $plugin_dir . '/beifen.php">' . __("Settings", WP_BEIFEN_DOMAIN) . '</a>';
	array_unshift($links, $settings_link);
	return $links;
}

// Display backup menu entries
function wp_beifen_menu()
{
	$menu_settings = __("Settings", WP_BEIFEN_DOMAIN);
	$menu_manage = __("Manage Backups", WP_BEIFEN_DOMAIN);
	$menu_create = __("Create New Backup", WP_BEIFEN_DOMAIN);
	$menu_schedule = __("Schedule New Backup", WP_BEIFEN_DOMAIN);
	// Add new main menu
	add_menu_page(__("Backup", WP_BEIFEN_DOMAIN), __("Backup", WP_BEIFEN_DOMAIN), 'administrator', __FILE__, 'wp_beifen_display_settings_page');
	// Double entry to avoid double link in the main menu
	add_submenu_page(__FILE__, $menu_settings, $menu_settings, 'administrator', __FILE__, 'wp_beifen_display_settings_page');
	// Add submenu for managing backups
	add_submenu_page(__FILE__, $menu_manage, $menu_manage, 'administrator',  dirname(__FILE__) . DS . 'pages' . DS . 'manage_backups.php');
	// Add submenu for creating backups
	add_submenu_page(__FILE__, $menu_create, $menu_create, 'administrator',  dirname(__FILE__) . DS . 'pages' . DS . 'create_backup.php');
	// Add submenu for scheduling backups
	add_submenu_page(__FILE__, $menu_schedule, $menu_schedule, 'administrator',  dirname(__FILE__) . DS . 'pages' . DS . 'create_scheduled_backup.php');
}

// Display settings page
// Needed for the plugin page and the main menu
function wp_beifen_display_settings_page()
{
	require_once(dirname(__FILE__) . DS . 'pages' . DS . 'settings.php');
}

// Display custom stylesheet
function wp_beifen_custom_css()
{
    $url = get_settings('siteurl');
    $url = $url . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/includes/style.css';
    echo '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
}

// clean path strings with double DS
function wp_beifen_clean_path($path)
{
	$pattern = "/[\\" . DS . "]{2,}/is";
	return preg_replace($pattern, '\\', $path);;
}

/******************************/
/* Special functions for AJAX */
/******************************/

// Custom AJAX/JQuery Handler
function wp_beifen_ajax_handler() {
	check_ajax_referer(WP_BEIFEN_NONCE);
	set_error_handler('ajax_error_handler');
	if(file_exists(dirname(__FILE__) . DS . 'includes' . DS . $_POST['task'] . '.php'))
	{
		$parameters = wp_beifen_deserialize_ajax_request($_POST['parameters']);
		require_once(dirname(__FILE__) . DS . 'includes' . DS . $_POST['task'] . '.php');
		$result = wp_beifen_process_ajax_request($parameters);
		echo custom_json_encode($result);
	}
	else
	{
		$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
		$result['message'] =__("The requested task is invalid!", WP_BEIFEN_DOMAIN);
		echo custom_json_encode($result);
	}
	restore_error_handler();
	die();
}

function wp_beifen_deserialize_ajax_request($parameters)
{
	$deserialized = array();
	foreach(explode('&', $parameters) as $parameter)
	{
		$temp = explode('=', $parameter);
		$deserialized[$temp[0]] = $temp[1];
	}
	return $deserialized;
}

function ajax_error_handler($error_code, $error_message, $error_file, $error_line)
{
	switch($error_code)
	{
		case 2:
			// only a warning, ignore it
			break;
		case 2048:
			if(WP_BEIFEN_DEV_DEBUG=='1')
			{
				$result['status'] = __("Warning", WP_BEIFEN_DOMAIN);
				$result['message'] =__("A warning! Request aborted!", WP_BEIFEN_DOMAIN);
				$result['message'] .= '<br>Error Code:';
				$result['message'] .= $error_code;
				$result['message'] .= '<br>Message: ';
				$result['message'] .= $error_message;
				$result['message'] .= '<br>File: ';
				$result['message'] .= $error_file;
				$result['message'] .= '<br>Line: ';
				$result['message'] .= $error_line;
				echo custom_json_encode($result);
				die();
			}
			break;
		default:
			// some critical error, send error message and abort
			$result['status'] = __("Error", WP_BEIFEN_DOMAIN);
			$result['message'] =__("A critical error occured! Request aborted!", WP_BEIFEN_DOMAIN);
			// Display for debugging?
			$options = get_option(WP_BEIFEN_OPTIONS);
			if($options['enable_debugging'])
			{
				$result['message'] .= '<br>Error Code:';
				$result['message'] .= $error_code;
				$result['message'] .= '<br>Message: ';
				$result['message'] .= $error_message;
				$result['message'] .= '<br>File: ';
				$result['message'] .= $error_file;
				$result['message'] .= '<br>Line: ';
				$result['message'] .= $error_line;
			}
			echo custom_json_encode($result);
			die();
	}
}

function custom_json_encode($arr){
	foreach($arr as $k=>$val)
	{
		$json[] = '"' . $k . '":' . php2js($val);
	}
	if(count($json) > 0)
	{
		return '{'.implode(',', $json).'}';
	}
	else
	{
		return '';
	}
}

function arr2json($arr){
	foreach($arr as $k=>$val)
	{
		$json[] = php2js($val);
	}
	if(count($json) > 0)
	{
		return '['.implode(',', $json).']';
	}
	else
	{
		return '';
	}
}

function php2js($val){
	if(is_array($val)) return arr2json($val);
	if(is_string($val)) return '"'.addslashes($val).'"';
	if(is_bool($val)) return 'Boolean('.(int) $val.')';
	if(is_null($val)) return '""';
	return $val;
}

/**********************/
/* Schedule functions */
/**********************/

function beifen_scheduled_backup_check()
{
	date_default_timezone_set(get_option('timezone_string'));
	set_error_handler('schedule_error_handler');
	$options = get_option('WP_BEIFEN_OPTIONS');
	if($options['backup_schedule'])
	{
		require_once($options['plugin_location'] . 'classes' . DS . 'class.schedule.php');
		require_once($options['plugin_location'] . 'classes' . DS . 'class.backup.php');
		require_once($options['plugin_location'] . 'classes' . DS . 'zip.php');
		require_once($options['plugin_location'] . 'classes' . DS . 'file.php');
		require_once($options['plugin_location'] . 'classes' . DS . 'database.php');
		require_once($options['plugin_location'] . 'includes' . DS . 'create_scheduled_backup.php');

		$scheduled_backups = get_option('WP_BEIFEN_SCHEDULED_BACKUPS');

		if(is_array($scheduled_backups) && count($scheduled_backups)>0)
		{
			for($x=0; $x<count($scheduled_backups); $x++)
			{
				$bkp = new BeiFenBackup($scheduled_backups[$x]);

				if($scheduled_backups[$x]['schedule_type']=='Single') // Single backups first
				{
					if($bkp->Schedule->isPastDue())
					{
						$backup_details['backup_name'] = $scheduled_backups[$x]['backup_name'];
						$backup_details['backup_type'] = $scheduled_backups[$x]['backup_type'];
						$backup_details['backup_timeout'] = $scheduled_backups[$x]['backup_timeout'];
						$backup_details['compress_backup'] = $scheduled_backups[$x]['compress_backup'];

						$bkp_result = create_scheduled_backup($backup_details);
						if($scheduled_backups[$x]['send_email_confirmation']=='Yes')
						{
							$result .= 'ID: "' . $backup_details['backup_name'] . '". ';
							$result .= 'Status: "' . $bkp_result['status'] . '". ';
							$result .= $bkp_result['message'];
							send_backup_result_mail($scheduled_backups[$x]['email_confirmation_address'], $result);
						}

						if($bkp_result['status'] != __("Error", WP_BEIFEN_DOMAIN))
						{
							// bkp ok, remove it, save list and stop
							if(count($scheduled_backups)==1)
							{
								update_option('WP_BEIFEN_SCHEDULED_BACKUPS', false);
							}
							else
							{
								$scheduled_backups_temp = array();
								for($y=0; $y<count($scheduled_backups); $y++)
								{
									if($y!=$x)
									{
										$scheduled_backups_temp[] = $scheduled_backups_temp[$x];
									}
								}
								update_option('WP_BEIFEN_SCHEDULED_BACKUPS', $scheduled_backups_temp);
							}
						}
						return $bkp_result;
					}
				}
				else // Frequent backups last
				{
					if($scheduled_backups[$x]['next_backup']<time())
					{
						$backup_details['backup_name'] = $scheduled_backups[$x]['backup_name'];
						$backup_details['backup_type'] = $scheduled_backups[$x]['backup_type'];
						$backup_details['backup_timeout'] = $scheduled_backups[$x]['backup_timeout'];
						$backup_details['compress_backup'] = $scheduled_backups[$x]['compress_backup'];
						if($scheduled_backups[$x]['replace_old_backup']=='Yes')
						{
							delete_old_backup($backup_details['backup_name']);
						}
						else
						{
							$backup_details['backup_name'] = $backup_details['backup_name'] . '-' . date('YmdHi');
						}
						$bkp_result = create_scheduled_backup($backup_details);
						if($scheduled_backups[$x]['send_email_confirmation']=='Yes')
						{
							$result .= 'ID: "' . $backup_details['backup_name'] . '". ';
							$result .= 'Status: "' . $bkp_result['status'] . '". ';
							$result .= $bkp_result['message'];
							send_backup_result_mail($scheduled_backups[$x]['email_confirmation_address'], $result);
						}

						$scheduled_backups[$x]['prev_backup'] = time();
						$scheduled_backups[$x]['next_backup'] = $bkp->Schedule->nextBackup;

						// save and stop
						update_option('WP_BEIFEN_SCHEDULED_BACKUPS', $scheduled_backups);
						return $bkp_result;
					}
				}
			}
		}
		update_option('WP_BEIFEN_SCHEDULED_BACKUPS', $scheduled_backups);
		return "No scheduled backups";
	}
	restore_error_handler();
}

function schedule_error_handler($error_code, $error_message, $error_file, $error_line)
{
	if($error_code!=2 && $error_code!=8 && $error_code!=2048)
	{
		$message = "Bei Fen has encountered an error: " . $error_code .', '. $error_message .', '. $error_file .', '. $error_line;
		wp_mail(get_option('admin_email'),'Backup Error', $message);
	}
}

/*********/
/* Hooks */
/*********/

// Add scheduled backup hook
add_action('beifen_hourly_backup_check', 'beifen_scheduled_backup_check');

// Load textdomain
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain(WP_BEIFEN_DOMAIN, false, $plugin_dir . DS . 'i18n');

// CSS for the plugin
add_action('admin_head', 'wp_beifen_custom_css');

// Activation
register_activation_hook( __FILE__, 'wp_beifen_activate');

// Deactivation
register_deactivation_hook( __FILE__, 'wp_beifen_deactivate');

// Admin Menu
add_action('admin_menu', 'wp_beifen_menu');

// Add Settings Link for the Plugins page
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'wp_beifen_settings_link');

// AJAX Handler
add_action('wp_ajax_wp_beifen_ajax_handler', 'wp_beifen_ajax_handler');

?>