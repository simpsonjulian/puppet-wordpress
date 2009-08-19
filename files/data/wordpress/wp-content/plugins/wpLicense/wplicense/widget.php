<?php

function widget_cc_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;

	// This is the function that outputs our little Google search form.
	function widget_cc($args) {
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);
		// $options = get_option('widget_cc_options');
		// $title = $options['title'];

		echo $before_widget; // . $before_title . $title . $after_title;

		echo '<div style="text-align: center">';
		licenseHtml();
		echo '</div>';

		echo $after_widget;
	}

	function widget_cc_admin() {
		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_cc_options');

		if ( !is_array($options) )
			$options = array('title'=>'', );
		if ( $_POST['license-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['license-title']));
			update_option('widget_cc_options', $options);
		}

		// sanitize...
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		
		echo '<p style="text-align:right;"><label for="license-title">' . __('Title:') . ' <input style="width: 200px;" id="license-title" name="license-title" type="text" value="'.$title.'" /></label></p>';

		echo '<input type="hidden" id="license-submit" name="license-submit" value="1" />';

	} // widget_cc_admin

	// Register the widget
	register_sidebar_widget(array('Content License', 'widgets'), 'widget_cc');
	// register_widget_control(array('Content License', 'widgets'), 'widget_cc_admin', 300, 75);
} // widget_cc_init

// Add the hook for initialization
add_action ('widgets_init', 'widget_cc_init');
?>
