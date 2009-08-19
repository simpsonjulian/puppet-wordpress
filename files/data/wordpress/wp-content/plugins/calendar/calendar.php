<?php
/*
Plugin Name: Calendar
Plugin URI: http://www.kjowebservices.co.uk
Description: This plugin allows you to display a calendar of all your events and appointments as a page on your site.
Author: Kieran O'Shea
Author URI: http://www.kjowebservices.co.uk
Version: 1.2.1
*/

/*  Copyright 2008  KJO Web Services  (email : sales@kjowebservices.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Define the tables used in Calendar
define('WP_CALENDAR_TABLE', $table_prefix . 'calendar');
define('WP_CALENDAR_CONFIG_TABLE', $table_prefix . 'calendar_config');
define('WP_CALENDAR_CATEGORIES_TABLE', $table_prefix . 'calendar_categories');

// Create a master category for Calendar and its sub-pages
add_action('admin_menu', 'calendar_menu');

// Enable the ability for the calendar to be loaded from pages
add_filter('the_content','calendar_insert');

// Add the function that puts style information in the header
add_action('wp_head', 'calendar_wp_head');

// Add the function that deals with deleted users
add_action('delete_user', 'deal_with_deleted_user');

// Add the widgets if we are using version 2.5
add_action('widgets_init', 'widget_init_calendar_today');
add_action('widgets_init', 'widget_init_calendar_upcoming');

// Before we get on with the functions, we need to define the initial style used for Calendar

// Function to deal with events posted by a user when that user is deleted
function deal_with_deleted_user($id)
{
  global $wpdb;

  // This wouldn't work unless the database was up to date. Lets check.
  check_calendar();

  // Do the query
  $wpdb->get_results("UPDATE ".WP_CALENDAR_TABLE." SET event_author=".$wpdb->get_var("SELECT MIN(ID) FROM ".$wpdb->prefix."users",0,0)." WHERE event_author=".$id);
}

// Function to add the calendar style into the header
function calendar_wp_head()
{
  global $wpdb;

  // If the calendar isn't installed or upgraded this won't work
  check_calendar();

  $styles = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='calendar_style'");
  if (!empty($styles))
    {
      foreach ($styles as $style)
        {
	  echo '<style type="text/css">
<!--
';
          echo $style->config_value.'
';
	  echo '//-->
</style>
';
        }
    }
}

// Function to deal with adding the calendar menus
function calendar_menu() 
{
  global $wpdb;

  // We make use of the Calendar tables so we must have installed Calendar
  check_calendar();

  // Set admin as the only one who can use Calendar for security
  $allowed_group = 'manage_options';

  // Use the database to *potentially* override the above if allowed
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='can_manage_events'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
	{
	  $allowed_group = $config->config_value;
	}
    }

  // Add the admin panel pages for Calendar. Use permissions pulled from above
   if (function_exists('add_menu_page')) 
     {
       add_menu_page(__('Calendar'), __('Calendar'), $allowed_group, __FILE__, 'edit_calendar');
     }
   if (function_exists('add_submenu_page')) 
     {
       add_submenu_page(__FILE__, __('Manage Calendar'), __('Manage Calendar'), $allowed_group, __FILE__, 'edit_calendar');
       add_action( "admin_head", 'calendar_add_javascript' );
       // Note only admin can change calendar options
       add_submenu_page(__FILE__, __('Manage Categories'), __('Manage Categories'), 'manage_options', 'manage-categories', 'manage_categories');
       add_submenu_page(__FILE__, __('Calendar Config'), __('Calendar Options'), 'manage_options', 'calendar-config', 'edit_calendar_config');
     }
}

// Function to add the javascript to the admin header
function calendar_add_javascript()
{ 
  echo '<script type="text/javascript" src="';
  bloginfo('url');
  echo '/wp-content/plugins/calendar/javascript.js"></script>
<script type="text/javascript">document.write(getCalendarStyles());</script>
';
}

// Function to deal with loading the calendar into pages
function calendar_insert($content)
{
  if (preg_match('{CALENDAR}',$content))
    {
      $content = str_replace('{CALENDAR}',calendar(),$content);
    }
  return $content;
}

// Function to check what version of Calendar is installed and install if needed
function check_calendar()
{
  // Checks to make sure Calendar is installed, if not it adds the default
  // database tables and populates them with test data. If it is, then the 
  // version is checked through various means and if it is not up to date 
  // then it is upgraded.

  // Lets see if this is first run and create us a table if it is!
  global $wpdb, $initial_style;

  // All this style info will go into the database on a new install
  // This looks nice in the Kubrick theme
  $initial_style = "    .calnk a:hover {
         background-position:0 0;
         text-decoration:none;  
         color:#000000;
         border-bottom:1px dotted #000000;
         }
    .calnk a:visited {
         text-decoration:none;
         color:#000000;
         border-bottom:1px dotted #000000;
        }
    .calnk a {
        text-decoration:none; 
        color:#000000; 
        border-bottom:1px dotted #000000;
        }
    .calnk a span { 
        display:none; 
        }
    .calnk a:hover span {
        color:#333333; 
        background:#F6F79B; 
        display:block;
        position:absolute; 
        margin-top:1px; 
        padding:5px; 
        width:150px; 
        z-index:100;
        }
     .calendar-table {
        border:none;
        width:100%;
     }
     .calendar-heading {
        height:25px;
        text-align:center;
        border:1px solid #D6DED5;
        background-color:#E4EBE3;
     }
     .calendar-next {
        width:25%;
        text-align:center;
     }
     .calendar-prev {
        width:25%;
        text-align:center;
     }
     .calendar-month {
        width:50%;
        text-align:center;
        font-weight:bold;
     }
     .normal-day-heading {
        text-align:center;
        width:25px;
        height:25px;
        font-size:0.8em;
        border:1px solid #DFE6DE;
        background-color:#EBF2EA;
     }
     .weekend-heading {
        text-align:center;
        width:25px;
        height:25px;
        font-size:0.8em;
        border:1px solid #DFE6DE;
        background-color:#EBF2EA;
        color:#FF0000;
     }
     .day-with-date {
        vertical-align:text-top;
        text-align:left;
        width:60px;
        height:60px;
        border:1px solid #DFE6DE;
     }
     .no-events {

     }
     .day-without-date {
        width:60px;
        height:60px;
        border:1px solid #E9F0E8;
     }
     span.weekend {
        color:#FF0000;
     }
     .current-day {
        vertical-align:text-top;
        text-align:left;
        width:60px;
        height:60px;
        border:1px solid #BFBFBF;
        background-color:#E4EBE3;
     }
     span.event {
        font-size:0.75em;
     }
     .kjo-link {
        font-size:0.75em;
        text-align:center;
     }
     .event-title {
        text-align:center;
        font-weight:bold;
        font-size:1.2em;
     }
     .event-title-break {
        width:96%;
        margin-left:2%;
        margin-right:2%;
        margin-top:5px;
        margin-bottom:5px;
        text-align:center;
        height:1px;
        background-color:#000000;
     }
     .event-content-break {
        width:96%;
        margin-left:2%;
        margin-right:2%;
        margin-top:5px;
        margin-bottom:5px;
        text-align:center;
        height:1px;
        background-color:#000000;
     }
     .calendar-date-switcher {
        height:25px;
        text-align:center;
        border:1px solid #D6DED5;
        background-color:#E4EBE3;
     }
     .calendar-date-switcher form {
        margin:0;
        padding:0;
     }
     .calendar-date-switcher input {
        border:1px #D6DED5 solid;
     }
     .calendar-date-switcher select {
        border:1px #D6DED5 solid;
     }
     .cat-key {
        width:100%;
        margin-top:10px;
        padding:5px;
        border:1px solid #D6DED5;
     }";
     

  // Assume this is not a new install until we prove otherwise
  $new_install = false;
  $vone_point_one_upgrade = false;
  $vone_point_two_beta_upgrade = false;

  $wp_calendar_exists = false;
  $wp_calendar_config_exists = false;
  $wp_calendar_config_version_number_exists = false;

  // Determine the calendar version
  $tables = $wpdb->get_results("show tables;");
  foreach ( $tables as $table )
    {
      foreach ( $table as $value )
        {
	  if ( $value == WP_CALENDAR_TABLE )
	    {
	      $wp_calendar_exists = true;
	    }
	  if ( $value == WP_CALENDAR_CONFIG_TABLE )
            {
              $wp_calendar_config_exists = true;
              
	      // We now try and find the calendar version number
              // This will be a lot easier than finding other stuff 
              // in the future.
	      $version_number = $wpdb->get_var("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='calendar_version'"); 
	      if ($version_number == "1.2")
		{
		  $wp_calendar_config_version_number_exists = true;
		}
            }
        }
    }

  if ($wp_calendar_exists == false && $wp_calendar_config_exists == false)
    {
      $new_install = true;
    }
  else if ($wp_calendar_exists == true && $wp_calendar_config_exists == false)
    {
      $vone_point_one_upgrade = true;
    }
  else if ($wp_calendar_exists == true && $wp_calendar_config_exists == true && $wp_calendar_config_version_number_exists == false)
    {
      $vone_point_two_beta_upgrade = true;
    }

  // Now we've determined what the current install is or isn't 
  // we perform operations according to the findings
  if ( $new_install == true )
    {
      $sql = "CREATE TABLE " . WP_CALENDAR_TABLE . " (
                                event_id INT(11) NOT NULL AUTO_INCREMENT ,
                                event_begin DATE NOT NULL ,
                                event_end DATE NOT NULL ,
                                event_title VARCHAR(30) NOT NULL ,
                                event_desc TEXT NOT NULL ,
                                event_time TIME ,
                                event_recur CHAR(1) ,
                                event_repeats INT(3) ,
                                event_author BIGINT(20) UNSIGNED,
                                PRIMARY KEY (event_id)
                        )";
      $wpdb->get_results($sql);
      $sql = "CREATE TABLE " . WP_CALENDAR_CONFIG_TABLE . " (
                                config_item VARCHAR(30) NOT NULL ,
                                config_value TEXT NOT NULL ,
                                PRIMARY KEY (config_item)
                        )";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='can_manage_events', config_value='edit_posts'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='calendar_style', config_value='".$initial_style."'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_author', config_value='false'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_jump', config_value='false'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_todays', config_value='true'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_upcoming', config_value='true'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_upcoming_days', config_value=7";
      $wpdb->get_results($sql);

      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='calendar_version', config_value='1.2'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='enable_categories', config_value='false'";
      $wpdb->get_results($sql);
      $sql = "ALTER TABLE ".WP_CALENDAR_TABLE." ADD COLUMN event_category BIGINT(20) UNSIGNED";
      $wpdb->get_results($sql);
      $sql = "UPDATE " . WP_CALENDAR_TABLE . " SET event_category=1";
      $wpdb->get_results($sql);
      $sql = "ALTER TABLE ".WP_CALENDAR_TABLE." ADD COLUMN event_link TEXT";
      $wpdb->get_results($sql);
      $sql = "UPDATE " . WP_CALENDAR_TABLE . " SET event_link=''";
      $wpdb->get_results($sql);
      $sql = "CREATE TABLE " . WP_CALENDAR_CATEGORIES_TABLE . " ( 
                                category_id INT(11) NOT NULL AUTO_INCREMENT, 
                                category_name VARCHAR(30) NOT NULL , 
                                category_colour VARCHAR(30) NOT NULL , 
                                PRIMARY KEY (category_id) 
                             )";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO " . WP_CALENDAR_CATEGORIES_TABLE . " SET category_id=0, category_name='General', category_colour='#F6F79B'";
      $wpdb->get_results($sql);
    }
  else if ($vone_point_one_upgrade == true)
    {
      $sql = "ALTER TABLE ".WP_CALENDAR_TABLE." ADD COLUMN event_author BIGINT(20) UNSIGNED";
      $wpdb->get_results($sql);
      $sql = "UPDATE ".WP_CALENDAR_TABLE." SET event_author=".$wpdb->get_var("SELECT MIN(ID) FROM ".$wpdb->prefix."users",0,0);
      $wpdb->get_results($sql);
      $sql = "ALTER TABLE ".WP_CALENDAR_TABLE." MODIFY event_desc TEXT NOT NULL";
      $wpdb->get_results($sql);
      $sql = "CREATE TABLE " . WP_CALENDAR_CONFIG_TABLE . " (
                                config_item VARCHAR(30) NOT NULL ,
                                config_value TEXT NOT NULL ,
                                PRIMARY KEY (config_item)
                        )";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='can_manage_events', config_value='edit_posts'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='calendar_style', config_value='".$initial_style."'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_author', config_value='false'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_jump', config_value='false'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_todays', config_value='true'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_upcoming', config_value='true'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='display_upcoming_days', config_value=7";
      $wpdb->get_results($sql);

      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='calendar_version', config_value='1.2'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='enable_categories', config_value='false'";
      $wpdb->get_results($sql);
      $sql = "ALTER TABLE ".WP_CALENDAR_TABLE." ADD COLUMN event_category BIGINT(20) UNSIGNED";
      $wpdb->get_results($sql);
      $sql = "UPDATE " . WP_CALENDAR_TABLE . " SET event_category=1";
      $wpdb->get_results($sql);
      $sql = "ALTER TABLE ".WP_CALENDAR_TABLE." ADD COLUMN event_link TEXT";
      $wpdb->get_results($sql);
      $sql = "UPDATE " . WP_CALENDAR_TABLE . " SET event_link=''";
      $wpdb->get_results($sql);
      $sql = "CREATE TABLE " . WP_CALENDAR_CATEGORIES_TABLE . " ( 
                                category_id INT(11) NOT NULL AUTO_INCREMENT, 
                                category_name VARCHAR(30) NOT NULL , 
                                category_colour VARCHAR(30) NOT NULL , 
                                PRIMARY KEY (category_id) 
                              )";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO " . WP_CALENDAR_CATEGORIES_TABLE . " SET category_id=0, category_name='General', category_colour='#F6F79B'";
      $wpdb->get_results($sql);
    }
  else if ($vone_point_two_beta_upgrade == true)
    {
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='calendar_version', config_value='1.2'";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO ".WP_CALENDAR_CONFIG_TABLE." SET config_item='enable_categories', config_value='false'";
      $wpdb->get_results($sql);
      $sql = "ALTER TABLE ".WP_CALENDAR_TABLE." ADD COLUMN event_category BIGINT(20) UNSIGNED";
      $wpdb->get_results($sql);
      $sql = "UPDATE " . WP_CALENDAR_TABLE . " SET event_category=1";
      $wpdb->get_results($sql);
      $sql = "ALTER TABLE ".WP_CALENDAR_TABLE." ADD COLUMN event_link TEXT";
      $wpdb->get_results($sql);
      $sql = "UPDATE " . WP_CALENDAR_TABLE . " SET event_link=''";
      $wpdb->get_results($sql);
      $sql = "CREATE TABLE " . WP_CALENDAR_CATEGORIES_TABLE . " (
                                category_id INT(11) NOT NULL AUTO_INCREMENT, 
                                category_name VARCHAR(30) NOT NULL , 
                                category_colour VARCHAR(30) NOT NULL , 
                                PRIMARY KEY (category_id) 
                             )";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO " . WP_CALENDAR_CATEGORIES_TABLE . " SET category_id=1, category_name='General', category_colour='#F6F79B'";
      $wpdb->get_results($sql);
      $sql = "UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value='".$initial_style."' WHERE config_item='calendar_style'";
      $wpdb->get_results($sql);
    }
}

// Used on the manage events admin page to display a list of events
function wp_events_display_list()
{
	global $wpdb;
	
	$events = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_TABLE . " ORDER BY event_begin DESC");
	
	if ( !empty($events) )
	{
		?>
		<table width="100%" cellpadding="3" cellspacing="3">
			<tr>
				<th scope="col"><?php _e('ID') ?></th>
				<th scope="col"><?php _e('Title') ?></th>
				<th scope="col"><?php _e('Description') ?></th>
				<th scope="col"><?php _e('Start Date') ?></th>
				<th scope="col"><?php _e('End Date') ?></th>
				<th scope="col"><?php _e('Recurs') ?></th>
				<th scope="col"><?php _e('Repeats') ?></th>
		                <th scope="col"><?php _e('Author') ?></th>
		                <th scope="col"><?php _e('Category') ?></th>
				<th scope="col"><?php _e('Edit') ?></th>
				<th scope="col"><?php _e('Delete') ?></th>
			</tr>
		<?php
		$class = '';
		foreach ( $events as $event )
		{
			$class = ($class == 'alternate') ? '' : 'alternate';
			?>
			<tr class="<?php echo $class; ?>">
				<th scope="row"><?php echo $event->event_id; ?></th>
				<td><?php echo $event->event_title; ?></td>
				<td><?php echo $event->event_desc; ?></td>
				<td><?php echo $event->event_begin; ?></td>
				<td><?php echo $event->event_end; ?></td>
				<td>
				<?php 
					// Interpret the DB values into something human readable
					if ($event->event_recur == 'S') { echo 'Never'; } 
					else if ($event->event_recur == 'W') { echo 'Weekly'; }
					else if ($event->event_recur == 'M') { echo 'Monthly'; }
					else if ($event->event_recur == 'Y') { echo 'Yearly'; }
				?>
				</td>
				<td>
				<?php
				        // Interpret the DB values into something human readable
					if ($event->event_recur == 'S') { echo 'N/A'; }
					else if ($event->event_repeats == 0) { echo 'Forever'; }
					else if ($event->event_repeats > 0) { echo $event->event_repeats.' Times'; }					
				?>
				</td>
				<td><?php $e = get_userdata($event->event_author); echo $e->display_name; ?></td>
                                <?php
				$sql = "SELECT * FROM " . WP_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".$event->event_category;
                                $this_cat = $wpdb->get_row($sql);
                                ?>
				<td style="background-color:<?php echo $this_cat->category_colour;?>;"><?php echo $this_cat->category_name; ?></td>
				<?php unset($this_cat); ?>
				<td><a href="<?php echo $_SERVER['REQUEST_URI']  ?>&amp;action=edit&amp;event_id=<?php echo $event->event_id;?>" class='edit'><?php echo __('Edit'); ?></a></td>
				<td><a href="<?php echo $_SERVER['REQUEST_URI'] ?>&amp;action=delete&amp;event_id=<?php echo $event->event_id;?>" class="delete" onclick="return confirm('Are you sure you want to delete this event?')"><?php echo __('Delete'); ?></a></td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
	}
	else
	{
		?>
		<p><?php _e("There are no events in the database!")	?></p>
		<?php	
	}
}


// The event edit form for the manage events admin page
function wp_events_edit_form($mode='add', $event_id=false)
{
	global $wpdb;
	$data = false;
	
	if ( $event_id !== false )
	{
		if ( intval($event_id) != $event_id )
		{
			echo "<div class=\"error\"><p>Bad Monkey! No banana!</p></div>";
			return;
		}
		else
		{
			$data = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_TABLE . " WHERE event_id='" . mysql_escape_string($event_id) . "' LIMIT 1");
			if ( empty($data) )
			{
				echo "<div class=\"error\"><p>An event with that ID couldn't be found</p></div>";
				return;
			}
			$data = $data[0];
		}	
	}
	
	?>
        <div id="pop_up_cal" style="position:absolute;margin-left:150px;visibility:hidden;background-color:white;layer-background-color:white;"></div>
	<form name="quoteform" id="quoteform" class="wrap" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="action" value="<?php echo $mode; ?>">
		<input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
	
		<div id="item_manager">
			<div style="float: left; width: 98%; clear: both;" class="top">
				<!-- List URL -->				
				<fieldset class="small"><legend><?php _e('Event Title'); ?></legend>
					<input type="text" name="event_title" class="input" size="40" maxlength="30"
					value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_title); ?>" />
				</fieldset>

				<fieldset class="small"><legend><?php _e('Event Description'); ?></legend>
					<textarea name="event_desc" class="input" rows="5" cols="50"><?php if ( !empty($data) ) echo htmlspecialchars($data->event_desc); ?></textarea>
				</fieldset>

				<fieldset class="small"><legend><?php _e('Event Category'); ?></legend>
					 <select name="event_category">
					     <?php
					         // Grab all the categories and list them
						 $sql = "SELECT * FROM " . WP_CALENDAR_CATEGORIES_TABLE;
	                                         $cats = $wpdb->get_results($sql);
                                                 foreach($cats as $cat)
						   {
						     echo '<option value="'.$cat->category_id.'"';
                                                     if (!empty($data))
						       {
							 if ($data->event_category == $cat->category_id)
							   {
							     echo 'selected="selected"';
							   }
						       }
                                                     echo '>'.$cat->category_name.'</option>
';
						   }
                                             ?>
                                         </select>
                                </fieldset>

				<fieldset class="small"><legend><?php _e('Event Link (Optional)'); ?></legend>
                                        <input type="text" name="event_link" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_link); ?>" />
                                </fieldset>

				<fieldset class="small"><legend><?php _e('Start Date'); ?></legend>
                                        <script type="text/javascript">
					var cal_begin = new CalendarPopup('pop_up_cal');
					</script>
					<input type="text" name="event_begin" class="input" size=12
					value="<?php 
					if ( !empty($data) ) 
					{
						echo htmlspecialchars($data->event_begin);
					}
					else
					{
						echo date("Y-m-d");
					} 
					?>" /> <a href="#" onClick="cal_begin.select(document.forms['quoteform'].event_begin,'event_begin_anchor','yyyy-MM-dd'); return false;" name="event_begin_anchor" id="event_begin_anchor">Select Date</a>
				</fieldset>

				<fieldset class="small"><legend><?php _e('End Date'); ?></legend>
                                        <script type="text/javascript">
					function check_and_print() {
					var cal_end = new CalendarPopup('pop_up_cal');
					var newDate = new Date();
					newDate.setFullYear(document.forms['quoteform'].event_begin.value.split('-')[0],document.forms['quoteform'].event_begin.value.split('-')[1]-1,document.forms['quoteform'].event_begin.value.split('-')[2]);
					newDate.setDate(newDate.getDate()-1);
                                        cal_end.addDisabledDates(null, formatDate(newDate, "yyyy-MM-dd"));
                                        cal_end.select(document.forms['quoteform'].event_end,'event_end_anchor','yyyy-MM-dd');
					}
                                        </script>
					<input type="text" name="event_end" class="input" size=12
					value="<?php 
					if ( !empty($data) ) 
					{
						echo htmlspecialchars($data->event_end);
					}
					else
					{
						echo date("Y-m-d");
					}
					?>" />  <a href="#" onClick="check_and_print(); return false;" name="event_end_anchor" id="event_end_anchor">Select Date</a>
				</fieldset>

				<fieldset class="small"><legend><?php _e('Time (hh:mm)(optional, set blank if not required)'); ?></legend>
					<input type="text" name="event_time" class="input" size=12
					value="<?php 
					if ( !empty($data) ) 
					{
						if ($data->event_time == "00:00:00")
						{
							echo '';
						}
						else
						{
							echo date("H:i",strtotime(htmlspecialchars($data->event_time)));
						}
					}
					else
					{
						echo date("H:i");
					}
					?>" /> <?php _e('Current time difference from GMT is '); echo get_option('gmt_offset'); _e(' hour(s)'); ?>
				</fieldset>

				<fieldset class="small"><legend><?php _e('Recurring Events'); ?></legend>
					<?php
					if ($data->event_repeats != NULL)
					{
						$repeats = $data->event_repeats;
					}
					else
					{
						$repeats = 0;
					}

					if ($data->event_recur == "S")
					{
						$selected_s = 'selected="selected"';
					}
					else if ($data->event_recur == "W")
					{
						$selected_w = 'selected="selected"';
					}
					else if ($data->event_recur == "M")
					{
						$selected_m = 'selected="selected"';
					}
					else if ($data->event_recur == "Y")
					{
						$selected_y = 'selected="selected"';
					}
					?>
					Repeats for 
					<input type="text" name="event_repeats" class="input" size="1" value="<?php echo $repeats; ?>" /> 
					<select name="event_recur" class="input">
						<option class="input" <?php echo $selected_s; ?> value="S">None</option>
						<option class="input" <?php echo $selected_w; ?> value="W">Weeks</option>
						<option class="input" <?php echo $selected_m; ?> value="M">Months</option>
						<option class="input" <?php echo $selected_y; ?> value="Y">Years</option>
					</select><br />
					Entering 0 means forever. Where the recurrance interval <br />
					is left at none, the event will not reoccur.
				</fieldset>
				<br />
				<input type="submit" name="save" class="button bold" value="Save &raquo;" />
			</div>
			<div style="clear:both; height:1px;">&nbsp;</div>
		</div>
	</form>
	<?php
}

// The actual function called to render the manage events page and 
// to deal with posts
function edit_calendar()
{
    global $current_user, $wpdb;
  ?>
  <style type="text/css">
<!--
	.error {
	  background: lightcoral;
	  border: 1px solid #e64f69;
	  margin: 1em 5% 10px;
	  padding: 0 1em 0 1em;
	}

	.center { 
	  text-align: center;	
	}
	.right { text-align: right;	
	}
        .left { 
	  text-align: left;		
	}
	.top { 
	  vertical-align: top;	
	}
	.bold { 
	  font-weight: bold; 
	}
	.private { 
	  color: #e64f69;		
	}
//-->
</style>

<?php

// First some quick cleaning up 
$edit = $create = $save = $delete = false;

// Make sure we are collecting the variables we need to select years and months
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$event_id = !empty($_REQUEST['event_id']) ? $_REQUEST['event_id'] : '';


// Lets see if this is first run and create us a table if it is!
check_calendar();

// Deal with adding an event to the database
if ( $action == 'add' )
{
	$title = !empty($_REQUEST['event_title']) ? $_REQUEST['event_title'] : '';
	$desc = !empty($_REQUEST['event_desc']) ? $_REQUEST['event_desc'] : '';
	$begin = !empty($_REQUEST['event_begin']) ? $_REQUEST['event_begin'] : '';
	$end = !empty($_REQUEST['event_end']) ? $_REQUEST['event_end'] : '';
	$time = !empty($_REQUEST['event_time']) ? $_REQUEST['event_time'] : '';
	$recur = !empty($_REQUEST['event_recur']) ? $_REQUEST['event_recur'] : '';
	$repeats = !empty($_REQUEST['event_repeats']) ? $_REQUEST['event_repeats'] : '';
	$category = !empty($_REQUEST['event_category']) ? $_REQUEST['event_category'] : '';
        $linky = !empty($_REQUEST['event_link']) ? $_REQUEST['event_link'] : '';

	// Deal with the fools who have left magic quotes turned on
	if ( ini_get('magic_quotes_gpc') )
	{
		$title = stripslashes($title);
		$desc = stripslashes($desc);
		$begin = stripslashes($begin);
		$end = stripslashes($end);
		$time = stripslashes($time);
		$recur = stripslashes($recur);
		$repeats = stripslashes($repeats);
                $category = stripslashes($category);
                $linky = stripslashes($linky);	
	}	

	$sql = "INSERT INTO " . WP_CALENDAR_TABLE . " SET event_title='" . mysql_escape_string($title)
	     . "', event_desc='" . mysql_escape_string($desc) . "', event_begin='" . mysql_escape_string($begin) 
             . "', event_end='" . mysql_escape_string($end) . "', event_time='" . mysql_escape_string($time) . "', event_recur='" . mysql_escape_string($recur) . "', event_repeats='" . mysql_escape_string($repeats) . "', event_author=".$current_user->ID.", event_category=".mysql_escape_string($category).", event_link='".mysql_escape_string($linky)."'";
	     
	$wpdb->get_results($sql);
	
	$sql = "SELECT event_id FROM " . WP_CALENDAR_TABLE . " WHERE event_title='" . mysql_escape_string($title) . "'"
		. " AND event_desc='" . mysql_escape_string($desc) . "' AND event_begin='" . mysql_escape_string($begin) . "' AND event_end='" . mysql_escape_string($end) . "' AND event_recur='" . mysql_escape_string($recur) . "' AND event_repeats='" . mysql_escape_string($repeats) . "' LIMIT 1";
	$result = $wpdb->get_results($sql);
	
	if ( empty($result) || empty($result[0]->event_id) )
	{
		?>
		<div class="error"><p><strong>Error:</strong> For some bizare reason your event was not added. Why not try again?</p></div>
		<?php
	}
	else
	{
		?>
		<div class="updated"><p>Event added. It will now show in your calendar.</p></div>
		<?php
	}
}
// Permit saving of events that have been edited
elseif ( $action == 'edit_save' )
{
	$title = !empty($_REQUEST['event_title']) ? $_REQUEST['event_title'] : '';
	$desc = !empty($_REQUEST['event_desc']) ? $_REQUEST['event_desc'] : '';
	$begin = !empty($_REQUEST['event_begin']) ? $_REQUEST['event_begin'] : '';
	$end = !empty($_REQUEST['event_end']) ? $_REQUEST['event_end'] : '';
	$time = !empty($_REQUEST['event_time']) ? $_REQUEST['event_time'] : '';
	$recur = !empty($_REQUEST['event_recur']) ? $_REQUEST['event_recur'] : '';
	$repeats = !empty($_REQUEST['event_repeats']) ? $_REQUEST['event_repeats'] : '';
	$category = !empty($_REQUEST['event_category']) ? $_REQUEST['event_category'] : '';
        $linky = !empty($_REQUEST['event_link']) ? $_REQUEST['event_link'] : '';

	// Deal with the fools who have left magic quotes turned on
	if ( ini_get('magic_quotes_gpc') )
	{
		$title = stripslashes($title);
		$desc = stripslashes($desc);
		$begin = stripslashes($begin);
		$end = stripslashes($end);
		$time = stripslashes($time);
		$recur = stripslashes($recur);
		$repeats = stripslashes($repeats);
                $category = stripslashes($category);
                $linky = stripslashes($linky);	
	}
	
	if ( empty($event_id) )
	{
		?>
		<div class="error"><p><strong>Failure:</strong> You can't update an event if you haven't submitted an event id</p></div>
		<?php		
	}
	else
	{
		$sql = "UPDATE " . WP_CALENDAR_TABLE . " SET event_title='" . mysql_escape_string($title)
		     . "', event_desc='" . mysql_escape_string($desc) . "', event_begin='" . mysql_escape_string($begin) 
                     . "', event_end='" . mysql_escape_string($end) . "', event_time='" . mysql_escape_string($time) . "', event_recur='" . mysql_escape_string($recur) . "', event_repeats='" . mysql_escape_string($repeats) . "', event_author=".$current_user->ID . ", event_category=".mysql_escape_string($category).", event_link='".mysql_escape_string($linky)."' WHERE event_id='" . mysql_escape_string($event_id) . "'";
		     
		$wpdb->get_results($sql);
		
		$sql = "SELECT event_id FROM " . WP_CALENDAR_TABLE . " WHERE event_title='" . mysql_escape_string($title) . "'"
		     . " AND event_desc='" . mysql_escape_string($desc) . "' AND event_begin='" . mysql_escape_string($begin) . "' AND event_end='" . mysql_escape_string($end) . "' AND event_recur='" . mysql_escape_string($recur) . "' AND event_repeats='" . mysql_escape_string($repeats) . "' LIMIT 1";
		$result = $wpdb->get_results($sql);
		
		if ( empty($result) || empty($result[0]->event_id) )
		{
			?>
			<div class="error"><p><strong>Failure:</strong> For some reason the event didnt update. Why not try again? </p></div>
			<?php
		}
		else
		{
			?>
			<div class="updated"><p>Event updated successfully</p></div>
			<?php
		}		
	}
}
// Deal with deleting an event from the database
elseif ( $action == 'delete' )
{
	if ( empty($event_id) )
	{
		?>
		<div class="error"><p><strong>Error:</strong> Good Lord you gave me nothing to delete, nothing I tell you!</p></div>
		<?php			
	}
	else
	{
		$sql = "DELETE FROM " . WP_CALENDAR_TABLE . " WHERE event_id='" . mysql_escape_string($event_id) . "'";
		$wpdb->get_results($sql);
		
		$sql = "SELECT event_id FROM " . WP_CALENDAR_TABLE . " WHERE event_id='" . mysql_escape_string($event_id) . "'";
		$result = $wpdb->get_results($sql);
		
		if ( empty($result) || empty($result[0]->event_id) )
		{
			?>
			<div class="updated"><p>Event deleted successfully</p></div>
			<?php
		}
		else
		{
			?>
			<div class="error"><p><strong>Error:</strong> For some bizare reason the event could not be deleted. Why not try again?</p></div>
			<?php

		}		
	}
}

// Now follows a little bit of code that pulls in the main 
// components of this page; the edit form and the list of events
?>

<div class="wrap">
	<?php
	if ( $action == 'edit' )
	{
		?>
		<h2><?php _e('Edit Event'); ?></h2>
		<?php
		if ( empty($event_id) )
		{
			echo "<div class=\"error\"><p>Good lord you didn't provide an event id to edit, what were you thinking?</p></div>";
		}
		else
		{
			wp_events_edit_form('edit_save', $event_id);
		}	
	}
	else
	{
		?>
		<h2><?php _e('Add Event'); ?></h2>
		<?php wp_events_edit_form(); ?>
	
		<h2><?php _e('Manage Events'); ?></h2>
		<?php
			wp_events_display_list();
	}
	?>
</div>

<?php
 
}

// Display the admin configuration page
function edit_calendar_config()
{
  global $wpdb, $initial_style;

  // We can't use this page unless Calendar is installed/upgraded
  check_calendar();

  if (isset($_POST['permissions']) && isset($_POST['style']))
    {
      if ($_POST['permissions'] == 'subscriber') { $new_perms = 'read'; }
      else if ($_POST['permissions'] == 'contributor') { $new_perms = 'edit_posts'; }
      else if ($_POST['permissions'] == 'author') { $new_perms = 'publish_posts'; }
      else if ($_POST['permissions'] == 'editor') { $new_perms = 'moderate_comments'; }
      else if ($_POST['permissions'] == 'admin') { $new_perms = 'manage_options'; }
      else { $new_perms = 'manage_options'; }

      $calendar_style = mysql_escape_string($_POST['style']);
      $display_upcoming_days = mysql_escape_string($_POST['display_upcoming_days']);

      if (mysql_escape_string($_POST['display_author']) == 'on')
	{
	  $disp_author = 'true';
	}
      else
	{
	  $disp_author = 'false';
	}

      if (mysql_escape_string($_POST['display_jump']) == 'on')
        {
          $disp_jump = 'true';
        }
      else
        {
          $disp_jump = 'false';
        }

      if (mysql_escape_string($_POST['display_todays']) == 'on')
        {
          $disp_todays = 'true';
        }
      else
        {
          $disp_todays = 'false';
        }

      if (mysql_escape_string($_POST['display_upcoming']) == 'on')
        {
          $disp_upcoming = 'true';
        }
      else
        {
          $disp_upcoming = 'false';
        }

      if (mysql_escape_string($_POST['enable_categories']) == 'on')
        {
          $enable_categories = 'true';
        }
      else
        {
	  $enable_categories = 'false';
        }

      $wpdb->get_results("UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value = '".$new_perms."' WHERE config_item='can_manage_events'");
      $wpdb->get_results("UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value = '".$calendar_style."' WHERE config_item='calendar_style'");
      $wpdb->get_results("UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value = '".$disp_author."' WHERE config_item='display_author'");
      $wpdb->get_results("UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value = '".$disp_jump."' WHERE config_item='display_jump'");
      $wpdb->get_results("UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value = '".$disp_todays."' WHERE config_item='display_todays'");
      $wpdb->get_results("UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value = '".$disp_upcoming."' WHERE config_item='display_upcoming'");
      $wpdb->get_results("UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value = '".$display_upcoming_days."' WHERE config_item='display_upcoming_days'");
      $wpdb->get_results("UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value = '".$enable_categories."' WHERE config_item='enable_categories'");

      // Check to see if we are replacing the original style
      if (mysql_escape_string($_POST['reset_styles']) == 'on')
        {
          $wpdb->get_results("UPDATE " . WP_CALENDAR_CONFIG_TABLE . " SET config_value = '".$initial_style."' WHERE config_item='calendar_style'");
        }

      echo "<div class=\"updated\"><p><strong>Settings saved.</strong></p></div>";
    }

  // Pull the values out of the database that we need for the form
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='can_manage_events'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          $allowed_group = $config->config_value;
        }
    }

  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='calendar_style'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          $calendar_style = $config->config_value;
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='display_author'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
	  if ($config->config_value == 'true')
	    {
	      $yes_disp_author = 'selected="selected"';
	    }
	  else
	    {
	      $no_disp_author = 'selected="selected"';
	    }
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='display_jump'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          if ($config->config_value == 'true')
            {
              $yes_disp_jump = 'selected="selected"';
            }
          else
            {
              $no_disp_jump = 'selected="selected"';
            }
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='display_todays'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          if ($config->config_value == 'true')
            {
              $yes_disp_todays = 'selected="selected"';
            }
          else
            {
              $no_disp_todays = 'selected="selected"';
            }
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='display_upcoming'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          if ($config->config_value == 'true')
            {
              $yes_disp_upcoming = 'selected="selected"';
            }
          else
            {
              $no_disp_upcoming = 'selected="selected"';
            }
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='display_upcoming_days'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          $upcoming_days = $config->config_value;
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_CALENDAR_CONFIG_TABLE . " WHERE config_item='enable_categories'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          if ($config->config_value == 'true')
            {
              $yes_enable_categories = 'selected="selected"';
            }
          else
            {
              $no_enable_categories = 'selected="selected"';
            }
        }
    }
  if ($allowed_group == 'read') { $subscriber_selected='selected="selected"';}
  else if ($allowed_group == 'edit_posts') { $contributor_selected='selected="selected"';}
  else if ($allowed_group == 'publish_posts') { $author_selected='selected="selected"';}
  else if ($allowed_group == 'moderate_comments') { $editor_selected='selected="selected"';}
  else if ($allowed_group == 'manage_options') { $admin_selected='selected="selected"';}

  // Now we render the form
  ?>
  <style type="text/css">
  <!--
        .error {
	  background: lightcoral;
	  border: 1px solid #e64f69;
	  margin: 1em 5% 10px;
	  padding: 0 1em 0 1em;
	}

        .center { 
	  text-align: center; 
	}
        .right { 
	  text-align: right; 
	}
        .left { 
	  text-align: left; 
	}
        .top { 
	  vertical-align: top; 
	}
        .bold { 
	  font-weight: bold; 
	}
        .private { 
	  color: #e64f69; 
	}
  //-->                                                                                                                                                        
  </style>

  <div class="wrap">
  <h2><?php _e('Calendar Options'); ?></h2>
  <form name="quoteform" id="quoteform" class="wrap" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                <div id="item_manager">
                        <div style="float: left; width: 98%; clear: both;" class="top">
                                <fieldset class="small"><legend><?php _e('Choose the lowest user group that may manage events'); ?></legend>
				        <select name="permissions">
				            <option value="subscriber"<?php echo $subscriber_seletced ?>><?php _e('Subscriber')?></option>
				            <option value="contributor" <?php echo $contributor_selected ?>><?php _e('Contributor')?></option>
				            <option value="author" <?php echo $author_selected ?>><?php _e('Author')?></option>
				            <option value="editor" <?php echo $editor_selected ?>><?php _e('Editor')?></option>
				            <option value="admin" <?php echo $admin_selected ?>><?php _e('Administrator')?></option>
				        </select>
                                </fieldset>
				<fieldset class="small"><legend><?php _e('Do you want to display the author name on events?'); ?></legend>
                                    <select name="display_author">
                                        <option value="on" <?php echo $yes_disp_author ?>><?php _e('Yes') ?></option>
                                        <option value="off" <?php echo $no_disp_author ?>><?php _e('No') ?></option>
                                    </select>
                                </fieldset>
				<fieldset class="small"><legend><?php _e('Display a jumpbox for changing month and year quickly?'); ?></legend>
                                    <select name="display_jump">
                                         <option value="on" <?php echo $yes_disp_jump ?>><?php _e('Yes') ?></option>
                                         <option value="off" <?php echo $no_disp_jump ?>><?php _e('No') ?></option>
                                    </select>
                                </fieldset>
				<fieldset class="small"><legend><?php _e('Display todays events?'); ?></legend>
                                    <select name="display_todays">
						<option value="on" <?php echo $yes_disp_todays ?>><?php _e('Yes') ?></option>
						<option value="off" <?php echo $no_disp_todays ?>><?php _e('No') ?></option>
                                    </select>
                                </fieldset>
				<fieldset class="small"><legend><?php _e('Display upcoming events? If yes, state for how many days into the future'); ?></legend>
                                    <select name="display_upcoming">
						<option value="on" <?php echo $yes_disp_upcoming ?>><?php _e('Yes') ?></option>
						<option value="off" <?php echo $no_disp_upcoming ?>><?php _e('No') ?></option>
                                    </select>
				    for <input type="text" name="display_upcoming_days" value="<?php echo $upcoming_days ?>" size="1" maxlength="2" /> days into the future
                                </fieldset>

				<fieldset class="small"><legend><?php _e('Enable event categories?'); ?></legend>
                                    <select name="enable_categories">
				                <option value="on" <?php echo $yes_enable_categories ?>><?php _e('Yes') ?></option>
						<option value="off" <?php echo $no_enable_categories ?>><?php _e('No') ?></option>
                                    </select>
                                </fieldset>

				<fieldset class="small"><legend><?php _e('Configure the stylesheet for Calendar'); ?></legend>
				    <textarea name="style" rows="10" cols="60" tabindex="2"><?php echo $calendar_style; ?></textarea>
				</fieldset>
				<fieldset class="small"><legend><?php _e('Reset Styles'); ?></legend>
				    <input type="checkbox" name="reset_styles" /> <?php _e('Tick this box if you wish to reset the Calendar style to default'); ?>
                                </fieldset>
				<br />
                                <input type="submit" name="save" class="button bold" value="Save &raquo;" />
			</div>
                        <div style="clear:both; height:1px;">&nbsp;</div>
	        </div>
  </form>
  </div>
  <?php


}

// Function to handle the management of categories
function manage_categories()
{
  global $wpdb;

  // Calendar must be installed and upgraded before this will work
  check_calendar();

?>
<style type="text/css">
  <!--
   .error {
     background: lightcoral;
     border: 1px solid #e64f69;
     margin: 1em 5% 10px;
     padding: 0 1em 0 1em;
   }

  .center {
    text-align: center;
  }
  .right {
    text-align: right;
  }
  .left {
    text-align: left;
  }
  .top {
    vertical-align: top;
  }
  .bold {
    font-weight: bold;
  }
  .private {
  color: #e64f69;
  }
  //-->                                                                                                                                                                               
</style>
<?php
  // We do some checking to see what we're doing
  if (isset($_POST['mode']) && $_POST['mode'] == 'add')
    {
      $sql = "INSERT INTO " . WP_CALENDAR_CATEGORIES_TABLE . " SET category_name='".mysql_escape_string($_POST['category_name'])."', category_colour='".mysql_escape_string($_POST['category_colour'])."'";
      $wpdb->get_results($sql);
      echo "<div class=\"updated\"><p><strong>Category added successfully</strong></p></div>";
    }
  else if (isset($_GET['mode']) && isset($_GET['category_id']) && $_GET['mode'] == 'delete')
    {
      $sql = "DELETE FROM " . WP_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".mysql_escape_string($_GET['category_id']);
      $wpdb->get_results($sql);
      $sql = "UPDATE " . WP_CALENDAR_TABLE . " SET event_category=1 WHERE event_category=".mysql_escape_string($_GET['category_id']);
      $wpdb->get_results($sql);
      echo "<div class=\"updated\"><p><strong>Category deleted successfully</strong></p></div>";
    }
  else if (isset($_GET['mode']) && isset($_GET['category_id']) && $_GET['mode'] == 'edit' && !isset($_POST['mode']))
    {
      $sql = "SELECT * FROM " . WP_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".mysql_escape_string($_GET['category_id']);
      $cur_cat = $wpdb->get_row($sql);
      ?>
<div class="wrap">
   <h2><?php _e('Edit Category'); ?></h2>
    <form name="catform" id="catform" class="wrap" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="mode" value="edit" />
                <input type="hidden" name="category_id" value="<?php echo $cur_cat->category_id ?>" />
                <div id="item_manager">
                        <div style="float: left; width: 98%; clear: both;" class="top">
				<fieldset class="small"><legend><?php _e('Category Name:'); ?></legend>
                                        <input type="text" name="category_name" class="input" size="30" maxlength="30" value="<?php echo $cur_cat->category_name ?>" />
                                </fieldset>
				<fieldset class="small"><legend><?php _e('Category Colour (Hex format):'); ?></legend>
                                        <input type="text" name="category_colour" class="input" size="10" maxlength="7" value="<?php echo $cur_cat->category_colour ?>" />
                                </fieldset>
                                <br />
                                <input type="submit" name="save" class="button bold" value="Save &raquo;" />
                        </div>
                        <div style="clear:both; height:1px;">&nbsp;</div>
                </div>
    </form>
</div>
      <?php
    }
  else if (isset($_POST['mode']) && isset($_POST['category_id']) && isset($_POST['category_name']) && isset($_POST['category_colour']) && $_POST['mode'] == 'edit')
    {
      $sql = "UPDATE " . WP_CALENDAR_CATEGORIES_TABLE . " SET category_name='".mysql_escape_string($_POST['category_name'])."', category_colour='".mysql_escape_string($_POST['category_colour'])."' WHERE category_id=".mysql_escape_string($_POST['category_id']);
      $wpdb->get_results($sql);
      echo "<div class=\"updated\"><p><strong>Category edited successfully</strong></p></div>";
    }

  if ($_GET['mode'] != 'edit' || $_POST['mode'] == 'edit')
    {
?>

  <div class="wrap">
    <h2><?php _e('Add Category'); ?></h2>
    <form name="catform" id="catform" class="wrap" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="mode" value="add" />
                <input type="hidden" name="category_id" value="">
                <div id="item_manager">
                        <div style="float: left; width: 98%; clear: both;" class="top">
       				<fieldset class="small"><legend><?php _e('Category Name:'); ?></legend>
                                        <input type="text" name="category_name" class="input" size="30" maxlength="30" value="" />
                                </fieldset>
				<fieldset class="small"><legend><?php _e('Category Colour (Hex format):'); ?></legend>
                                        <input type="text" name="category_colour" class="input" size="10" maxlength="7" value="" />
                                </fieldset>
                                <br />
                                <input type="submit" name="save" class="button bold" value="Save &raquo;" />
                        </div>
		        <div style="clear:both; height:1px;">&nbsp;</div>
                </div>
    </form>
    <h2><?php _e('Manage Categories'); ?></h2>
<?php
    
    // We pull the categories from the database	
    $categories = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_id ASC");

 if ( !empty($categories) )
   {
     ?>
     <table width="50%" cellpadding="3" cellspacing="3">
       <tr>
         <th scope="col"><?php _e('ID') ?></th>
	 <th scope="col"><?php _e('Category Name') ?></th>
	 <th scope="col"><?php _e('Category Colour') ?></th>
	 <th scope="col"><?php _e('Edit') ?></th>
	 <th scope="col"><?php _e('Delete') ?></th>
       </tr>
       <?php
       $class = '';
       foreach ( $categories as $category )
         {
	   $class = ($class == 'alternate') ? '' : 'alternate';
           ?>
           <tr class="<?php echo $class; ?>">
	     <th scope="row"><?php echo $category->category_id; ?></th>
	     <td><?php echo $category->category_name; ?></td>
	     <td style="background-color:<?php echo $category->category_colour; ?>;">&nbsp;</td>
	     <td><a href="<?php echo $_SERVER['REQUEST_URI']  ?>&amp;mode=edit&amp;category_id=<?php echo $category->category_id;?>" class='edit'><?php echo __('Edit'); ?></a></td>
	     <?php
	     if ($category->category_id == 1)
	       {
             ?>
             <td>N/A</td>
              <?php
	       }
             else
	       {
               ?>
               <td><a href="<?php echo $_SERVER['REQUEST_URI'] ?>&amp;mode=delete&amp;category_id=<?php echo $category->category_id;?>" class="delete" onclick="return confirm('Are you sure you want to delete this category?')"><?php echo __('Delete'); ?></a></td>
               <?php
	       }
                ?>
              </tr>
                <?php
          }
      ?>
      </table>
      <?php
   }
 else
   {
     ?>
     <p><?php _e("There are no categories in the database - something has gone wrong!")     ?></p>
     <?php
   }

?>
  </div>

<?php
      } 
}

// Function to return a prefix which will allow the correct 
// placement of arguments into the query string.
function permalink_prefix()
{
  // Get the permalink structure from WordPress
  $p_link = get_permalink();

  // Work out what the real URL we are viewing is
  $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
  $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")).$s;
  $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
  $real_link = $protocol.'://'.$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];

  // Now use all of that to get the correctly craft the Calendar link prefix
  if (strstr($p_link, '?') && $p_link == $real_link)
    {
      $link_part = $p_link.'&';
    }
  else if ($p_link == $real_link)
    {
      $link_part = $p_link.'?';
    }
  else if (strstr($real_link, '?'))  
    {
      if (isset($_GET['month']) && isset($_GET['yr']))
	{
	  $new_tail = split("&", $real_link);
	  foreach ($new_tail as $item)
	    {
	      if (!strstr($item, 'month') && !strstr($item, 'yr'))
		{
		  $link_part .= $item.'&';
		}
	    }
	  if (!strstr($link_part, '?'))
	    {
	      $new_tail = split("month", $link_part);
	      $link_part = $new_tail[0].'?'.$new_tail[1];
	    }
	}
      else
	{
	  $link_part = $real_link.'&';
	}
    }
  else
    {
      $link_part = $real_link.'?';
    }

  return $link_part;
}

// Configure the "Next" link in the calendar
function next_link($cur_year,$cur_month)
{
  $mod_rewrite_months = array(1=>'jan','feb','mar','apr','may','jun','jul','aug','sept','oct','nov','dec');
  $next_year = $cur_year + 1;

  if ($cur_month == 12)
    {
      return '<a href="' . permalink_prefix() . 'month=jan&yr=' . $next_year . '">Next &raquo;</a>';
    }
  else
    {
      $next_month = $cur_month + 1;
      $month = $mod_rewrite_months[$next_month];
      return '<a href="' . permalink_prefix() . 'month='.$month.'&yr=' . $cur_year . '">Next &raquo;</a>';
    }
}

// Configure the "Previous" link in the calendar
function prev_link($cur_year,$cur_month)
{
  $mod_rewrite_months = array(1=>'jan','feb','mar','apr','may','jun','jul','aug','sept','oct','nov','dec');
  $last_year = $cur_year - 1;

  if ($cur_month == 1)
    {
      return '<a href="' . permalink_prefix() . 'month=dec&yr='. $last_year .'">&laquo; Prev</a>';
    }
  else
    {
      $next_month = $cur_month - 1;
      $month = $mod_rewrite_months[$next_month];
      return '<a href="' . permalink_prefix() . 'month='.$month.'&yr=' . $cur_year . '">&laquo; Prev</a>';
    }
}

// Print upcoming events
function upcoming_events()
{
  global $wpdb;

  // This function cannot be called unless calendar is up to date
  check_calendar();
 
  // Find out if we should be displaying upcoming events
  $display = $wpdb->get_var("SELECT config_value FROM ".WP_CALENDAR_CONFIG_TABLE." WHERE config_item='display_upcoming'",0,0);

  if ($display == 'true')
    {
      // Get number of days we should go into the future 
      $future_days = $wpdb->get_var("SELECT config_value FROM ".WP_CALENDAR_CONFIG_TABLE." WHERE config_item='display_upcoming_days'",0,0);
      $day_count = 1;

      while ($day_count < $future_days+1)
	{
	  list($y,$m,$d) = split("-",date("Y-m-d",mktime($day_count*24,0,0,date("m"),date("d"),date("Y"))));
	  $events = grab_events($y,$m,$d);
	  usort($events, "time_cmp");
	  if (count($events) != 0) {
	    $output .= '<li>'.date(get_option('date_format'),mktime($day_count*24,0,0,date("m"),date("d"),date("Y"))).'<ul>';
	  }
	  foreach($events as $event)
	    {
	      if ($event->event_time == '00:00:00') {
		$time_string = ' all day';
	      }
	      else {
		$time_string = ' at '.date(get_option('time_format'), strtotime($event->event_time));
	      }
              $output .= '<li>'.draw_widget_event($event).$time_string.'</li>';
	    }
	  if (count($events) != 0) {
	    $output .= '</ul></li>';
	  }
	  $day_count = $day_count+1;
	}

      if ($output != '')
	{
	  $visual = '<li class="upcoming-events"><h2>Upcoming Events</h2><ul>';
	  $visual .= $output;
	  $visual .= '</ul></li>';
	  return $visual;
	}
    }
}

// Print todays events
function todays_events()
{
  global $wpdb;

  // This function cannot be called unless calendar is up to date
  check_calendar();

  // Find out if we should be displaying todays events
  $display = $wpdb->get_var("SELECT config_value FROM ".WP_CALENDAR_CONFIG_TABLE." WHERE config_item='display_todays'",0,0);

  if ($display == 'true')
    {
      $output = '<li class="todays-events"><h2>Todays Events</h2><ul>';
      $events = grab_events(date("Y"),date("m"),date("d"));
      usort($events, "time_cmp");
      foreach($events as $event)
	{
	  if ($event->event_time == '00:00:00') {
	    $time_string = ' all day';
	  }
	  else {
	    $time_string = ' at '.date(get_option('time_format'), strtotime($event->event_time));
	  }
	  $output .= '<li>'.draw_widget_event($event).$time_string.'</li>';
	}
      $output .= '</ul></li>';
      if (count($events) != 0)
	{
	  return $output;
	}
    }
}

// Function to compare time in event objects
function time_cmp($a, $b)
{
  if ($a->event_time == $b->event_time) {
    return 0;
  }
  return ($a->event_time < $b->event_time) ? -1 : 1;
}

// Used to draw multiple events
function draw_events($events)
{
  // We need to sort arrays of objects by time
  usort($events, "time_cmp");

  // Now process the events
  foreach($events as $event)
    {
      $output .= draw_event($event);
    }
  return $output;
}


// The widget to show todays events in the sidebar
function widget_init_calendar_today() {
  // Check for required functions
  if (!function_exists('register_sidebar_widget'))
    return;

  function widget_calendar_today($args) {
    extract($args);
    ?>
      <?php echo todays_events(); ?>
			<?php
			    }

  register_sidebar_widget('Todays Events','widget_calendar_today');
  }

// The widget to show todays events in the sidebar                                              
function widget_init_calendar_upcoming() {
  // Check for required functions                                                               
  if (!function_exists('register_sidebar_widget'))
    return;

  function widget_calendar_upcoming($args) {
    extract($args);
    ?>
      <?php echo upcoming_events(); ?>
                        <?php
                            }

  register_sidebar_widget('Upcoming Events','widget_calendar_upcoming');
}


// Used to draw an event to the screen
function draw_event($event)
{
  global $wpdb;

  // Calendar must be updated to run this function
  check_calendar();

  // Before we do anything we want to know if we                                             
  // should display the author and/or show categories. 
  // We check for this later                                      
  $display_author = $wpdb->get_var("SELECT config_value FROM ".WP_CALENDAR_CONFIG_TABLE." WHERE config_item='display_author'",0,0);
  $show_cat = $wpdb->get_var("SELECT config_value FROM ".WP_CALENDAR_CONFIG_TABLE." WHERE config_item='enable_categories'",0,0);

  if ($show_cat == 'true')
    {
      $sql = "SELECT * FROM " . WP_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".$event->event_category;
      $cat_details = $wpdb->get_row($sql);
      $style = "background-color:".$cat_details->category_colour.";";
    }

  $header_details .=  '<div class="event-title">'.$event->event_title.'</div><div class="event-title-break"></div>';
  if ($event->event_time != "00:00:00")
    {
      $header_details .= '<strong>Time:</strong> ' . date(get_option('time_format'), strtotime($event->event_time)) . '<br />';
    }
  if ($display_author == 'true')
    {
      $e = get_userdata($event->event_author);
      $header_details .= '<strong>Posted by:</strong> '.$e->display_name.'<br />';
    }
  if ($display_author == 'true' || $event->event_time != "00:00:00")
    {
      $header_details .= '<div class="event-content-break"></div>';
    }
  if ($event->event_link != '') { $linky = $event->event_link; }
  else { $linky = '#'; }

  $details = '<br />
* <span class="calnk" nowrap="nowrap"><a href="'.$linky.'" style="'.$style.'">' . $event->event_title . '<span style="'.$style.'">' . $header_details . '' . $event->event_desc . '</span></a></span>';

  return $details;
}

// Draw an event but customise the HTML for use in the widget
function draw_widget_event($event)
{
  global $wpdb;

  // Calendar must be updated to run this function
  check_calendar();

  // Before we do anything we want to know if we
  // should display the author and/or show categories.
  // We check for this later
  $display_author = $wpdb->get_var("SELECT config_value FROM ".WP_CALENDAR_CONFIG_TABLE." WHERE config_item='display_author'",0,0);
  $show_cat = $wpdb->get_var("SELECT config_value FROM ".WP_CALENDAR_CONFIG_TABLE." WHERE config_item='enable_categories'",0,0);

  if ($show_cat == 'true')
    {
      $sql = "SELECT * FROM " . WP_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".$event->event_category;
      $cat_details = $wpdb->get_row($sql);
      $style = "background-color:".$cat_details->category_colour.";";
    }

  $header_details .=  '<div class="event-title">'.$event->event_title.'</div><div class="event-title-break"></div>';
  if ($event->event_time != "00:00:00")
    {
      $header_details .= '<strong>Time:</strong> ' . date(get_option('time_format'), strtotime($event->event_time)) . '<br />';
    }
  if ($display_author == 'true')
    {
      $e = get_userdata($event->event_author);
      $header_details .= '<strong>Posted by:</strong> '.$e->display_name.'<br />';
    }
  if ($display_author == 'true' || $event->event_time != "00:00:00")
    {
      $header_details .= '<div class="event-content-break"></div>';
    }
  if ($event->event_link != '') { $linky = $event->event_link; }
  else { $linky = '#'; }

  $details = '<span class="calnk" nowrap="nowrap"><a href="'.$linky.'">' . $event->event_title . '<span style="'.$style.'">' . $header_details . '' . $event->event_desc . '</span></a></span>';

  return $details;
}

// Grab all events for the requested date from calendar
function grab_events($y,$m,$d)
{
     global $wpdb;

     $arr_events = array();

     // Get the date format right
     $date = $y . '-' . $m . '-' . $d;
     
     // Firstly we check for conventional events. These will form the first instance of a recurring event
     // or the only instance of a one-off event
     $events = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_TABLE . " WHERE event_begin <= '$date' AND event_end >= '$date' AND event_recur = 'S' ORDER BY event_id");
     if (!empty($events))
     {
         foreach($events as $event)
         {
	   array_push($arr_events, $event);
         }
     }

	// Even if there were results for that query, we may still have events recurring 
	// from the past on this day. We now methodically check the for these events

	/* 
	 The yearly code - easy because the day and month will be the same, so we return all yearly
	 events that match the date part. Out of these we show those with a repeat of 0, and fast-foward
	 a number of years for those with a value more than 0. Those that land in the future are displayed.
	*/

	
	// Deal with forever recurring year events
	$events = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_TABLE . " WHERE event_recur = 'Y' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats = 0 ORDER BY event_id");

	if (!empty($events))
     	{
       	  foreach($events as $event)
          {
	    // This is going to get complex so lets setup what we would place in for 
	    // an event so we can drop it in with ease

	    // Technically we don't care about the years, but we need to find out if the 
	    // event spans the turn of a year so we can deal with it appropriately.
	    $year_begin = date('Y',strtotime($event->event_begin));
	    $year_end = date('Y',strtotime($event->event_end));

	    if ($year_begin == $year_end)
	    {
		if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) && 
			date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date)))
		{
	      		array_push($arr_events, $event);
		}
	    }
	    else if ($year_begin < $year_end)
	    {
		if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) || 
			date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date)))
		{
	      		array_push($arr_events, $event);
		}
	    }
          }
     	}
	
	// Now the ones that happen a finite number of times
	$events = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_TABLE . " WHERE event_recur = 'Y' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats != 0 AND (EXTRACT(YEAR FROM '$date')-EXTRACT(YEAR FROM event_begin)) <= event_repeats ORDER BY event_id");
	if (!empty($events))
     	{
       	  foreach($events as $event)
          {
	    // This is going to get complex so lets setup what we would place in for 
	    // an event so we can drop it in with ease

	    // Technically we don't care about the years, but we need to find out if the 
	    // event spans the turn of a year so we can deal with it appropriately.
	    $year_begin = date('Y',strtotime($event->event_begin));
	    $year_end = date('Y',strtotime($event->event_end));

	    if ($year_begin == $year_end)
	    {
		if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) && 
			date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date)))
		{
	      		array_push($arr_events, $event);
		}
	    }
	    else if ($year_begin < $year_end)
	    {
		if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) || 
			date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date)))
		{
	      		array_push($arr_events, $event);
		}
	    }
          }
     	}	

	/* 
	  The monthly code - just as easy because as long as the day of the month is correct, then we 
	  show the event
	*/

	// The monthly events that never stop recurring
	$events = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_TABLE . " WHERE event_recur = 'M' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats = 0 ORDER BY event_id");
	if (!empty($events))
     	{
       	  foreach($events as $event)
          {
	    // This is going to get complex so lets setup what we would place in for 
	    // an event so we can drop it in with ease

	    // Technically we don't care about the years or months, but we need to find out if the 
	    // event spans the turn of a year or month so we can deal with it appropriately.
	    $month_begin = date('m',strtotime($event->event_begin));
	    $month_end = date('m',strtotime($event->event_end));

	    if ($month_begin == $month_end)
	    {
		if (date('d',strtotime($event->event_begin)) <= date('d',strtotime($date)) && 
			date('d',strtotime($event->event_end)) >= date('d',strtotime($date)))
		{
	      		array_push($arr_events, $event);
		}
	    }
	    else if ($month_begin < $month_end)
	    {
		if ( ($event->event_begin <= date('Y-m-d',strtotime($date))) && (date('d',strtotime($event->event_begin)) <= date('d',strtotime($date)) || 
			date('d',strtotime($event->event_end)) >= date('d',strtotime($date))) )
		{
	      		array_push($arr_events, $event);
		}
	    }
          }
     	}


	// Now the ones that happen a finite number of times
	$events = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_TABLE . " WHERE event_recur = 'M' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats != 0 AND (PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '$date'),EXTRACT(YEAR_MONTH FROM event_begin))) <= event_repeats ORDER BY event_id");
	if (!empty($events))
     	{
       	  foreach($events as $event)
          {
	    // This is going to get complex so lets setup what we would place in for 
	    // an event so we can drop it in with ease

	    // Technically we don't care about the years or months, but we need to find out if the 
	    // event spans the turn of a year or month so we can deal with it appropriately.
	    $month_begin = date('m',strtotime($event->event_begin));
	    $month_end = date('m',strtotime($event->event_end));

	    if ($month_begin == $month_end)
	    {
		if (date('d',strtotime($event->event_begin)) <= date('d',strtotime($date)) && 
			date('d',strtotime($event->event_end)) >= date('d',strtotime($date)))
		{
		        array_push($arr_events, $event);
		}
	    }
	    else if ($month_begin < $month_end)
	    {
		if ( ($event->event_begin <= date('Y-m-d',strtotime($date))) && (date('d',strtotime($event->event_begin)) <= date('d',strtotime($date)) || 
			date('d',strtotime($event->event_end)) >= date('d',strtotime($date))) )
		{
	      		array_push($arr_events, $event);
		}
	    }
          }
     	}


	/* 
	  Weekly - well isn't this fun! We need to scan all weekly events, find what day they fell on
	  and see if that matches the current day. If it does, we check to see if the repeats are 0. 
	  If they are, display the event, if not, we fast forward from the original day in week blocks 
	  until the number is exhausted. If the date we arrive at is in the future, display the event.
	*/

	// The weekly events that never stop recurring
	$events = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_TABLE . " WHERE event_recur = 'W' AND '$date' >= event_begin AND event_repeats = 0 ORDER BY event_id");
	if (!empty($events))
     	{
       	  foreach($events as $event)
          {
	    // This is going to get complex so lets setup what we would place in for 
	    // an event so we can drop it in with ease

	    // Now we are going to check to see what day the original event
	    // fell on and see if the current date is both after it and on 
	    // the correct day. If it is, display the event!
	    $day_start_event = date('D',strtotime($event->event_begin));
	    $day_end_event = date('D',strtotime($event->event_end));
	    $current_day = date('D',strtotime($date));

	    $plan = array();
	    $plan['Mon'] = 1;
	    $plan['Tue'] = 2;
	    $plan['Wed'] = 3;
	    $plan['Thu'] = 4;
	    $plan['Fri'] = 5;
	    $plan['Sat'] = 6;
	    $plan['Sun'] = 7;

	    if ($plan[$day_start_event] > $plan[$day_end_event])
	    {
		if (($plan[$day_start_event] <= $plan[$current_day]) || ($plan[$current_day] <= $plan[$day_end_event]))
	    	{
			array_push($arr_events, $event);
	    	}
	    }
	    else if (($plan[$day_start_event] < $plan[$day_end_event]) || ($plan[$day_start_event]== $plan[$day_end_event]))
	    {
		if (($plan[$day_start_event] <= $plan[$current_day]) && ($plan[$current_day] <= $plan[$day_end_event]))
	    	{
			array_push($arr_events, $event);
	    	}		
	    }
	    
          }
     	}

	// The weekly events that have a limit on how many times they occur
	$events = $wpdb->get_results("SELECT * FROM " . WP_CALENDAR_TABLE . " WHERE event_recur = 'W' AND '$date' >= event_begin AND event_repeats != 0 AND (event_repeats*7) >= (TO_DAYS('$date') - TO_DAYS(event_end)) ORDER BY event_id");
	if (!empty($events))
     	{
       	  foreach($events as $event)
          {
	    // This is going to get complex so lets setup what we would place in for 
	    // an event so we can drop it in with ease

	    // Now we are going to check to see what day the original event
	    // fell on and see if the current date is both after it and on 
	    // the correct day. If it is, display the event!
	    $day_start_event = date('D',strtotime($event->event_begin));
	    $day_end_event = date('D',strtotime($event->event_end));
	    $current_day = date('D',strtotime($date));

	    $plan = array();
	    $plan['Mon'] = 1;
	    $plan['Tue'] = 2;
	    $plan['Wed'] = 3;
	    $plan['Thu'] = 4;
	    $plan['Fri'] = 5;
	    $plan['Sat'] = 6;
	    $plan['Sun'] = 7;

	    if ($plan[$day_start_event] > $plan[$day_end_event])
	    {
		if (($plan[$day_start_event] <= $plan[$current_day]) || ($plan[$current_day] <= $plan[$day_end_event]))
	    	{
			array_push($arr_events, $event);
	    	}
	    }
	    else if (($plan[$day_start_event] < $plan[$day_end_event]) || ($plan[$day_start_event]== $plan[$day_end_event]))
	    {
		if (($plan[$day_start_event] <= $plan[$current_day]) && ($plan[$current_day] <= $plan[$day_end_event]))
	    	{
			array_push($arr_events, $event);
	    	}		
	    }

          }
     	}
 
     return $arr_events;
}


// Actually do the printing of the calendar
// Compared to searching for and displaying events
// this bit is really rather easy!
function calendar()
{
    global $wpdb;

    // First things first, make sure calendar is up to date
    check_calendar();

    // Deal with the week not starting on a monday
    if (get_option('start_of_week') == 0)
      {
	$name_days = array(1=>'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
      }
    // Choose Monday if anything other than Sunday is set
    else
      {
	$name_days = array(1=>'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
      }

    // Carry on with the script
    $name_months = array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');

    // If we don't pass arguments we want a calendar that is relevant to today
    if (empty($_GET['month']) || empty($_GET['yr']))
    {
        $c_year = date("Y");
        $c_month = date("m");
        $c_day = date("d");
    }

    // Years get funny if we exceed 3000, so we use this check
    if ($_GET['yr'] <= 3000 && $_GET['yr'] >= 0)
    {
        // This is just plain nasty and all because of permalinks
        // which are no longer used, this will be cleaned up soon
        if ($_GET['month'] == 'jan' || $_GET['month'] == 'feb' || $_GET['month'] == 'mar' || $_GET['month'] == 'apr' || $_GET['month'] == 'may' || $_GET['month'] == 'jun' || $_GET['month'] == 'jul' || $_GET['month'] == 'aug' || $_GET['month'] == 'sept' || $_GET['month'] == 'oct' || $_GET['month'] == 'nov' || $_GET['month'] == 'dec')
	  {

	       // Again nasty code to map permalinks into something
	       // databases can understand. This will be cleaned up
               $c_year = mysql_escape_string($_GET['yr']);
               if ($_GET['month'] == 'jan') { $t_month = 1; }
               else if ($_GET['month'] == 'feb') { $t_month = 2; }
               else if ($_GET['month'] == 'mar') { $t_month = 3; }
               else if ($_GET['month'] == 'apr') { $t_month = 4; }
               else if ($_GET['month'] == 'may') { $t_month = 5; }
               else if ($_GET['month'] == 'jun') { $t_month = 6; }
               else if ($_GET['month'] == 'jul') { $t_month = 7; }
               else if ($_GET['month'] == 'aug') { $t_month = 8; }
               else if ($_GET['month'] == 'sept') { $t_month = 9; }
               else if ($_GET['month'] == 'oct') { $t_month = 10; }
               else if ($_GET['month'] == 'nov') { $t_month = 11; }
               else if ($_GET['month'] == 'dec') { $t_month = 12; }
               $c_month = $t_month;
               $c_day = date("d");
        }
	// No valid month causes the calendar to default to today
        else
        {
               $c_year = date("Y");
               $c_month = date("m");
               $c_day = date("d");
        }
    }
    // No valid year causes the calendar to default to today
    else
    {
        $c_year = date("Y");
        $c_month = date("m");
        $c_day = date("d");
    }

    // Fix the days of the week if week start is not on a monday
    if (get_option('start_of_week') == 0)
      {
	$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
        $first_weekday = ($first_weekday==0?1:$first_weekday+1);
      }
    // Otherwise assume the week starts on a Monday. Anything other 
    // than Sunday or Monday is just plain odd
    else
      {
	$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
	$first_weekday = ($first_weekday==0?7:$first_weekday);
      }

    $days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));

    // Start the table and add the header and naviagtion
    $calendar_body .= '
<table cellspacing="1" cellpadding="0" class="calendar-table">
';

    // We want to know if we should display the date switcher
    $date_switcher = $wpdb->get_var("SELECT config_value FROM ".WP_CALENDAR_CONFIG_TABLE." WHERE config_item='display_jump'",0,0);

    if ($date_switcher == 'true')
      {
	$calendar_body .= '<tr>
        <td colspan="7" class="calendar-date-switcher">
            <form method="GET" action="'.$_SERVER['REQUEST_URI'].'">
';
	$qsa = array();
	parse_str($_SERVER['QUERY_STRING'],$qsa);
	foreach ($qsa as $name => $argument)
	  {
	    if ($name != 'month' && $name != 'yr')
	      {
		$calendar_body .= '<input type="hidden" name="'.$name.'" value="'.$argument.'" />
';
	      }
	  }
	function month_comparison($month)
	  {
	    $current_month = strtolower(date("M", time()));
	    if (isset($_GET['yr']) && isset($_GET['month']))
	      {
		if ($month == $_GET['month'])
		  {
		    return ' selected="selected"';
		  }
	      }
	    elseif ($month == $current_month) 
	      { 
		return ' selected="selected"'; 
	      }
	  }
	// We build the months in the switcher
	$calendar_body .= '
            Month: <select name="month" style="width:100px;">
            <option value="jan"'.month_comparison('jan').'>January</option>
            <option value="feb"'.month_comparison('feb').'>February</option>
            <option value="mar"'.month_comparison('mar').'>March</option>
            <option value="apr"'.month_comparison('apr').'>April</option>
            <option value="may"'.month_comparison('may').'>May</option>
            <option value="jun"'.month_comparison('jun').'>June</option>
            <option value="jul"'.month_comparison('jul').'>July</option> 
            <option value="aug"'.month_comparison('aug').'>August</option> 
            <option value="sept"'.month_comparison('sept').'>September</option> 
            <option value="oct"'.month_comparison('oct').'>October</option> 
            <option value="nov"'.month_comparison('nov').'>November</option> 
            <option value="dec"'.month_comparison('dec').'>December</option> 
            </select>
            Year: <select name="yr" style="width:60px;">
';

	// The year builder is string mania. If you can make sense of this, 
        // you know your PHP!
	function year_comparison($year)
          {
            $current_year = strtolower(date("Y", time()));
            if (isset($_GET['yr']) && isset($_GET['month']))
              {
                if ($year == $_GET['yr'])
                  {
                    return ' selected="selected"';
                  }
              }
            else if ($year == $current_year)
              {
                return ' selected="selected"';
              }
          }

	$past = 30;
	$future = 30;
	$fut = 1;
	while ($past > 0)
	  {
	    $p .= '            <option value="';
	    $p .= date("Y",time())-$past;
	    $p .= '"'.year_comparison(date("Y",time())-$past).'>';
	    $p .= date("Y",time())-$past.'</option>
';
	    $past = $past - 1;
	  }
	while ($fut < $future) 
	  {
	    $f .= '            <option value="';
	    $f .= date("Y",time())+$fut;
	    $f .= '"'.year_comparison(date("Y",time())+$fut).'>';
	    $f .= date("Y",time())+$fut.'</option>
';
	    $fut = $fut + 1;
	  } 
	$calendar_body .= $p;
	$calendar_body .= '            <option value="'.date("Y",time()).'"'.year_comparison(date("Y",time())).'>'.date("Y",time()).'</option>
';
	$calendar_body .= $f;
        $calendar_body .= '</select>
            <input type="submit" value="Go" />
            </form>
        </td>
</tr>
';
      }

    // The header of the calendar table and the links. Note calls to link functions
    $calendar_body .= '<tr>
                <td colspan="7" class="calendar-heading">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                    <td class="calendar-prev">' . prev_link($c_year,$c_month) . '</td>
                    <td class="calendar-month">'.$name_months[(int)$c_month].' '.$c_year.'</td>
                    <td class="calendar-next">' . next_link($c_year,$c_month) . '</td>
                    </tr>
                    </table>
                </td>
</tr>
';

    // Print the headings of the days of the week
    $calendar_body .= '<tr>
';
    for ($i=1; $i<=7; $i++) 
      {
	// Colours need to be different if the starting day of the week is different
	if (get_option('start_of_week') == 0)
	  {
	    $calendar_body .= '        <td class="'.($i<7&&$i>1?'normal-day-heading':'weekend-heading').'">'.$name_days[$i].'</td>
';
	  }
	else
	  {
	    $calendar_body .= '        <td class="'.($i<6?'normal-day-heading':'weekend-heading').'">'.$name_days[$i].'</td>
';
	  }
      }
    $calendar_body .= '</tr>
';

    for ($i=1; $i<=$days_in_month;)
      {
        $calendar_body .= '<tr>
';
        for ($ii=1; $ii<=7; $ii++)
	  {
            if ($ii==$first_weekday && $i==1)
	      {
		$go = TRUE;
	      }
            elseif ($i > $days_in_month ) 
	      {
		$go = FALSE;
	      }

            if ($go) 
	      {
		// Colours again, this time for the day numbers
		if (get_option('start_of_week') == 0)
		  {
		    // This bit of code is for styles believe it or not.
		    $grabbed_events = grab_events($c_year,$c_month,$i);
		    $no_events_class = '';
		    if (!count($grabbed_events))
		      {
			$no_events_class = ' no-events';
		      }
		    $calendar_body .= '        <td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date("Ymd")?'current-day':'day-with-date').$no_events_class.'"><span '.($ii<7&&$ii>1?'':'class="weekend"').'>'.$i++.'</span><span class="event">' . draw_events($grabbed_events) . '</span></td>
';
		  }
		else
		  {
		    $grabbed_events = grab_events($c_year,$c_month,$i);
		    $no_events_class = '';
	            if (!count($grabbed_events))
		      {
			$no_events_class = ' no-events';
		      }
		    $calendar_body .= '        <td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date("Ymd")?'current-day':'day-with-date').$no_events_class.'"><span '.($ii<6?'':'class="weekend"').'>'.$i++.'</span><span class="event">' . draw_events($grabbed_events) . '</span></td>
';
		  }
	      }
            else 
	      {
		$calendar_body .= '        <td class="day-without-date">&nbsp;</td>
';
	      }
        }
        $calendar_body .= '</tr>
';
    }
    $show_cat = $wpdb->get_var("SELECT config_value FROM ".WP_CALENDAR_CONFIG_TABLE." WHERE config_item='enable_categories'",0,0);

    if ($show_cat == 'true')
      {
	$sql = "SELECT * FROM " . WP_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_name ASC";
	$cat_details = $wpdb->get_results($sql);
        $calendar_body .= '<tr><td colspan="7">
<table class="cat-key">
<tr><td colspan="2"><strong>Category Key</strong></td></tr>
';
        foreach($cat_details as $cat_detail)
	  {
	    $calendar_body .= '<tr><td style="background-color:'.$cat_detail->category_colour.'; width:20px; height:20px;"></td><td>'.$cat_detail->category_name.'</td></tr>';
	  }
        $calendar_body .= '</table>
</td></tr>
';
      }
    $calendar_body .= '</table>
';

    // A little link to yours truely. See the README if you wish to remove this
    $calendar_body .= '<div class="kjo-link"><p>Web development and hosting from <a href="http://www.kjowebservices.co.uk">KJO Web Services</a></p></div>
';

    // Phew! After that bit of string building, spit it all out.
    // The actual printing is done by the calling function.
    return $calendar_body;
}

?>
