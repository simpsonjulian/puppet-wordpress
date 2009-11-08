<?php
/*
Plugin Name: Bei Fen
Plugin URI: http://www.david-schneider.name/beifen/
Description: A backup plugin for Wordpress. You can create full, files-only, and database-only backups!
Version: 1.3.1
Author: David Schneider
Author URI: http://www.david-schneider.name/
Text Domain: backup
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
	define('WP_BEIFEN_VERSION', '131');
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
	$backup_options['plugin_backup_directory'] = $backup_options['plugin_location'] . 'backups' . DS;
	$backup_options['include_backup_directory'] = false;
	$backup_options['enable_debugging'] = false;
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
	// Add new main menu
	add_menu_page(__("Backup", WP_BEIFEN_DOMAIN), __("Backup", WP_BEIFEN_DOMAIN), 'administrator', __FILE__, 'wp_beifen_display_settings_page');
	// Double entry to avoid double link in the main menu
	add_submenu_page(__FILE__, $menu_settings, $menu_settings, 'administrator', __FILE__, 'wp_beifen_display_settings_page');
	// Add submenu for managing backups
	add_submenu_page(__FILE__, $menu_manage, $menu_manage, 'administrator',  dirname(__FILE__) . DS . 'pages' . DS . 'manage_backups.php');
	// Add submenu for creating backups
	add_submenu_page(__FILE__, $menu_create, $menu_create, 'administrator',  dirname(__FILE__) . DS . 'pages' . DS . 'create_backup.php');
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

/*********/
/* Hooks */
/*********/

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