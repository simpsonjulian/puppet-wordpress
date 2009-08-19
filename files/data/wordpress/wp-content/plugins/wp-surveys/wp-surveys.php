<?php

/*
Plugin Name: WP-Surveys
Plugin URI: http://wordpress.org/extend/plugins/wp-surveys
Description: Create Surveys for WordPress. Inspired by the groundwork of Survey Fly, which it was previously inspired by Instinct Entertainment's Survey Creator Plugin. That give me the base to do these modifications and have a new great plug-in! Thanks out to them for letting me use their code. Some features: Create, modify and retire surveys; Up to ten answer option with horizontal, vertical and dropdown-menu alignment; Backward compatibility with Polyglot plugin; Spanish and Basque localization.

Version: 0.8.2.1
Author: Martin Mozos
*/

/*  
Copyright 2008 Martin Mozos (email: martinmozos AT gmail DOT com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

For a copy of the GNU General Public License, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
http://www.gnu.org/licenses/gpl.html
*/

/*******************************************************************************************************/

if ( file_exists(ABSPATH . PLUGINDIR . '/polyglot.php') ) {
	$current_plugins = get_option('active_plugins');
	if (in_array('polyglot.php', $current_plugins)) {
		require_once(ABSPATH . PLUGINDIR . '/polyglot.php');
		polyglot_init();
		}
	}

load_plugin_textdomain('wp-surveys',$path='wp-content/plugins/wp-surveys'); 

// ----------------------------------------------------------------
register_activation_hook(__FILE__, 'surveys_install');
add_filter('the_content', 'view_surveys');
add_action('admin_menu', 'surveys_admin_page');
//add_filter('admin_head', 'admin_header');
//add_action('wp_head', 'plugin_header');
add_action('init', 'wpsurv_submit_survey');
// ----------------------------------------------------------------

function view_surveys($content = '') {
	global $table_prefix, $wpdb;
	ob_start();
	require_once("wp-surveys-out.php");
	$output = ob_get_contents();
	ob_end_clean();
		return preg_replace("/\[wp_surveys\]/", $output, $content);
	}
 
function surveys_admin_page() {
	if(function_exists('add_menu_page'))
		add_menu_page(__('Surveys', 'wp-surveys'), __('Surveys', 'wp-surveys'), 2, __FILE__, 'manage_surveys');
	}
/*
function admin_header() {
	if(substr(($_REQUEST[page]), 0, 10) == 'wp-surveys') {
		echo	'<style type="text/css">\n
					</style>';
		}
		return;
	}

function plugin_header() { ?>
	<style type="text/css" media="screen">
	</style><?php
   }
*/
function wpsurv_submit_survey() {
	global $table_prefix, $wpdb;
	$survey_table = $table_prefix . "surveys";
	$question_table = $table_prefix . "surveys_questions";
	$answer_table = $table_prefix . "surveys_responses";
	$data_table = $table_prefix . "surveys_data";
	session_start();
	$_SESSION['voted'] = false;
	$_SESSION['novote'] = false;
    if(($_POST['wpsurveys_button'] == __('Submit Survey', 'wp-surveys')) && is_numeric($_POST['survey_id']) && (!isset($_COOKIE['voted']))) {
		$survey_id = $_POST['survey_id'];
		$unique_id = md5(uniqid(rand(), true));
		$questions = $_POST['option'];
		$answer_count = 0;
		if($questions) {
			foreach($questions as $question_id => $score)
				$answer_count++;
			}
		$question_count = $wpdb->get_var("SELECT COUNT(*) FROM `".$question_table."` WHERE `survey_id`=".$survey_id.";");
		if(($answer_count != $question_count) || (!$questions)) {
			$_SESSION['novote'] = true;
			session_write_close();
			return;
			}
		$wpdb->query("INSERT INTO `".$answer_table."` (`response_id`, `survey_id`, `response_unique_id`, `response_datestamp`) VALUES ('', '".$survey_id."', '".$unique_id."', '".gmdate("Y-m-d H:i:s", time())."');");
		$all_survey_questions = $wpdb->get_results("SELECT `question_id` FROM `".$question_table."` WHERE `survey_id`=".$survey_id.";",ARRAY_A);
		for($i = 0; $i < count($all_survey_questions); $i++) {
			unset ($full_options); 
			$option_count = 0; 
			$questionid = $all_survey_questions[$i]['question_id'];
			$roll = count($questions[$questionid]) + 1;
			for($j = 0; $j < $roll; $j++) {
				if(($questions[$questionid][$j] != '') && ($option_count > 0))
					$full_options = $full_options." | ";
				if($questions[$questionid][$j] != '') {
					$full_options = $full_options.htmlspecialchars($questions[$questionid][$j]);
					$option_count++;
					}
				}
			$current_quest = $wpdb->get_results("SELECT * FROM `".$question_table."` WHERE `question_id`=".$questionid.";",ARRAY_A);
			$empty_opts = 0;
			for($k = 0; $k < 10; $k++) {
				$opt = 'question_option_'.$k;
				if ($current_quest[0][$opt] == '')
				$empty_opts++;
				}
			if(($empty_opts == 10) && (is_null($full_options)))
				$full_options=__('No option(s) defined yet', 'wp-surveys');
			elseif(is_null($full_options))
				$full_options=__('No response recorded', 'wp-surveys');
			$responded_survey_id = $wpdb->get_var("SELECT `response_id` FROM `".$answer_table."` WHERE `response_unique_id`='".$unique_id."' LIMIT 1;");
			if(is_numeric($questionid))
				$wpdb->query("INSERT INTO `".$data_table."` (`data_id`, `question_id`, `data_option`, `response_id`) VALUES ('', '$questionid', '$full_options', '$responded_survey_id');");
			}
		$survey_repost = $wpdb->get_var("SELECT `survey_repost` FROM `".$survey_table."` WHERE `survey_id`=".$survey_id." LIMIT 1;");
		$expiry = gmmktime()+(60 * 60 * 24 * $survey_repost);
		//$expiry = gmmktime() + 60; // expires in 60 seconds for testing
		setcookie("voted[survey_id]", $survey_id, $expiry);
		setcookie("voted[unique_id]", $unique_id, $expiry);
		$_SESSION['voted'] = true;
		}
	session_write_close();
	}
 
function manage_surveys() {
	global $table_prefix, $wpdb;
	$survey_table = $table_prefix . "surveys";
	if(isset($_REQUEST["wpsurv_submit"])) {
		require_once('functions.php');
	 	if(($_POST["wpsurv_submit"] == __('Edit', 'wp-surveys')) || ($_POST["wpsurv_submit"] == __('Cancel', 'wp-surveys')) || ($_POST["wpsurv_submit"] == __('Back to Edit Survey', 'wp-surveys')))
			edit($_POST['survey_id']);
	    elseif($_POST["wpsurv_submit"] == __('Update', 'wp-surveys'))
			update($_POST['survey_id']);
		elseif($_POST["wpsurv_submit"] == __('Update Options', 'wp-surveys'))
			update_options($_POST['survey_id'], $_POST['question_id']);
		elseif(($_POST["wpsurv_submit"] == __('Activate', 'wp-surveys')) || ($_POST["wpsurv_submit"] == __('Make Active', 'wp-surveys')))
			activate($_POST['survey_id']);
		elseif($_POST["wpsurv_submit"] == __('Retire', 'wp-surveys'))
			retire($_POST['survey_id']);
		elseif(($_POST["wpsurv_submit"] == __('Edit Options', 'wp-surveys')) || ($_POST["wpsurv_submit"] == __('Add Some Options', 'wp-surveys')) || ($_POST["wpsurv_submit"] == __('Edit or Add more Options', 'wp-surveys')))
			edit_options($_POST['survey_id'], $_POST['question_id']);
		elseif(($_POST["wpsurv_submit"] == __('Add More Questions', 'wp-surveys')) || ($_POST["wpsurv_submit"] == __('Add Some Questions', 'wp-surveys')))
			add_question($_POST['survey_id']);
  		elseif($_POST["wpsurv_submit"] == __('Step 2', 'wp-surveys'))
			step2($_POST['survey_id']);
		elseif($_POST["wpsurv_submit"] == __('Create Question', 'wp-surveys'))
			create_quest($_POST['survey_id']);
  		elseif(($_POST["wpsurv_submit"] == __('Change Survey', 'wp-surveys')) || ($_POST["wpsurv_submit"] == __('Add Survey', 'wp-surveys')))
			survey($_POST['survey_id']);
  		elseif($_POST["wpsurv_submit"] == __('Add This Survey', 'wp-surveys')) 
			add_survey($_POST['survey_id']);
		elseif($_POST["wpsurv_submit"] == __('View Survey Results', 'wp-surveys'))
			results($_POST['survey_id']);
		//elseif($_POST["wpsurv_submit"] == __('View Survey Results in CSV File', 'wp-surveys'))
			//results_CSV($_POST['survey_id']);    			
    	//elseif($_POST["wpsurv_submit"] == __('Delete File', 'wp-surveys'))
			//delete_file($_POST['survey_id']);
		}
	else {
		$current_plugins = get_option('active_plugins');
		if ((file_exists(ABSPATH . PLUGINDIR . '/polyglot.php')) && (in_array('polyglot.php', $current_plugins)))
			$polyglot = true;
		echo '<div class="wrap">';	
		$open_surveys = $wpdb->get_results("SELECT * FROM `".$survey_table."` WHERE `survey_open`='1' LIMIT 1;",ARRAY_A);
		echo '<h2>'.__('Survey Management', 'wp-surveys').'</h2><h3><u>'.__('Active Survey', 'wp-surveys').'</u>:</h3>';		
		if($open_surveys) {
			echo '<table class="widefat" width="100%" cellpadding="4" cellspacing="4">';
			echo '<tr><th align="left">'.__('Title', 'wp-surveys').'</th><th align="left">'.__('Description', 'wp-surveys').'</th><th></th><th></th><th></th></tr>';
			foreach($open_surveys as $survey) {
				echo '<tr class="alternate">';
				if($polyglot)
					echo '<td><b>'.polyglot_filter(stripcslashes($survey['survey_name'])).'</b></td><td>'.polyglot_filter(stripcslashes($survey['survey_describe'])).'</td>';
				elseif(!$polyglot)
					echo '<td><b>'.stripcslashes($survey['survey_name']).'</b></td><td>'.stripcslashes($survey['survey_describe']).'</td>';
				echo '<td class="submit" align="center"><form method="post" action=""><input type="hidden" name="survey_id" value="'.$survey['survey_id'].'" />';
				echo '<input type="submit" name="wpsurv_submit" value="'.__('Edit', 'wp-surveys').'" /></form></td>';
				echo '<td class="submit" align="center"><form method="post" action=""><input type="hidden" name="survey_id" value="'.$survey['survey_id'].'" />';
				echo '<input type="submit" name="wpsurv_submit" value="'.__('Retire', 'wp-surveys').'" /></form></td>';
				echo '<td class="submit" align="center"><form method="post" action=""><input type="hidden" name="survey_id" value="'.$survey['survey_id'].'" />';
				echo '<input type="submit" name="wpsurv_submit" value="'.__('View Survey Results', 'wp-surveys').'" /></form></td>';
				//echo '<td align="center"><form method="post" action=""><input type="hidden" name="survey_id" value="'.$survey['survey_id'].'" />';
				//echo '<input type="submit" name="wpsurv_submit" value="'.__('View Survey Results in CSV File', 'wp-surveys').'" /></form></td>';
				echo '</tr>';
				}
			echo '</table>';
			$next = ++$survey['survey_id'];
			echo '<br /><form method="post" action=""><input type="hidden" name="survey_id" value="'.$next.'" /><input class="button" type="submit" name="wpsurv_submit" value="'.__('Change Survey', 'wp-surveys').'" title="'.__('Current Survey will be saved as Retired', 'wp-surveys').'" /></form>';
			}
		else {
			echo __('There are no open surveys', 'wp-surveys').'.';
			//$last = $wpdb->get_var("SELECT COUNT(`survey_id`) FROM `".$survey_table."`;");
			$last = $wpdb->get_var("SELECT `survey_id` FROM `".$survey_table."` ORDER BY `survey_id` DESC LIMIT 1;");
			$next = ++$last;
			echo '<br /><br /><form method="post" action=""><input type="hidden" name="survey_id" value="'.$next.'" /><input class="button" type="submit" name="wpsurv_submit" value="'.__('Add Survey', 'wp-surveys').'" /></form>';
			}
		$closed_surveys = $wpdb->get_results("SELECT * FROM `".$survey_table."` WHERE `survey_open`='0';",ARRAY_A);
		echo '<h3><u>'.__('Retired Surveys', 'wp-surveys').'</u>:</h3>';
		if($closed_surveys) {
			echo '<table class="widefat" width="100%" cellpadding="4" cellspacing="4">';
			echo '<tr><th align="left">'.__('Title', 'wp-surveys').'</th><th align="left">'.__('Description', 'wp-surveys').'</th><th></th><th></th><th></th></tr>';
			foreach($closed_surveys as $survey) {
				echo '<tr class="alternate">';
				if($polyglot)
					echo '<td><b>'.polyglot_filter(stripcslashes($survey['survey_name'])).'</b></td><td>'.polyglot_filter(stripcslashes($survey['survey_describe'])).'</td>';
				elseif(!$polyglot)
					echo '<td><b>'.stripcslashes($survey['survey_name']).'</b></td><td>'.stripcslashes($survey['survey_describe']).'</td>';
				echo '<td class="submit" align="center"><form method="post" action=""><input type="hidden" name="survey_id" value="'.$survey['survey_id'].'" />';
				echo '<input type="submit" name="wpsurv_submit" value="'.__('Edit', 'wp-surveys').'" /></form></td>';
				echo '<td class="submit" align="center"><form method="post" action=""><input type="hidden" name="survey_id" value="'.$survey['survey_id'].'" />';
				echo '<input type="submit" name="wpsurv_submit" value="'.__('Make Active', 'wp-surveys').'" /></form></td>';
				echo '<td class="submit" align="center"><form method="post" action=""><input type="hidden" name="survey_id" value="'.$survey['survey_id'].'" />';
				echo '<input type="submit" name="wpsurv_submit" value="'.__('View Survey Results', 'wp-surveys').'" /></form></td>';
				//echo '<td align="center"><form method="post" action=""><input type="hidden" name="survey_id" value="'.$survey['survey_id'].'" />';
				//echo '<input type="submit" name="wpsurv_submit" value="'.__('View Survey Results in CSV File', 'wp-surveys').'" /></form></td>';
				echo '</tr>';
				}
				echo '</table>';
			}
		else echo __('There are no retired surveys', 'wp-surveys').'.';
		echo '</div>';
		}
	}
	
function surveys_install() {
	global $table_prefix, $wpdb;
	$survey_table = $table_prefix . "surveys";
	$question_table = $table_prefix . "surveys_questions"; 
	$answer_table = $table_prefix . "surveys_responses";
	$data_table = $table_prefix . "surveys_data";
	if($wpdb->get_var("SHOW TABLES LIKE '$survey_table'") != $survey_table) {
		$sql = "CREATE TABLE `".$survey_table."` (".
		"`survey_id` bigint(16) unsigned NOT NULL auto_increment,".
		"`survey_name` varchar(255) NOT NULL,".
		"`survey_describe` text,".
		"`survey_open` bigint(16) NOT NULL default '0',".
		"`survey_repost` bigint(16) NOT NULL default '90',".
		"`survey_page_chain` text NOT NULL,".
		"`survey_redirect_URL` text NOT NULL,".
		"`survey_share_results` bigint(16) NOT NULL default '0',".
		"PRIMARY KEY (`survey_id`)) DEFAULT CHARACTER SET utf8;";
		$wpdb->query($sql);
		}
	if($wpdb->get_var("SHOW TABLES LIKE '$question_table'") != $question_table) {
		$sql = "CREATE TABLE `".$question_table."` (".
		"`question_id` bigint unsigned NOT NULL auto_increment,".
		"`question_name` text NOT NULL,".
		"`question_type` varchar( 128 ) NOT NULL,".
		"`question_option_0` text,".
		"`question_option_1` text,".
		"`question_option_2` text,".
		"`question_option_3` text,".
		"`question_option_4` text,".
		"`question_option_5` text,".
		"`question_option_6` text,".
		"`question_option_7` text,".
		"`question_option_8` text,".
		"`question_option_9` text,".
		"`question_rows` bigint(16) NOT NULL default '1',".
		"`survey_id` bigint unsigned NOT NULL,".    
		"`question_forever` text NOT NULL,".
		"`question_manditory` bigint(16) NOT NULL default '0',".
		"PRIMARY KEY (`question_id`)) DEFAULT CHARACTER SET utf8;";
		$wpdb->query($sql);
		}
	if($wpdb->get_var("SHOW TABLES LIKE '$answer_table'") != $answer_table) {
		$sql = "CREATE TABLE `$answer_table` (".
		"`response_id` bigint unsigned NOT NULL auto_increment,".
		"`survey_id` bigint unsigned NOT NULL,".
		"`response_unique_id` varchar( 128 ) NOT NULL,".
		"`response_datestamp` datetime NOT NULL default '0000-00-00 00:00:00',".
		"PRIMARY KEY (`response_id`)) DEFAULT CHARACTER SET utf8;";
		$wpdb->query($sql);
		}
	if($wpdb->get_var("SHOW TABLES LIKE '$data_table'") != $data_table) {
		$sql = "CREATE TABLE `$data_table` (".
		"`data_id` bigint unsigned NOT NULL auto_increment,".
		"`question_id` bigint unsigned NOT NULL,".
		"`data_option` text NOT NULL,".
		"`response_id` bigint unsigned NOT NULL,".
		"PRIMARY KEY (`data_id`)) DEFAULT CHARACTER SET utf8;";
		$wpdb->query($sql);
		}
	} ?>
