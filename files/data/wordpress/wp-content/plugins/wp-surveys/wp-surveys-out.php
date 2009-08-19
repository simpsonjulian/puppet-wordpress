<?php
load_plugin_textdomain('wp-surveys',$path='wp-content/plugins/wp-surveys');

global $table_prefix, $wpdb;
$survey_table = $table_prefix . "surveys";
$question_table = $table_prefix . "surveys_questions";
$answer_table = $table_prefix . "surveys_responses";

if(($_SESSION['voted'] == true) || (isset($_COOKIE['voted']))) {
	echo '<br />'.__('Thank you for your assistance in completing our survey!', 'wp-surveys').'<br />';
	$_SESSION['voted'] = false;
	}
else {
	$current_plugins = get_option('active_plugins');
	if ((file_exists(ABSPATH . PLUGINDIR . '/polyglot.php')) && (in_array('polyglot.php', $current_plugins)))
		$polyglot = true;
	$survey_raw = $wpdb->get_results("SELECT * FROM `".$survey_table."` WHERE `survey_open`='1' LIMIT 1;",ARRAY_A);
	if($survey_raw) {
		$survey = $survey_raw[0];
		$questions = $wpdb->get_results("SELECT * FROM `".$question_table."` WHERE `survey_id`='".$survey['survey_id']."';",ARRAY_A);
		if($_SESSION['novote'] == true)
			echo '<br />'.__('Oops, you forgot to vote for one or more of the questions!', 'wp-surveys').'<br />';
		echo '</p></div><h2>';
		if($polyglot) {
			print(stripcslashes(polyglot_filter($survey['survey_name'])).'</h2>');
			print(stripcslashes(polyglot_filter($survey['survey_describe'])).'<div>');
			}
		elseif(!$polyglot) {
			print(stripcslashes($survey['survey_name']).'</h2>');
			print(stripcslashes($survey['survey_describe']).'<div>');
			}
		echo '<br />';
		if($questions) {
			echo '<form name="surveyform" action="http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'" method="post">';
			foreach($questions as $question) {
				//unset($extra_options);
				if($polyglot)
					print("<h3>".stripcslashes(polyglot_filter($question['question_name']))."</h3>\n");
				elseif(!$polyglot)
					print("<h3>".stripcslashes($question['question_name'])."</h3>\n");
				// Define types of questions
				if($question['question_type']=="one-vert") {
					//$forever = $question['question_forever'];  
					//if($forever != '')
						//$extra_options = explode(",", $forever);
					for($q=0;$q<10;$q++) {
						$option="question_option_".$q;
						if($question[$option]!='') {
							print("<br /><input type='radio' name='option[".$question['question_id']."][0]' value='".$question[$option]."'");
							if (($_POST['option'][$question['question_id']][0]) == ($question[$option]))
								print(" checked='checked' />&nbsp;");
							else
								print(" />&nbsp;");
							if($polyglot)
								print(stripcslashes(polyglot_filter($question[$option]))."<br />\n");
							elseif(!$polyglot)
								print(stripcslashes($question[$option])."<br />\n");
							}
						}
					/*if(isset($extra_options)) {
						foreach($extra_options as $option) {
							print("<input type='radio' ");
							print(" name=\"option[".$question['question_id']."][".$q."]\"value=\"".$option."\">&nbsp;");
							print(trim( stripcslashes ($option) )."\n");
							}
						}*/
					echo '<br />';
					}
				elseif($question['question_type']=="one-hori") {
					//$forever=$question['question_forever'];  
					//if($forever != '')
						//$extra_options = explode(",", $forever);
					echo '<br />';
					for($q=0;$q<10;$q++) {
						$option="question_option_".$q;
						if($question[$option]!='') {
							print("<input type='radio' name='option[".$question['question_id']."][0]' value='".$question[$option]."'");
							if (($_POST['option'][$question['question_id']][0]) == ($question[$option]))
								print(" checked='checked' />");
							else print(" />");
							if($polyglot)
								print(stripcslashes(polyglot_filter($question[$option]))."&nbsp;&nbsp;\n");
							elseif(!$polyglot)
								print(stripcslashes($question[$option])."&nbsp;&nbsp;\n");
							}
						}
					/*if(isset($extra_options)) {
						foreach($extra_options as $option) {
							print("<input type='radio' ");
							print(" name=\"option[".$question['question_id']."][".$q."]\" value=\"".$option."\">&nbsp;");
							print(trim( stripcslashes ($option) )."&nbsp;&nbsp;\n");
							}
						}*/
					echo '<br /><br />';
					}
				elseif($question['question_type']=="one-menu") {
					//$forever=$question['question_forever'];  
					//if($forever != '')
						//$extra_options = explode(",", $forever);
					echo '<br /><select name="option['.$question['question_id'].'][0]" style="width:90%">';
					echo '<option value="'.__('No response recorded', 'wp-surveys').'">-- '.__('Select an answer below', 'wp-surveys').'</option>';
					for($q=0;$q<10;$q++) {
						$option="question_option_".$q;
						if($question[$option]!='') {
							print("<option value='".$question[$option]."'");
							if (($_POST['option'][$question['question_id']][0]) == ($question[$option]))
								echo ' selected="selected">';
							else echo '>';
							if($polyglot)
								print(stripcslashes(polyglot_filter($question[$option]))."</option>\n");
							elseif(!$polyglot)
								print(stripcslashes($question[$option])."</option>\n");
							}
						}
					/*if(isset($extra_options)) {
						foreach($extra_options as $option) {
							print("<option value=\"".trim($option)."\" ");
							print(" >");
							print("".trim( stripcslashes ($option) )."</option>\n");
							}
						}*/
					echo '</select><br /><br />';
					}
				/*if($question['question_type']=="mul-vert") {
					$running = 0;
					//$forever = $question['question_forever'];
					//if($forever != '')
						//$extra_options = explode(",", $forever);
					for($q=0;$q<10;$q++) { 
						$option="question_option_".$q;
						if($question[$option]!='') {
							print("<input type='checkbox' ");
							print(" name=\"option[".$question['question_id']."][".$running."]\" value=\"".$question[$option]."\">&nbsp;");
							print(stripcslashes ( $question[$option] )."\n");  
							$running++;
							}
						}
					if(isset($extra_options)) {
						foreach($extra_options as $options) {
							$q++;
							print("<input type='checkbox' ");
							print(" name='option[".$question['question_id']."][".$running."]' value='".trim( $options )."'>&nbsp;");
							print(trim( stripcslashes ($options) )."\n");        
							$running++;
							}
						}
					}
				if($question['question_type']=="mul-hori") {
					$forever=$question['question_forever'];  
					$running=0;
					if($forever != "")
						$extra_options = explode(",", $forever);
					print("<p>\n");
					for($q=0;$q<10;$q++) { 
						$option="question_option_".$q;
						if($question[$option]!='') {
							print("<input type='checkbox' ");
							print(" name='option[".$question['question_id']."][".$running."]' value='".$question[$option]."'>&nbsp;");
							print(stripcslashes ( $question[$option] )."&nbsp;&nbsp;\n");   
							$running++;
							}
						}
					if(isset($extra_options)) {
						foreach($extra_options as $options) {
							print("<input type='checkbox' ");
							print(" name='option[".$question['question_id']."][".$running."]' value='".trim( $options )."'>&nbsp;");
							print(trim( stripcslashes ($options) )."&nbsp;&nbsp;\n");  
							$running++;
							}
						}
					print("\n\n");
					}
				elseif($question['question_type']=="open-onep") {
					print("<div class=\"wpsurveys_text\"><input name='option[".$question['question_id']."][0]' style=\"height:30px\" value='".$filled."'></div>\n");
					}
				elseif($question['question_type']=="open-more") {
					print("<div class=\"wpsurveys_text\"><input name='option[".$question['question_id']."][0]' style=\"height:100px\" value='".$filled."'></div>\n");
					}
				else if($question['question_type']=="open-essa") {
					print("<div class=\"wpsurveys_text\"><input name='option[".$question['question_id']."][0]' style=\"height:200px\" value='".$filled."'></div>\n");
					}
				elseif($question['question_type']=="open-date") {
					print("<div class=\"wpsurveys_text\"><input name='option[".$question['question_id']."][0]' style=\"height:300px\" value='".$filled."'></div>\n");
					}*/
				//else print("Undefined.");
				}
			echo '<input type="hidden" name="survey_id" value="'.$survey['survey_id'].'" /><br />';
			echo '<input type="submit" name="wpsurveys_button" value="'.__('Submit Survey', 'wp-surveys').'" class="surveysubmit" />';
			echo '</form><p>';
			}
		else echo '<br />'.__('This survey has no questions yet.', 'wp-surveys').'<br /><p>';
		}
	else echo '</p><br />'.__('There are no surveys to display.', 'wp-surveys').'<br /><p>';
    } ?>