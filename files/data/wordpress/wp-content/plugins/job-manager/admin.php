<?php //encoding: utf-8

// Admin Settings
require_once( JOBMAN_DIR . '/admin-settings.php' );
// Frontend Display Settings
require_once( JOBMAN_DIR . '/admin-frontend-settings.php' );
// Job Form Setup
require_once( JOBMAN_DIR . '/admin-jobs-settings.php' );
// Job management
require_once( JOBMAN_DIR . '/admin-jobs.php' );
// Application form setup
require_once( JOBMAN_DIR . '/admin-application-form.php' );
// Applications
require_once( JOBMAN_DIR . '/admin-applications.php' );
// Emails
require_once( JOBMAN_DIR . '/admin-emails.php' );
// Interview Scheduling
require_once( JOBMAN_DIR . '/admin-interviews.php' );
// Comment handling functions
require_once( JOBMAN_DIR . '/admin-comments.php' );

function jobman_admin_setup() {
	$options = get_option( 'jobman_options' );

	// Setup the admin menu item
	$pages = array();
	add_menu_page( __( 'Job Manager', 'jobman' ), __( 'Job Manager', 'jobman' ), 'publish_posts', 'jobman-conf', 'jobman_conf' );
	$pages[] = add_submenu_page( 'jobman-conf', __( 'Job Manager', 'jobman' ), __( 'Settings', 'jobman' ), 'manage_options', 'jobman-conf', 'jobman_conf' );
	$pages[] = add_submenu_page( 'jobman-conf', __( 'Job Manager', 'jobman' ), __( 'Add Job', 'jobman' ), 'publish_posts', 'jobman-add-job', 'jobman_add_job' );
	$pages[] = add_submenu_page( 'jobman-conf', __( 'Job Manager', 'jobman' ), __( 'Jobs', 'jobman' ), 'publish_posts', 'jobman-list-jobs', 'jobman_list_jobs' );
	$pages[] = add_submenu_page( 'jobman-conf', __( 'Job Manager', 'jobman' ), __( 'Applications', 'jobman' ), 'read_private_pages', 'jobman-list-applications', 'jobman_list_applications' );
	$pages[] = add_submenu_page( 'jobman-conf', __( 'Job Manager', 'jobman' ), __( 'Emails', 'jobman' ), 'read_private_pages', 'jobman-list-emails', 'jobman_list_emails' );
	
	if( $options['interviews'] )
		$pages[] = add_submenu_page( 'jobman-conf', __( 'Job Manager', 'jobman' ), __( 'Interviews', 'jobman' ), 'read_private_pages', 'jobman-interviews', 'jobman_interviews' );

	// Load our header info
	foreach( $pages as $page ) {
		add_action( "admin_print_styles-$page", 'jobman_admin_print_styles' );
		add_action( "admin_print_scripts-$page", 'jobman_admin_print_scripts' );
		add_action( "admin_head-$page", 'jobman_admin_header' );
	}
}

function jobman_plugin_row_meta( $links, $file ) {
	if( JOBMAN_BASENAME == $file && ! get_option( 'pento_consulting' ) ) {
		$links[] = '<a href="http://www.amazon.com/wishlist/1ORKI9ZG875BL">' . __( 'My Amazon Wish List', 'jobman' ) . '</a>';
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=gary%40pento%2enet&item_name=WordPress%20Plugin%20(Job%20Manager)&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8">' . __( 'Donate with PayPal', 'jobman' ) . '</a>';
	}
	
	return $links;
}

function jobman_admin_print_styles() {
	global $wp_styles;
	
	wp_enqueue_style( 'jobman-admin', JOBMAN_URL . '/css/admin.css', false, JOBMAN_VERSION, 'all' );
	wp_enqueue_style( 'jobman-admin-print', JOBMAN_URL . '/css/admin-print.css', false, JOBMAN_VERSION, 'print' );
	wp_enqueue_style( 'jobman-admin-print-ie7', JOBMAN_URL . '/css/admin-print-ie7.css', false, JOBMAN_VERSION, 'print' );
	wp_enqueue_style( 'dashboard' );
	
	$wp_styles->add_data( 'jobman-admin-print-ie7', 'conditional', 'lte IE 7' );
}

function jobman_admin_print_scripts() {
	wp_enqueue_script( 'jobman-admin', JOBMAN_URL . '/js/admin.js', false, JOBMAN_VERSION );
	wp_enqueue_script( 'jquery-ui-datepicker', JOBMAN_URL . '/js/jquery-ui-datepicker.js', array( 'jquery-ui-core' ), JOBMAN_VERSION );
	wp_enqueue_script( 'dashboard' );
}

function jobman_admin_header() {
	$options = get_option( 'jobman_options' );

	$textareas = array();
	foreach( $options['job_fields'] as $id => $field ) {
		if( 'textarea' == $field['type'] ) {
			$textareas[] = $id;
		}
	}
?>
<script type="text/javascript"> 
//<![CDATA[
addLoadEvent(function() {
	jQuery(".datepicker").datepicker({
								dateFormat: 'yy-mm-dd', 
								changeMonth: true, 
								changeYear: true, 
								gotoCurrent: true,
								showOn: 'button',
								buttonImage: '<?php echo JOBMAN_URL ?>/images/calendar.gif',
								buttonImageOnly: true
							});
	jQuery(".column-cb > *").click(function() { jQuery(".check-column > *").attr('checked', jQuery(this).is(':checked')) } );
	
	jQuery("div.star-holder img").click(function() {
	    var cssclass = jQuery(this).parent().attr("class");
		var count = cssclass.replace("star star", "");
		jQuery(this).parent().parent().find("input[name=jobman-rating]").attr("value", count);
		jQuery(this).parent().parent().find("div.star-rating").css("width", (count * 19) + "px");
		
        var data = jQuery(this).parent().parent().find("input[name=callbackid]");
        var func = jQuery(this).parent().parent().find("input[name=callbackfunction]");
        var callback;
        if( data.length > 0 ) {
			callback = {
			        action: func[0].value,
			        appid: data[0].value,
			        rating: count
			};
			
			jQuery.post( ajaxurl, callback );
		}
	});
	
	jQuery("div.star-holder img").mouseenter(function() {
	    var cssclass = jQuery(this).parent().attr("class");
		var count = cssclass.replace("star star", "");
		jQuery(this).parent().parent().find("div.star-rating").css("width", (count * 19) + "px");
	});

	jQuery("div.star-holder img").mouseleave(function() {
		var count = jQuery(this).parent().parent().find("input[name=jobman-rating]").attr("value");
		jQuery(this).parent().parent().find("div.star-rating").css("width", (count * 19) + "px");
	});
	
<?php
	if( user_can_richedit() ) {
?>
	var jobman_textareas = <?php echo json_encode( $textareas ) ?>;

	for( ii in jobman_textareas ) {
		fieldid = 'jobman-field-' + jobman_textareas[ii];
		jQuery('#field-toolbar-' + jobman_textareas[ii] + ' a.toggleVisual').click(function() {
			tinyMCE.execCommand('mceAddControl', false, jQuery(this).parent().parent().find('textarea').attr('id'));
			jQuery(this).parent().find('a.toggleHTML').removeClass('active');
			jQuery(this).parent().find('a').removeClass('cssadjust');
			jQuery(this).addClass('active');
		});
		
		jQuery('#field-toolbar-' + jobman_textareas[ii] + ' a.toggleHTML').click(function() {
			tinyMCE.execCommand('mceRemoveControl', false, jQuery(this).parent().parent().find('textarea').attr('id'));
			jQuery(this).parent().find('a.toggleVisual').removeClass('active');
			jQuery(this).parent().find('a').addClass('cssadjust');
			jQuery(this).addClass('active');
		});
	}
<?php
	}
?>
});

function jobman_reset_rating( id, func ) {
	jQuery( "#jobman-rating-" + id ).attr("value", 0);
	jQuery( "#jobman-star-rating-" + id ).css("width", "0px");
	
	if( "filter" != id ) {
		callback = {
				action: func,
				appid: id,
				rating: 0
		};
		
		jQuery.post( ajaxurl, callback );
	}
}
//]]>
</script> 
<?php
}

function jobman_print_settings_tabs() {
	$tabs = array(
				'admin' => __( 'Admin Settings', 'jobman' ),
				'display' => __('Display Settings', 'jobman'),
				'appform' => __('App. Form Settings', 'jobman'),
				'jobform' => __('Job Form Settings', 'jobman'),
			);

	if( ! array_key_exists( 'tab', $_REQUEST ) )
		$_REQUEST['tab'] = 'admin';
?>
		<h2 class="jobman-tabs">
<?php
	foreach( $tabs as $key => $title ) {
		$class = '';
		if( $key != $_REQUEST['tab'] )
			$class = ' menu-tab-inactive';
		echo "<a href='" . admin_url( "admin.php?page=jobman-conf&amp;tab=$key" ) . "' class='menu-tabs$class'>$title</a>";
	}
?>
		</h2>
<?php
}

function jobman_print_donate_box() {
?>
		<p><?php _e( "If this plugin helps you find that perfect new employee, I'd appreciate it if you shared the love, by way of my Donate or Amazon Wish List links below.", 'jobman' ) ?></p>
		<ul>
			<li><a href="http://www.amazon.com/wishlist/1ORKI9ZG875BL"><?php _e( 'My Amazon Wish List', 'jobman' ) ?></a></li>
			<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=gary%40pento%2enet&item_name=WordPress%20Plugin%20(Job%20Manager)&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8"><?php _e( 'Donate with PayPal', 'jobman' ) ?></a></li>
		</ul>
<?php
}

function jobman_print_about_box() {
?>
		<ul>
			<li><a href="http://pento.net/"><?php _e( "Gary Pendergast's Blog", 'jobman' ) ?></a></li>
			<li><a href="http://twitter.com/garypendergast"><?php _e( 'Follow me on Twitter!', 'jobman' ) ?></a></li>
			<li><a href="http://pento.net/projects/wordpress-job-manager-plugin/"><?php _e( 'Plugin Homepage', 'jobman' ) ?></a></li>
			<li><a href="http://code.google.com/p/wordpress-job-manager/issues/list"><?php _e( 'Submit a Bug/Feature Request', 'jobman' ) ?></a></li>
		</ul>
<?php
}

function jobman_print_translators_box() {
?>
		<p><?php _e( "If you're using Job Manager in a language other than English, you have some of my wonderful translators to thank for it!", 'jobman' ) ?></p>
		<p><?php printf( __( "If you're fluent in a language not listed here, and would like to appear on this list, please <a href='%1s'>contact me</a>!", 'jobman' ), 'http://pento.net/contact/' ) ?>
		<ul>
			<li><strong><?php _e( 'Danish', 'jobman' ) ?></strong> - <a href="http://www.lithin.com/">Christian Olesen</a>, <a href="http://www.lithin.com/">Caspar Lange</a></li>
			<li><strong><?php _e( 'Dutch', 'jobman' ) ?></strong> - <a href="http://www.centrologic.nl/">Patrick Tessels</a>, <a href="http://webtaurus.nl/">Henk van den Bor</a></li>
			<li><strong><?php _e( 'Estonian', 'jobman' ) ?></strong> - Robert Jakobson</li>
			<li><strong><?php _e( 'French', 'jobman' ) ?></strong> - <a href="http://www.procure-smart.com/">Fabrice Fotso</a>, Vincent Clady</li>
			<li><strong><?php _e( 'German', 'jobman' ) ?></strong> - <a href="http://www.tolingo.com/">tolingo translations</a>, <a href="http://www.la-palma-diving.com/">Joachim Richter</a></li>
			<li><strong><?php _e( 'Portuguese (Brazil)', 'jobman' ) ?></strong> - <a href="http://www.alexfj.com.br/">Alex Ferreira</a></li>
			<li><strong><?php _e( 'Spanish', 'jobman' ) ?></strong> - <a href="http://www.tradiart.com">TradiArt</a></li>
			<li><strong><?php _e( 'Swedish', 'jobman' ) ?></strong> - <a href="http://www.saxekon.se/">Berndt Axelsson</a></li>
		</ul>
<?php
}
?>