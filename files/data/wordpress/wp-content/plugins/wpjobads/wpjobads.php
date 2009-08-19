<?php
/*
Plugin Name: WPJobAds
Plugin URI: http://www.wpjobads.com
Description: Self-managed job board. Instant Payment via PayPal.
Author: WPJobAds.com
Version: 1.2.3
License: Commercial
Author URI: http://www.wpjobads.com
*/
###############################################################################
# WPJobAds version 1.2.3                                                      #
# Copyright © 2008 - 2009 WPJobAds.com All Rights Reserved.                   #
# This file may not be redistributed in whole or significant part.            #
#                                                                             #
# The complete EULA can be found along with this file. Alternatively,         #
# it can be viewed online at http://www.wpjobads.com/license                  #
#                                                                             #
# Info    : info@wpjobads.com                                                 #
# Support : support@wpjobads.com                                              #
###############################################################################

error_reporting(E_ALL & E_WARNING);

// ----------------------------------------------------------------------------

// Global constants

// {{{ GLOBALS
define('WPJOBADS_VERSION', '1.2.3');
define('WPJOBADS_RELEASE_DATE', '2009-03-23');
define('WPJOBADS_UPDATE_URL', 'http://www.wpjobads.com/download');
define('WPJOBADS_CHECK_UPDATE_URL', 'http://www.wpjobads.com/update-check');
define('WPJOBADS_CHECK_UPDATE_PERIOD', 43200);
define('WPJOBADS_FORCE_CHECK_UPDATE', true);
define('WPJOBADS_JOB', 'wpjobads_job');
define('WPJOBADS_CATEGORY', 'wpjobads_category');
define('WPJOBADS_TITLE_SEPARATOR', ' &raquo ');
define('WPJOBADS_ADMIN_JOB_ENTRIES', 20);
define('WPJOBADS_SEND_EMAIL', true);
define('WPJOBADS_WRITE_LOG', true);
// }}}

// ----------------------------------------------------------------------------

// Includes

if (is_readable('wpjobads-template-' . get_option('template'))) {// {{{
    include 'wpjobads-template-' . get_option('template') . '.php';
} else {
    include 'wpjobads-template.php';
}// }}}

// ----------------------------------------------------------------------------

// Initialization functions

function wpjobads_init()// {{{
{
    // Possibly check for serial number?
    load_plugin_textdomain('wpjobads', 'wp-content/plugins/wpjobads');
    wp_enqueue_script('prototype');
}// }}}

add_action('init', 'wpjobads_init');

function wpjobads_preview($job)// {{{
{
    ob_start();
    $permalink = wpjobads_get_permalink();
    $parsed_url = parse_url($permalink);
    $path = $parsed_url['path'];
    $permalink .= ($path{strlen($path)-1} == '/') ? '#wpjobads' : '/#wpjobads';
?>
    <form method="post" action="<?php echo $permalink ?>">
        <input type="hidden" name="wpjobads_title" value="<?php echo attribute_escape($job['title']) ?>" />
        <input type="hidden" name="wpjobads_category" value="<?php echo attribute_escape($job['category']) ?>" />
        <input type="hidden" name="wpjobads_type" value="<?php echo attribute_escape($job['type']) ?>" />
        <input type="hidden" name="wpjobads_description" value="<?php echo attribute_escape($job['description']) ?>" />
        <input type="hidden" name="wpjobads_how_to_apply" value="<?php echo attribute_escape($job['how_to_apply']) ?>" />
        <input type="hidden" name="wpjobads_location" value="<?php echo attribute_escape($job['location']) ?>" />
        <input type="hidden" name="wpjobads_zipcode" value="<?php echo attribute_escape($job['zipcode']) ?>" />
        <input type="hidden" name="wpjobads_company_name" value="<?php echo attribute_escape($job['company_name']) ?>" />
        <input type="hidden" name="wpjobads_company_url" value="<?php echo attribute_escape($job['company_url']) ?>" />
        <input type="hidden" name="wpjobads_contact_name" value="<?php echo attribute_escape($job['contact_name']) ?>" />
        <input type="hidden" name="wpjobads_contact_email" value="<?php echo attribute_escape($job['contact_email']) ?>" />
        <input type="hidden" name="wpjobads-action" value="postjob">
        <p><?php _e('Are you sure?') ?></p>
        <input type="submit" value="<?php echo attribute_escape(__('Yes')) ?>"> <a href="javascript:history.go(-1);"><?php _e('No') ?></a>
    </form>
<?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}// }}}

function wpjobads_license_form($redirect = null)// {{{
{
    if (!$redirect) $redirect = 'wpjobads/wpjobads.php';
    $nonce_action = 'activate-license';
?>
    <div class="wrap">
        <h2>Plugin Activation</h2>
        <p><label for="license_key">Please enter your license key:</label></p>
        <form name="license" id="license" method="post" action="admin.php?page=wpjobads-admin-options">
            <?php wp_nonce_field($nonce_action) ?>
            <input type="hidden" name="action" value="activate">
            <input type="hidden" name="r" value="<?php echo attribute_escape($redirect) ?>" />
            <input type="text" class="regular-text" id="license_key" name="license_key" /> <input type="submit" value="Activate" />
        </form>
        <?php if ($_GET['m']): ?>
        <p style="color:red;"><?php echo base64_decode($_GET['m']) ?></p>
        <?php endif ?>
    </div>
<?php
}// }}}

// ----------------------------------------------------------------------------

function wpjobads_extract_fields($data)// {{{
{
    $extract = array();
    $fields = array('title','category', 'type','description','how_to_apply','location','zipcode','company_name','company_url','contact_name','contact_email');
    foreach ($fields as $field) {
        $extract[$field] = trim($data['wpjobads_' . $field]);
    }
    return $extract;
}// }}}

function wpjobads_php4_fix()// {{{
{
    global $wp_the_query, $wp_query, $post;
    $wp_the_query->queried_object->post_title = $wp_query->posts[0]->post_title = $post->post_title;
    $wp_the_query->queried_object->post_content = $wp_query->posts[0]->post_content = $post->post_content;
}// }}}

function wpjobads_page_handler()// {{{
{
    global $wpdb;
    global $wp, $wp_query, $wp_the_query;
    global $post;

    if (!$post) $post =& get_post($id = 0);
    if (!$post) return;

    $wpjobads_options = get_option('wpjobads_options');

    if (!isset($post->ID) or (intval($post->ID) != $wpjobads_options['post_id'])) return;

    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_POST = stripslashes_deep($_POST);
        if ($_POST['wpjobads-action'] == 'postjob' and $wpjobads_options['enable_frontend']) {
            $job = wpjobads_extract_fields($_POST);
            // set defaults
            $job['ad_duration']  = $wpjobads_options['duration'];
            $job['ad_currency']  = $wpjobads_options['currency'];
            $job['ad_price']     = $wpjobads_options[$job['type'] . '_price'];
            $job['ad_paid']      = $job['ad_price'] == '0' ? 1 : 0;
            $job['ad_approved']  = $wpjobads_options['auto_approve'] == '1' ? 1 : 0;
            $job['ad_published'] = 1;
            if (wpjobads_insert_job($job)) {
                $job['id'] = $wpdb->insert_id;
                $job['expired'] = $wpdb->get_var("SELECT `expired` FROM $table_job WHERE `id` = " . $job['id']);
                if (!empty($wpjobads_options['email_notification'])) {
                    wpjobads_send_notification_email($job);
                }
                if ($wpjobads_options['auto_approve'] == 1) {
                    wpjobads_log('Job #' . $job['id'] . ' - "' . $job['title'] . '" has been inserted to the database auto-approved.');
                    if ($job['ad_paid'] == 1) {
                        wpjobads_send_publish_email($job);
                        wp_redirect(wpjobads_get_permalink('action=auto-approved&job_id=' . $job['id']));
                    } else {
                        if ($wpjobads_options['force_payment_email']) {
                            wpjobads_send_payment_email($job);
                        }
                        wp_redirect(wpjobads_get_permalink('action=paypal&job_id=' . $job['id']));
                    }
                } else {
                    wpjobads_log('Job #' . $job['id'] . ' - "' . $job['title'] . '" has been inserted to the database, awaiting for approval.');
                    if ($job['ad_paid'] == 1) {
                        wp_redirect(wpjobads_get_permalink('action=waiting-approval'));
                    } else {
                        wp_redirect(wpjobads_get_permalink('action=waiting-approval&pay=1'));
                    }
                }
            } else {
                $description = apply_filters('the_content', $wpjobads_options['description']);
                remove_filter('the_content', 'wptexturize');
                remove_filter('the_content', 'wpautop');

                $post->post_title = $wpjobads_options['title'];

                if (!$wpjobads_options['enable_frontend']) {
                    $post->post_content = __('Job posting is currently disabled. Please check back later.', 'wpjobads');
                } else {
                    $error = __('<p>An error occured while posting your job ad. <strong>All fields are required except for zipcode and company URL</strong>. Please check your submission and try again. If the problem persists try contacting the administrator.</p>', 'wpjobads');
                    $post->post_content = $error . $description . wpjobads_postjob_form(wpjobads_extract_fields($_POST));
                }

                add_filter('wp_title', create_function('$title', "return \$title;"), 10);
                wpjobads_php4_fix();
            }
            return;
        }

        if ($_POST['wpjobads-action'] == 'preview') {
            $preview = wpjobads_extract_fields($_POST);
            if (wpjobads_valid_job($preview)) {
                $preview['title'] = sprintf(__('Preview: %s'), $preview['title']);
                $post->post_title = $preview['title'];
                $post->post_content = wpjobads_preview(wpjobads_extract_fields($_POST)) . apply_filters('the_content', wpjobads_view_job($preview)) . wpjobads_preview(wpjobads_extract_fields($_POST));
            } else {
                $post->post_title = $wpjobads_options['title'];
                if (!$wpjobads_options['enable_frontend']) {
                    $post->post_content = __('Job posting is currently disabled. Please check back later.', 'wpjobads');
                } else {
                    $error = __('<p>An error occured while posting your job ad. <strong>All fields are required except for zipcode and company URL</strong>. Please check your submission and try again. If the problem persists try contacting the administrator.</p>', 'wpjobads');
                    $post->post_content = $error . apply_filters('the_content', $wpjobads_options['description']) . wpjobads_postjob_form(wpjobads_extract_fields($_POST));
                }
            }
            remove_filter('the_content', 'wptexturize');
            remove_filter('the_content', 'wpautop');
            add_filter('wp_title', create_function('$title', "return \$title;"), 10);
            wpjobads_php4_fix();
            return;
        }

        if (isset($_POST['txn_id'])) {
            wpjobads_paypal_ipn();
            return;
        }
    }

    if (!defined('WPJOBADS_TITLE_SEPARATOR')) define('WPJOBADS_TITLE_SEPARATOR', ' &raquo; ');

    if (isset($_GET['job_id']) and !isset($_GET['action'])) {
        $strict = $wpjobads_options['viewable_expired_ads'] == 1 ? false : true;
        if (wpjobads_job_is_viewable(intval($_GET['job_id']), $strict)) {
            $job = wpjobads_get_job(intval($_GET['job_id']));
            $post->post_title = $job['title'];
            $post->post_content = wpjobads_view_job($job);
        } else {
            $post->post_title = __('Job Not Found.', 'wpjobads');
            $post->post_content = '<p>' . __('<p>The job you are looking for does not exist.</p>', 'wpjobads') . '</p>';
        }
        add_filter('wp_title', create_function('$title', "return \$title;"), 10);
        wpjobads_php4_fix();
        return;
    }

    if (isset($_GET['action']) and $_GET['action'] == 'postjob') {
        $description = apply_filters('the_content', $wpjobads_options['description']);
        remove_filter('the_content', 'wptexturize');
        remove_filter('the_content', 'wpautop');

        $job['category'] = (isset($_GET['cat_ID'])) ? $_GET['cat_ID'] : $wpjobads_options['default_category'];
        $post->post_title = $wpjobads_options['title'];

        if (!$wpjobads_options['enable_frontend']) {
            $post->post_content = '<p>' . __('Job posting is currently disabled. Please check back later.', 'wpjobads') . '</p>';
        } else {
            $post->post_content = $description . wpjobads_postjob_form($job);
        }

        add_filter('wp_title', create_function('$title', "return \$title;"), 10);
        wpjobads_php4_fix();
        return;
    }

    if (isset($_GET['action']) and $_GET['action'] == 'waiting-approval') {
        $post->post_title = __('Your job ad is waiting for approval.', 'wpjobads');
        if ($_GET['pay'] == 1) {
            $post->post_content = '<p>' . __('Your ad will be reviewed by one of our administrators. You will receive an email with payment instructions once your ad has been approved. Thank you for your patience.', 'wpjobads') . '</p>';
        } else {
            $post->post_content = '<p>' . __('Your ad will be reviewed by one of our administrators. Thank you for your patience.', 'wpjobads') . '</p>';
        }
        add_filter('wp_title', create_function('$title', "return \$title;"), 10);
        wpjobads_php4_fix();
        return;
    }

    if (isset($_GET['action']) and $_GET['action'] == 'auto-approved') {
        $job = wpjobads_get_job(intval($_GET['job_id']));
        $post->post_title = $wpjobads_options['title'];
        $post->post_content = '<p>' . sprintf(__('Your job ad has been published at <a href="%1$s">%2$s</a>', 'wpjobads'), wpjobads_get_permalink('job_id=' . $_GET['job_id']), $job['title']) . '</p>';
        add_filter('wp_title', create_function('$title', "return \$title;"), 10);
        wpjobads_php4_fix();
        return;
    }

    if (isset($_GET['action']) and $_GET['action'] == 'paypal' and isset($_GET['job_id'])) {
        if (wpjobads_job_paid(intval($_GET['job_id']))) {
            $post->post_title = __('Pay with PayPal', 'wpjobads');
            $post->post_content = '<p>' . __('This ad has already been paid.', 'wpjobads') . '</p>';
        } else {
            $job = wpjobads_get_job(intval($_GET['job_id']));
            $post->post_title = __('Pay with PayPal', 'wpjobads');
            $post->post_content = '<p>' . $wpjobads_options['terms'] . '</p>' . wpjobads_paypal_form($job);
        }
        add_filter('wp_title', create_function('$title', "return \$title;"), 10);
        wpjobads_php4_fix();
        return;
    }

    if (isset($_GET['action']) and $_GET['action'] == 'paypal-return') {
        $post->post_title = __('Your payment is being processed.', 'wpjobads');
        $post->post_content = wpjobads_paypal_return();
        add_filter('wp_title', create_function('$title', "return \$title;"), 10);
        wpjobads_php4_fix();
        return;
    }

    if (isset($_GET['search'])) {
        $post->post_title = $wpjobads_options['title'];
        add_filter('wp_title', create_function('$title', "return \$title;"), 10);
        $post->post_content = wpjobads_job_search($_GET['search']);
        wpjobads_php4_fix();
        return;
    }

    if (isset($_GET['jobfeed']) and $_GET['jobfeed'] == 'rss2') {
        $cat_ID = isset($_GET['cat_ID']) ? intval($_GET['cat_ID']) : null;
        $cat_ID = isset($cat_ID) ? $cat_ID : $_GET['jobcat'];
        $jobtype = isset($_GET['jobtype']) ? $_GET['jobtype'] : null;
        wpjobads_rss2($cat_ID, $jobtype);
        exit;
    }

    $post->post_title = $wpjobads_options['title'];
    add_filter('wp_title', create_function('$title', "return \$title;"), 10);
    $post->post_content = wpjobads_job_listing($_GET['jobcat'], $_GET['jobtype']);
    wpjobads_php4_fix();
    return;
}// }}}

add_action('wp', 'wpjobads_page_handler');

function wpjobads_random_ad($content)// {{{
{
    $wpjobads_options = get_option('wpjobads_options');

    if (!is_single() or !$wpjobads_options['enable_random_ad']) return $content;

    global $wpdb;

    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;

    $columns = "$table_job.id, $table_job.posted, $table_job.title, $table_job.company_name, $table_job.company_url, $table_category.id AS category_id, $table_category.name AS category_name";
    $now = gmdate('Y-m-d H:i:s', time());

    $sql = "SELECT id FROM $table_job WHERE ad_approved = 1 AND ad_paid = 1 AND ad_published = 1 AND expired > '$now'";
    $job_IDs = $wpdb->get_col($sql);

    if (is_array($job_IDs) and count($job_IDs)) {
        $job_ID = $job_IDs[rand(0, count($job_IDs) - 1)];
        $sql = "SELECT $columns FROM $table_job INNER JOIN $table_category ON $table_job.category = $table_category.id WHERE $table_job.id = $job_ID";
        $job = $wpdb->get_row($sql);
    }

    if ($job) {
        ob_start();
        wpjobads_template_random_ad(array(
            'job' => $job
        ));
        $random_ad = ob_get_contents();
        ob_end_clean();
        return $content . $random_ad;
    } else {
        return $content;
    }
}// }}}

add_action('the_content', 'wpjobads_random_ad');

function wpjobads_get_edit_post_link($link)// {{{
{
    global $post;
    if (!isset($post)) $post =& get_post($id = 0);
    $wpjobads_options = get_option('wpjobads_options');
    if (isset($post->ID) and $post->ID == $wpjobads_options['post_id']) {
        if (isset($_GET['job_id']))
            return get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wpjobads-admin-jobs&amp;action=edit&amp;job_ID=' . $_GET['job_id'];
        else
            return $link;
    } else {
        return $link;
    }
}// }}}

add_filter('get_edit_post_link', 'wpjobads_get_edit_post_link', 1);

// ----------------------------------------------------------------------------

// Front-end functions

function wpjobads_get_permalink($params = '')// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    $post_id = $wpjobads_options['post_id'];
    $permalink = get_permalink($post_id);
    $parsed_url = parse_url($permalink);

    if ($params) {
        return $permalink . ($parsed_url['query'] ? '&' : '?') . $params;
    }
    return $permalink;
}// }}}

function wpjobads_selected($current, $option)// {{{
{
    if ($current == $option) echo 'selected="selected"';
}// }}}

function wpjobads_checked($current, $checked)// {{{
{
    if ($current == $checked) echo 'checked="checked"';
}// }}}

function wpjobads_view_job($job)// {{{
{
    ob_start();
    wpjobads_template_view_job(array(
        'job' => $job
    ));
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}// }}}

function wpjobads_postjob_form($job = null)// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    $categories = wpjobads_get_all_categories();
    $types = wpjobads_get_all_types();
    ob_start();
    $permalink = wpjobads_get_permalink();
    $parsed_url = parse_url($permalink);
    $path = $parsed_url['path'];
    $permalink .= ($path{strlen($path)-1} == '/') ? '#wpjobads' : '/#wpjobads';
?>
    <style>input[type="text"], textarea, select {border:1px solid #aaa;padding: 3px;background-color: #F4F4F4;}</style>
    <style>input[type="text"]:focus, textarea:focus, select:focus {background-color: #FFF;}</style>
    <style>label {font-weight: bold;}</style>
    <style>fieldset {padding: 1em;}</style>
    <form style="text-align:left;margin: 1em 0;" method="post" action="<?php echo $permalink ?>">
        <fieldset style="border: 1px solid #ccc;">
            <legend><?php _e('Job Details', 'wpjobads') ?></legend>

            <label for="wpjobads_title"><?php _e('Job title', 'wpjobads') ?></label><br/>
            <input type="text" id="wpjobads_title" name="wpjobads_title" size="40" value="<?php echo attribute_escape($job['title']) ?>" tabindex="1" /><br/><br/>

            <label for="wpjobads_category"><?php _e('Job category', 'wpjobads') ?></label><br/>
            <select style="width: 300px;" id="wpjobads_category" name="wpjobads_category" tabindex="2">
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo attribute_escape($cat['id']) ?>" <?php wpjobads_selected($job['category'], $cat['id']) ?>><?php echo attribute_escape($cat['name']) ?></option>
                <?php endforeach ?>
            </select><br/><br/>

            <label for="wpjobads_type"><?php _e('Job type', 'wpjobads') ?></label><br/>
            <select style="width: 300px;" id="wpjobads_type" name="wpjobads_type" tabindex="3">
                <?php foreach ($types as $type): ?>
                <option value="<?php echo attribute_escape($type['id']) ?>" <?php wpjobads_selected($job['type'], $type['id']) ?>><?php echo attribute_escape($type['name']) ?></option>
                <?php endforeach ?>
            </select><br/><br/>

            <label for="wpjobads_description"><?php _e('Description', 'wpjobads') ?></label><br/>
            <textarea id="wpjobads_description" name="wpjobads_description" rows="8" cols="50" tabindex="3"><?php echo attribute_escape($job['description']) ?></textarea><br/><br/>

            <label for="wpjobads_how_to_apply"><?php _e('How to apply', 'wpjobads') ?></label><br/>
            <textarea id="wpjobads_how_to_apply" name="wpjobads_how_to_apply" rows="4" cols="50" tabindex="4"><?php echo attribute_escape($job['how_to_apply']) ?></textarea><br/><br/>

            <label for="wpjobads_location"><?php _e('Job location', 'wpjobads') ?></label><br/>
            <input type="text" id="wpjobads_location" name="wpjobads_location" size="40" value="<?php echo attribute_escape($job['location']) ?>" tabindex="5" /><br/><br/>

            <label for="wpjobads_zipcode"><?php _e('Zipcode (optional)', 'wpjobads') ?></label><br/>
            <input type="text" id="wpjobads_zipcode" name="wpjobads_zipcode" size="10" value="<?php echo attribute_escape($job['zipcode']) ?>" tabindex="6" /><br/><br/>

        </fieldset>
        <br/>
        <fieldset style="border: 1px solid #ccc;">
            <legend><?php _e('Employer Details', 'wpjobads') ?></legend>

            <label for="wpjobads_company_name"><?php _e('Company name', 'wpjobads') ?></label><br/>
            <input type="text" id="wpjobads_company_name" name="wpjobads_company_name" size="40" value="<?php echo attribute_escape($job['company_name']) ?>" tabindex="7" /><br/><br/>

            <label for="wpjobads_company_url"><?php _e('Company URL (optional)', 'wpjobads') ?></label><br/>
            <input type="text" id="wpjobads_company_url" name="wpjobads_company_url" size="40" value="<?php echo attribute_escape($job['company_url']) ?>" tabindex="8" /><br/><br/>

            <label for="wpjobads_contact_name"><?php _e('Contact name', 'wpjobads') ?></label><br/>
            <input type="text" id="wpjobads_contact_name" name="wpjobads_contact_name" size="40" value="<?php echo attribute_escape($job['contact_name']) ?>" tabindex="9" /><br/><br/>

            <label for="wpjobads_contact_email"><?php _e('Contact email', 'wpjobads') ?></label><br/>
            <input type="text" id="wpjobads_contact_email" name="wpjobads_contact_email" size="40" value="<?php echo attribute_escape($job['contact_email']) ?>" tabindex="10" /><br/><br/>
        </fieldset>
        <br/>
        <fieldset style="border: 1px solid #ccc;">
            <legend><?php _e('Terms &amp; Conditions', 'wpjobads') ?></legend>
            <?php echo wpautop(wptexturize($wpjobads_options['terms'])) ?>
        </fieldset>
        <input type="hidden" name="wpjobads-action" value="preview" />
        <p><input id="submit" type="submit" value="<?php echo attribute_escape(__('Post new job', 'wpjobads')) ?>" tabindex="11" /></p>
    </form>
<?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}// }}}

function wpjobads_job_listing($cat = null, $type = null)// {{{
{
    global $wpdb;

    $wpjobads_options = get_option('wpjobads_options');

    $jobs = wpjobads_get_all_jobs($cat, $type);

    $date_format = $wpjobads_options['date_format'];
    $gmt_offset = intval(get_option('gmt_offset')) * 3600;

    $invite = empty($wpjobads_options['invite']) ? attribute_escape(_('Post a job and find the right person')) : attribute_escape($wpjobads_options['invite']);
    $widget_invite = empty($wpjobads_options['widget_invite']) ? attribute_escape(__('Post new job', 'wpjobads')) : attribute_escape($wpjobads_options['widget_invite']);

    ob_start();
    wpjobads_template_job_listing(array(
        'enable_frontend' => $wpjobads_options['enable_frontend'],
        'jobs' => $jobs,
        'cat_ID' => $cat,
        'invite' => $invite,
        'widget_invite' => $widget_invite,
        'date_format' => $date_format,
        'gmt_offset' => $gmt_offset
    ));
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}// }}}

function wpjobads_job_search($query) // {{{
{
    global $wpdb;

    $wpjobads_options = get_option('wpjobads_options');

    $jobs = wpjobads_search_all_jobs($query);

    $date_format = $wpjobads_options['date_format'];
    $gmt_offset = intval(get_option('gmt_offset')) * 3600;

    $invite = empty($wpjobads_options['invite']) ? attribute_escape(_('Post a job and find the right person')) : attribute_escape($wpjobads_options['invite']);
    $widget_invite = empty($wpjobads_options['widget_invite']) ? attribute_escape(__('Post new job', 'wpjobads')) : attribute_escape($wpjobads_options['widget_invite']);

    ob_start();
    wpjobads_template_job_listing(array(
        'enable_frontend' => $wpjobads_options['enable_frontend'],
        'jobs' => $jobs,
        'cat_ID' => $cat,
        'invite' => $invite,
        'widget_invite' => $widget_invite,
        'date_format' => $date_format,
        'gmt_offset' => $gmt_offset
    ));
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}// }}}

function wpjobads_rss2($cat_ID = null, $jobtype = null) // {{{
{ 
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
    $columns = "$table_job.id, $table_job.posted, $table_job.title, $table_job.description, $table_job.how_to_apply, $table_job.company_name, $table_job.location, $table_job.zipcode, $table_job.company_url, $table_category.id AS category_id, $table_category.name AS category_name, $table_job.type";
    $now = gmdate('Y-m-d H:i:s', time());

    $where_jobs = array();
    $where_count = '';
    if ($cat_ID) {
        $cat_ID = intval($cat_ID);
        $where_jobs[] = "$table_category.id = $cat_ID";
    }

    if ($jobtype) {
        $jobtype = $wpdb->escape($jobtype);
        $where_jobs[] = "$table_job.`type` = '$jobtype'";
        $where_count = "AND $table_job.`type` = '$jobtype'";
    }
    $where_jobs = empty($where_jobs) ? '' : ('AND ' . implode(' AND ', $where_jobs));

    $jobs_sql = "SELECT $columns FROM $table_job INNER JOIN $table_category ON $table_job.category = $table_category.id WHERE ad_approved = 1 AND ad_paid = 1 AND ad_published = 1 AND (expired > '$now' OR ad_duration = -1) $where_jobs ORDER BY posted DESC";
    $jobs = $wpdb->get_results($jobs_sql, ARRAY_A);
    $posted_sql = "SELECT MAX($table_job.posted) AS posted FROM $table_job WHERE ad_approved = 1 AND ad_paid = 1 AND ad_published = 1 AND (expired > '$now' OR ad_duration = -1) $where_count ORDER BY posted DESC";
    $posted = $wpdb->get_var($posted_sql);

    $wpjobads_options = get_option('wpjobads_options');

    header('Content-Type: text/xml; charset="' . get_option('blog_charset') . '"', true);
?>
<?php echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
>

<channel>
    <title><?php echo attribute_escape($wpjobads_options['title']) ?></title>
    <link><?php echo wpjobads_get_permalink() ?></link>
    <description><?php echo attribute_escape($wpjobads_options['description']) ?></description>
    <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $posted, false); ?></pubDate>
    <language><?php echo get_option('rss_language'); ?></language>
    <sy:updatePeriod>hourly</sy:updatePeriod>
    <sy:updateFrequency>1</sy:updateFrequency>

    <?php foreach ($jobs as $job): ?>
    <item>
        <title><?php echo attribute_escape($job['title']) ?></title>
        <link><?php echo attribute_escape(wpjobads_get_permalink('job_id='.$job['id'])) ?></link>
        <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $job['posted'], false); ?></pubDate>
        <category><![CDATA[<?php echo html_entity_decode($job['category_name']) ?>]]></category>
        <guid isPermaLink="false"><?php echo attribute_escape(wpjobads_get_permalink('job_id='.$job['id'])) ?></guid>
        <content:encoded><![CDATA[<?php echo html_entity_decode(wpjobads_view_job($job)) ?>]]></content:encoded>
    </item>
    <?php endforeach ?>

</channel>
</rss>
<?php
}// }}}

// ----------------------------------------------------------------------------

// HTTP & Net functions

function wpjobads_http_request($method, $url, $data = '', $headers = array(), $timeout = 5)// {{{
{
    $url = parse_url($url);
    if (!$url['path']) $url['path'] = '/';
    if ($url['query']) $url['path'] .= '?' . $url['query'];
    $request = strtoupper($method) . ' ' . $url['path'] . " HTTP/1.0\r\n";
    $headers['Host'] = $url['host'];
    $headers['Content-Length'] = strlen($data);
    foreach ($headers as $name => $value) {
        $request .= $name . ': ' . $value . "\r\n";
    }
    $request .= "\r\n";
    $request .= $data;
    $response = false;
    if (!isset($url['port'])) $url['port'] = 80;
    if (false != ($http = @fsockopen($url['host'], $url['port'], $errno, $errstr, $timeout)) && is_resource($http)) {
        fwrite($http, $request);
        while (!feof($http))
            $response .= fgets($http, 1160); // One TCP-IP packet
        fclose($http);
        $response = explode("\r\n\r\n", $response, 2);
    }
    return $response;
}// }}}

function wpjobads_http_get($url, $data = '', $headers = array(), $timeout = 5)// {{{
{
    if ($data) $url .= '?' . $data;
    return wpjobads_http_request('GET', $url, '', $headers, $timeout);
}// }}}

function wpjobads_http_post($url, $data = '', $headers = array(), $timout = 5)// {{{
{
    if (!isset($headers['Content-Type'])) {
        $headers = array_merge($headers, array('Content-Type' => 'application/x-www-form-urlencoded'));
    }
    return wpjobads_http_request('POST', $url, $data, $headers, $timeout);
}// }}}

function wpjobads_paypal_verify($data = '', $headers = array(), $timeout = 30)// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    $url = parse_url($wpjobads_options['paypal_verification_url']);
    if (!$url['path']) $url['path'] = '/';
    if ($url['query']) $url['path'] .= '?' . $url['query'];
    $request = 'POST ' . $url['path'] . " HTTP/1.0\r\n";
    $headers['Host'] = $url['host'];
    $headers['Content-Length'] = strlen($data);
    foreach ($headers as $name => $value) {
        $request .= $name . ': ' . $value . "\r\n";
    }
    $request .= "\r\n";
    $request .= $data;
    $response = false;
    if (!isset($url['port'])) $url['port'] = 443;
    if (false != ($http = fsockopen($url['scheme'] . '://' . $url['host'], $url['port'], $errno, $errstr, $timeout)) && is_resource($http)) {
        fwrite($http, $request);
        while (!feof($http))
            $response .= fgets($http, 1160); // One TCP-IP packet
        fclose($http);
        $response = explode("\r\n\r\n", $response, 2);
    } else {
        wpjobads_log('HTTP unable to open socket to ' . $wpjobads_options['paypal_verification_url'] . ' with errno = ' . $errno . ' and errstr = ' . $errstr);
    }
    return $response;
}// }}}

function wpjobads_check_update($plugin_file, $plugin_data, $context)// {{{
{
    global $wp_version;

    //if (!empty($context) and $context != 'active') return false;
    if ($plugin_data['Name'] != 'WPJobAds') return false;
    if (!is_callable('fsockopen')) return false;

    $wpjobads_options = get_option('wpjobads_options');

    if (!WPJOBADS_FORCE_CHECK_UPDATE and (WPJOBADS_CHECK_UPDATE_PERIOD > (time() - $wpjobads_options['last_checked']))) {
        if (version_compare($wpjobads_options['new_version'], $wpjobads_options['version']) == 1) {
            wpjobads_notify_update($plugin_data['Name'], $wpjobads_options['update_url'], $wpjobads_options['new_version']);
            return true;
        }
        return false;
    }

    $request = 'v='.urlencode($plugin_data['Version']).'&u='.urlencode(get_bloginfo('url'));
    if ($wpjobads_options['license_key']) $request .= '&l='.urlencode(md5($wpjobads_options['license_key']));

    $headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset');
    $headers['User-Agent']   = 'WordPress/' . $wp_version . '; ' . get_bloginfo('url');
    list($header, $response) = wpjobads_http_post(WPJOBADS_CHECK_UPDATE_URL, $request, $headers, 30);
    $response = unserialize($response);

    if (!$response || !$response['v'] || !$response['u']) {
        return false;
    }

    if (version_compare($response['v'], $wpjobads_options['version']) == 1) {
        $wpjobads_options['last_checked'] = time();
        $wpjobads_options['new_version']  = $response['v'];
        $wpjobads_options['update_url']   = $response['u'];
        update_option('wpjobads_options', $wpjobads_options);
        wpjobads_notify_update($plugin_data['Name'], $wpjobads_options['update_url'], $wpjobads_options['new_version']);
        return true;
    }
    return false;
}// }}}

add_action('after_plugin_row', 'wpjobads_check_update', 10, 3);

function wpjobads_notify_update($plugin_name, $update_url, $new_version)// {{{
{
    echo '<tr><td colspan="5" class="plugin-update">';
    printf(__('There is a new version of %1$s available. <a href="%2$s">Download version %3$s here</a> <em>automatic upgrade unavailable for this plugin</em>.'),
        $plugin_name, $update_url, $new_version);
    echo "</td></tr>";
}// }}}

// ----------------------------------------------------------------------------

// Administrative pages

function wpjobads_install_page()// {{{
{
    global $user_ID;

    $post['post_type']      = 'page';
    $post['post_title']     = __('Jobs', 'wpjobads');
    $post['post_name']      = __('jobs', 'wpjobads');
    $post['post_content']   = __('This post was auto generated by WPJobAds during installation. Please do not delete this page.', 'wpjobads');
    $post['post_excerpt']   = '';
    $post['post_parent']    = 0;
    $post['to_ping']        = '';
    $post['post_author']    = $user_ID;
    $post['post_status']    = 'publish';
    $post['comment_status'] = 'closed';
    $post['ping_status']    = 'closed';

    $post_ID = wp_insert_post($post);
    if (is_wp_error($post_ID))
        return $post_ID;

    if (empty($post_ID))
        return 0;

    return $post_ID;
}// }}}

function wpjobads_install()// {{{
{
    global $wpdb;

    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_category'") != $table_category) {
        $sql = "CREATE TABLE $table_category (
                  id int(4) unsigned NOT NULL auto_increment,
                  name varchar(255) NOT NULL,
                  priority int(10) unsigned NOT NULL default '10',
                  job_count bigint(20) unsigned NOT NULL default '0',
                  PRIMARY KEY (id)
                );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        $sql = "INSERT INTO $table_category (name)
                   VALUES('" . $wpdb->escape(__('Miscellaneous', 'wpjobads')) . "')
                  ";
        $result = $wpdb->query($sql);
    }

    $table_job = $wpdb->prefix . WPJOBADS_JOB;

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_job'") != $table_job) {
        $sql = "CREATE TABLE $table_job (
                  id bigint(20) unsigned NOT NULL auto_increment,
                  posted datetime NOT NULL default '0000-00-00 00:00:00',
                  modified datetime NOT NULL default '0000-00-00 00:00:00',
                  expired datetime NOT NULL default '0000-00-00 00:00:00',
                  title varchar(255) NOT NULL default '',
                  category int(4) unsigned NOT NULL default '1',
                  `type` ENUM('fulltime','parttime','freelance','internship') NOT NULL DEFAULT 'fulltime',
                  description text NOT NULL,
                  how_to_apply text NOT NULL,
                  location varchar(100) NOT NULL default '',
                  zipcode varchar(10) NOT NULL default '',
                  company_name varchar(100) NOT NULL default '',
                  company_url varchar(100) NOT NULL default '',
                  contact_name varchar(100) NOT NULL default '',
                  contact_email varchar(100) NOT NULL default '',
                  ad_duration int(4) NOT NULL default '0',
                  ad_currency char(3) NOT NULL default 'USD',
                  ad_price double NOT NULL default '0',
                  ad_paid BOOLEAN NOT NULL default '0',
                  ad_approved BOOLEAN NOT NULL default '0',
                  ad_published BOOLEAN NOT NULL default '1',
                  ip_address varchar(100) NOT NULL default '',
                  txn_id varchar(17),
                  PRIMARY KEY (id)
                );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    $wpjobads_options = get_option('wpjobads_options');
    if (!empty($wpjobads_options) and $wpjobads_options['post_id']) {
        $post_ID = $wpjobads_options['post_id'];
    } else {
        $post_ID = wpjobads_install_page();
    }

    $default_options = array(
        'post_id' => $post_ID,
        'version' => WPJOBADS_VERSION,
        'release_date' => WPJOBADS_RELEASE_DATE,
        'last_checked' => time() - WPJOBADS_CHECK_UPDATE_PERIOD,
        'new_version' => WPJOBADS_VERSION,
        'update_url' => WPJOBADS_UPDATE_URL,
        'license_key' => '',
        ###
        'title' => __('Job Board', 'wpjobads'),
        'description' => '',
        'invite' => __('Post a job and find the right person'),
        'enable_frontend' => 0,
        'auto_approve' => 0,
        'force_payment_email' => 1,
        'duration' => 30,
        'currency' => 'USD',
        'fulltime_price' => 0,
        'parttime_price' => 0,
        'freelance_price' => 0,
        'internship_price' => 0,
        'paypal_email' => '',
        'paypal_url' => 'https://www.paypal.com/cgi-bin/webscr',
        'paypal_verification_url' => 'ssl://www.paypal.com:443/cgi-bin/webscr',
        'date_format' => 'n/j',
        'terms' => '',
        'payment_email_subject' => 'Job Board Notification',
        'payment_email_message' => '',
        'publish_email_subject' => 'Job Board Notification',
        'publish_email_message' => '',
        'email_from_name' => 'WPJobAds',
        'email_from' => 'wpjobads@example.com',
        'email_notification' => '',
        'notification_email_subject' => 'Job Ad Submission Notification',
        'notification_email_message' => '',
        'viewable_expired_ads' => 0,
        'enable_random_ad' => 1,
        ###
        'default_category' => 1,
        'widget_title' => __('Job Board', 'wpjobads'),
        'widget_invite' => __('Post new job', 'wpjobads')
    );

    foreach ($default_options as $name => $value) {
        if (!isset($wpjobads_options[$name])) {
            $wpjobads_options[$name] = $value;
        }
    }

    $wpjobads_options['version'] = WPJOBADS_VERSION;
    $wpjobads_options['release_date'] = WPJOBADS_RELEASE_DATE;

    if (version_compare(WPJOBADS_VERSION, $wpjobads_options['new_version']) == 1) {
        $wpjobads_options['last_checked'] = time();
        $wpjobads_options['new_version'] = WPJOBADS_VERSION;
    }

    update_option('wpjobads_options', $wpjobads_options);
}// }}}

register_activation_hook(__FILE__, 'wpjobads_install');

function wpjobads_uninstall()// {{{
{
    global $wpdb;
    $wpjobads_options = get_option('wpjobads_options');
    $wpdb->query('DROP TABLE ' . $wpdb->prefix . WPJOBADS_CATEGORY);
    $wpdb->query('DROP TABLE ' . $wpdb->prefix . WPJOBADS_JOB);
    $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'posts WHERE ID = ' . $wpjobads_options['post_id']);
    $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'postmeta WHERE post_id = ' . $wpjobads_options['post_id']);
    delete_option('wpjobads_options');
    deactivate_plugins('wpjobads/wpjobads.php');
    wp_redirect('plugins.php?deactivate=true');
}// }}}

function wpjobads_add_admin_pages()// {{{
{
    global $wpdb;
    global $plugin_page, $pagenow;

    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $unapproved = intval($wpdb->get_var("SELECT COUNT(id) AS unapproved FROM $table_job WHERE ad_approved = 0"));

    $submenu = array();

    add_menu_page(__('WPJobAds for WordPress', 'wpjobads'), __('WPJobAds', 'wpjobads'), 10, __FILE__, 'wpjobads_admin_index');
    $submenu['wpjobads_admin_load_jobs'] = add_submenu_page(__FILE__, __('WPJobAds Listings', 'wpjobads'), __('Jobs', 'wpjobads'), 10, 'wpjobads-admin-jobs', 'wpjobads_admin_jobs');
    if ($unapproved or ($_GET['page'] == 'wpjobads-admin-approvals' and isset($_GET['message'])))
        $submenu['wpjobads_admin_load_approvals'] = add_submenu_page(__FILE__, __('WPJobAds Approvals', 'wpjobads'), sprintf(__('Awaiting Approval (%d)', 'wpjobads'), $unapproved), 10, 'wpjobads-admin-approvals', 'wpjobads_admin_approvals');
    $submenu['wpjobads_admin_load_categories'] = add_submenu_page(__FILE__, __('WPJobAds Categories', 'wpjobads'), __('Categories', 'wpjobads'), 10, 'wpjobads-admin-categories', 'wpjobads_admin_categories');
    $submenu['wpjobads_admin_load_options'] = add_submenu_page(__FILE__, __('WPJobAds Options', 'wpjobads'), __('Options', 'wpjobads'), 10, 'wpjobads-admin-options', 'wpjobads_admin_options');
    $submenu['wpjobads_admin_load_uninstall'] = add_submenu_page(__FILE__, __('WPJobAds Uninstall', 'wpjobads'), __('Uninstall', 'wpjobads'), 10, 'wpjobads-admin-uninstall', 'wpjobads_admin_uninstall');

    foreach ($submenu as $handler => $page_hook) {
        if ($page_hook == get_plugin_page_hook($plugin_page, $pagenow)) {
            add_action('load-' . $page_hook, $handler);
        }
    }
}// }}}

add_action('admin_menu', 'wpjobads_add_admin_pages');

function wpjobads_submenu_fix()// {{{
{
    global $submenu;
    if (array_key_exists('wpjobads/wpjobads.php', $submenu)) {
        $wpjobads = $submenu['wpjobads/wpjobads.php'][0];
        $wpjobads[0] = __('Overview', 'wpjobads');
        $wpjobads[3] = __('WPJobAds Overview', 'wpjobads');
        $submenu['wpjobads/wpjobads.php'][0] = $wpjobads;
    }
}// }}}

add_action('admin_head', 'wpjobads_submenu_fix');

function wpjobads_admin_load_approvals()// {{{
{
    if ($_POST['action'] == 'approve') return wpjobads_admin_approve_job();
}// }}}

function wpjobads_admin_approvals()// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    if (empty($wpjobads_options['license_key'])) return wpjobads_license_form('wpjobads-admin-approvals');

    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $unapproved = $wpdb->get_results("SELECT * FROM $table_job WHERE ad_approved = 0 ORDER BY posted ASC", ARRAY_A);
    $gmt_offset = intval(get_option('gmt_offset')) * 3600;

    $messages[1] = __('Job approved.', 'wpjobads');
    $messages[2] = __('Job not approved.', 'wpjobads');
?>
    <?php if (isset($_GET['message'])) : ?>
    <div id="message" class="updated fade"><p><?php echo $messages[$_GET['message']]; ?></p></div>
    <?php endif; ?>

    <div class="wrap">
        <h2><?php _e('Approval Queue', 'wpjobads') ?></h2>
        <?php if (empty($unapproved)): ?>
        <p><?php _e('No jobs found.', 'wpjobads') ?></p>
        <?php else: ?>
        <ol class="commentlist">
        <?php foreach ($unapproved as $job): ?>
            <li>
                <p><strong><?php echo $job['title'] ?></strong> | <a href="<?php echo $job['company_url'] ?>"><?php echo $job['company_name'] ?></a> | <a href="mailto:<?php echo $job['contact_email'] ?>"><?php echo $job['contact_email'] ?></a> | <?php _e('IP:', 'wpjobads') ?> <a href="http://ws.arin.net/cgi-bin/whois.pl?queryinput=<?php echo urlencode($job['ip_address']) ?>"><?php echo $job['ip_address'] ?></a></p>
                <p><small><?php echo date('M d, g:i A', strtotime($job['posted']) + $gmt_offset) ?> &#8212; [ <a id="showlink-<?php echo $job['id'] ?>" href="javascript:wpjobads_toggle(<?php echo $job['id'] ?>)"><?php _e('Show', 'wpjobads') ?></a> | <a href="admin.php?page=wpjobads-admin-jobs&amp;action=edit&amp;job_ID=<?php echo attribute_escape($job['id']) ?>">Edit</a> | <a href="<?php echo wp_nonce_url('admin.php?page=wpjobads-admin-jobs&amp;action=delete&amp;job_ID=' . $job['id'], 'delete-job_' . $job['id'])?>">Delete</a> ]</small></p>
                <div class="jobcontent" id="jobcontent-<?php echo $job['id'] ?>">
                    <address><?php echo $job['location'] ?> <?php echo $job['zipcode'] ?></address><br/>
                    <?php echo apply_filters('the_content', $job['description']) ?>
                    <h4><?php _e('Interested?', 'wpjobads') ?></h4>
                    <?php echo apply_filters('the_content', $job['how_to_apply']) ?>
                </div>
                <form method="post" action="admin.php?page=wpjobads-admin-approvals&amp;action=approve">
                    <input type="hidden" name="job_ID" value="<?php echo attribute_escape($job['id']) ?>" />
                    <?php wp_nonce_field('approve-job_' . $job['id']) ?>
                    <input type="hidden" name="action" value="approve" />
                    <p class="submit" style="text-align: left;"><input type="submit" class="submit" value="<?php _e('Approve', 'wpjobads') ?>"></p>
                </form>
            </li>
        <?php endforeach ?>
        </ol>

        <script type="text/javascript">
        //<![CDATA[
            function wpjobads_toggle(id){
                var div = $('jobcontent-' + id);
                var link = $('showlink-' + id);
                if (div.style.display == 'none') {
                    div.style.display = 'block';
                    link.innerHTML = '<?php _e('Hide', 'wpjobads') ?>';
                } else {
                    div.style.display = 'none';
                    link.innerHTML = '<?php _e('Show', 'wpjobads') ?>';
                }
            }
            var wpjobads_contents = document.getElementsByClassName('jobcontent');
            for (i = 0; i < wpjobads_contents.length; i++) wpjobads_contents[i].style.display = 'none';
        //]]>
        </script>
        <?php endif ?>
    </div>
<?php
}// }}}

function wpjobads_admin_approve_job()// {{{
{
    $_POST = stripslashes_deep($_POST);
    $job_ID = intval($_POST['job_ID']);
    check_admin_referer('approve-job_' . $job_ID);
    $job = wpjobads_get_job($job_ID);
    if (wpjobads_set_approved($job)) {
        if (intval($job['ad_price']) == 0) {
            wpjobads_send_publish_email($job);
        } else {
            wpjobads_send_payment_email($job);
        }
        wpjobads_log('Job #' . $job['id'] . ' - "' . $job['title'] . '" is set to "approved" and an email has been sent to ' . $job['contact_email']);
        wp_redirect('admin.php?page=wpjobads-admin-approvals&message=1');
    } else {
        wp_redirect('admin.php?page=wpjobads-admin-approvals&message=2');
    }
}// }}}

function wpjobads_set_approved($job, $ad_approved = 1)// {{{
{
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $now = time();
    $modified = $wpdb->escape(gmdate('Y-m-d H:i:s', $now));
    $updates = array();
    $updates[] = "modified = '$modified'";
    $ad_approved = intval($ad_approved) ? 1 : 0;
    $updates[] = "ad_approved = $ad_approved";
    $expired = gmdate('Y-m-d H:i:s', $now + intval($job['ad_duration']) * 86400);
    $expired = $wpdb->escape($expired);
    $updates[] = "expired = '$expired'";
    $updates = implode(', ', $updates);
    return $wpdb->query("UPDATE $table_job SET $updates WHERE id = " . $job['id']);
}// }}}

function wpjobads_set_paid($job, $txn_id, $ad_paid = 1)// {{{
{
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $now = time();
    $modified = $wpdb->escape(gmdate('Y-m-d H:i:s', $now));
    $updates = array();
    $updates[] = "modified = '$modified'";
    $ad_paid = intval($ad_paid) ? 1 : 0;
    $updates[] = "ad_paid = $ad_paid";
    $expired = gmdate('Y-m-d H:i:s', $now + intval($job['ad_duration']) * 86400);
    $expired = $wpdb->escape($expired);
    $updates[] = "expired = '$expired'";
    $txn_id = $wpdb->escape($txn_id);
    $updates[] = "txn_id = '$txn_id'";
    $updates = implode(', ', $updates);
    return $wpdb->query("UPDATE $table_job SET $updates WHERE id = " . $job['id']);
}// }}}

function wpjobads_job_paid($job_ID)// {{{
{
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $ad_paid = $wpdb->get_var("SELECT ad_paid FROM $table_job WHERE id = " . $job_ID, 0);
    return $ad_paid == 1 ? true : false;
}// }}}

function wpjobads_admin_index()// {{{
{
    global $wpdb;

    $wpjobads_options = get_option('wpjobads_options');
    if (empty($wpjobads_options['license_key'])) return wpjobads_license_form();

    $table_job = $wpdb->prefix . WPJOBADS_JOB;

    $total = intval($wpdb->get_var("SELECT COUNT(id) AS total FROM $table_job WHERE ad_approved = 1", 0));

    $age = floor((time() - strtotime($wpjobads_options['release_date'])) / (24 * 3600));
    $url = urlencode(get_bloginfo('url'));
?>
    <div class="wrap">
        <h2><?php _e('Overview', 'wpjobads') ?></h2>
        <p><?php echo sprintf(__('Your WPJobAds version is <span style="background-color: #FFF9D8; font-weight: bold;">%d</span> days old.', 'wpjobads'), intval($age)) ?></p>
        <p><?php echo sprintf(__('Visit %s to check on the latest news and updates.', 'wpjobads'), '<a href="http://www.wpjobads.com/?url='.urlencode($url).'&l='.urlencode(md5($wpjobads_options['license_key'])).'">WPJobAds.com</a>') ?></p>
    </div>
<?php
}// }}}

function wpjobads_admin_load_categories()// {{{
{
    if ($_POST['action'] == 'add') return wpjobads_admin_add_category();
    elseif ($_POST['action'] == 'update') return wpjobads_admin_update_category();
    elseif ($_GET['action'] == 'delete') return wpjobads_admin_delete_category();
}// }}}

function wpjobads_admin_categories()// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    if (empty($wpjobads_options['license_key'])) return wpjobads_license_form('wpjobads-admin-categories');

    if ($_GET['action'] == 'edit') return wpjobads_admin_edit_category();

    $categories = wpjobads_get_all_categories();

    $messages[1] = __('Category added.', 'wpjobads');
    $messages[2] = __('Category deleted.', 'wpjobads');
    $messages[3] = __('Category updated.', 'wpjobads');
    $messages[4] = __('Category not added.', 'wpjobads');
    $messages[5] = __('Category not updated.', 'wpjobads');

    $form = '<form name="addcat" id="addcat" method="post" action="admin.php?page=wpjobads-admin-categories&action=add">';
    $action = 'add';
    $nonce_action = 'add-job-category';
    $heading = __('Add Job Category', 'wpjobads');
    $submit_text = __('Add Job Category', 'wpjobads');

?>
    <?php if (isset($_GET['message'])) : ?>
    <div id="message" class="updated fade"><p><?php echo $messages[$_GET['message']]; ?></p></div>
    <?php endif; ?>

    <div class="wrap">
        <h2><?php _e('Job Categories (<a href="#addcat">add new</a>)', 'wpjobads') ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th style="text-align: center;" scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th width="90" style="text-align: center;" scope="col">Jobs</th>
                    <th width="90" style="text-align: center;" scope="col">Priority</th>
                    <th width="200" colspan="2" style="text-align: center;" scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $i => $cat): ?>
                <?php if ($i % 2): ?>
                <tr>
                <?php else: ?>
                <tr class="alternate">
                <?php endif ?>
                    <td style="text-align: center;"><?php echo attribute_escape($cat['id']) ?></td>
                    <td><?php echo attribute_escape($cat['name']) ?></td>
                    <td style="text-align: center;"><?php echo attribute_escape($cat['job_count']) ?></td>
                    <td style="text-align: center;"><?php echo attribute_escape($cat['priority']) ?></td>
                    <td><a class="edit" href="admin.php?page=wpjobads-admin-categories&amp;action=edit&amp;cat_ID=<?php echo attribute_escape($cat['id']) ?>"><?php _e('Edit', 'wpjobads') ?></a></td>
                    <?php if ($cat['id'] != intval($wpjobads_options['default_category'])): ?>
                    <td><a class="delete" href="<?php echo wp_nonce_url('admin.php?page=wpjobads-admin-categories&amp;action=delete&amp;cat_ID=' . $cat['id'], 'delete-job-category_' . $cat['id'])?>" ><?php _e('Delete', 'wpjobads') ?></a></td>
                    <?php else: ?>
                    <td style="text-align: center;"><?php _e('Default', 'wpjobads') ?></td>
                    <?php endif ?>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <?php if ( current_user_can('manage_categories') ) : ?>
    <div class="wrap">
        <p><?php printf(__('<strong>Note:</strong><br />Deleting a category does not delete the jobs in that category. Instead, jobs that were assigned to the deleted category are set to the category <strong>%s</strong>.', 'wpjobads'), wpjobads_get_catname($wpjobads_options['default_category'])) ?></p>
    </div>

    <div class="wrap">
        <h2><?php echo $heading ?></h2>
        <?php wpjobads_admin_category_form(null, $form, $action, $nonce_action, $submit_text) ?>
    </div>
    <?php endif;
}// }}}

function wpjobads_admin_category_form($category, $form, $action, $nonce_action, $submit_text)// {{{
{
    global $wp_version;
    if (is_null($category)) {
        $category = array();
        $category['id'] = '';
        $category['name'] = '';
        $category['priority'] = 1;
    }
?>
    <?php echo $form ?>
        <input type="hidden" value="<?php echo $action ?>" name="action" />
        <input type="hidden" value="<?php echo attribute_escape($category['id']) ?>" name="cat_ID"/>
        <?php wp_nonce_field($nonce_action) ?>
        <?php if (version_compare($wp_version, '2.5', '>=') == TRUE): ?>
        <table class="form-table" width="100%" cellspacing="2" cellpadding="5">
        <?php else: ?>
        <table class="editform" width="100%" cellspacing="2" cellpadding="5">
        <?php endif ?>
            <tr>
                <th scope="row" valign="top"><label for="name"><?php _e('Category name:', 'wpjobads') ?></label></th>
                <td><input type="text" class="regular-text" id="name" name="name" size="40" value="<?php echo attribute_escape($category['name']) ?>" tabindex="1" /></td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="priority"><?php _e('Priority:', 'wpjobads') ?></label></th>
                <td>
                    <input type="text" id="priority" name="priority" size="3" value="<?php echo attribute_escape($category['priority']) ?>" tabindex="2" />
                    <br/>
                    <?php _e('Determines the order of display.', 'wpjobads') ?>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php echo attribute_escape($submit_text) ?>" />
        </p>
    </form>
<?php
}// }}}

function wpjobads_admin_load_jobs()// {{{
{
    if ($_POST['action'] == 'add') return wpjobads_admin_add_job();
    elseif ($_POST['action'] == 'update') return wpjobads_admin_update_job();
    elseif ($_GET['action'] == 'delete') return wpjobads_admin_delete_job();
}// }}}

function wpjobads_admin_jobs()// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    if (empty($wpjobads_options['license_key'])) return wpjobads_license_form('wpjobads-admin-jobs');

    if ($_GET['action'] == 'edit') return wpjobads_admin_edit_job();

    global $wpdb, $wp_version;

    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $columns = 'id, posted, modified, title, category, type, company_name, company_url, ad_approved, ad_paid, ad_published, ad_currency, ad_price';

    $page = max(isset($_GET['paged']) ? intval($_GET['paged']) : 1, 1);
    $limit = WPJOBADS_ADMIN_JOB_ENTRIES;
    $offset = ($page - 1) * $limit;

    if (isset($_GET['s'])) {
        $search = $wpdb->escape($_GET['s']);
        $sql = "SELECT $columns FROM $table_job WHERE title LIKE '%$search%' ORDER BY posted DESC LIMIT $limit OFFSET $offset";
        $jobs = $wpdb->get_results($sql, ARRAY_A);
        $total = intval($wpdb->get_var("SELECT COUNT(id) AS total FROM $table_job WHERE title LIKE '%$search%' ORDER BY posted DESC", 0));
    } else {
        $sql = "SELECT $columns FROM $table_job ORDER BY posted DESC LIMIT $limit OFFSET $offset";
        $jobs = $wpdb->get_results($sql, ARRAY_A);
        $total = intval($wpdb->get_var("SELECT COUNT(id) AS total FROM $table_job ORDER BY posted DESC", 0));
    }

    if (!$jobs) $jobs = array();

    $prev = $offset + $limit < $total ? true : false;
    $next = $page > 1 ? true : false;

    $messages[1] = __('Job added.', 'wpjobads');
    $messages[2] = __('Job deleted.', 'wpjobads');
    $messages[3] = __('Job updated.', 'wpjobads');
    $messages[4] = __('Job not added.', 'wpjobads');
    $messages[5] = __('Job not updated.', 'wpjobads');

    $form = '<form name="addjob" id="addjob" method="post" action="admin.php?page=wpjobads-admin-jobs&action=add">';
    $action = 'add';
    $nonce_action = 'add-job-listing';
    $heading = __('Add Job Listing', 'wpjobads');
    $submit_text = __('Add Job Listing', 'wpjobads');
    $gmt_offset = intval(get_option('gmt_offset')) * 3600;

    $colors = array('fulltime' => '#009900', 'parttime' => '#663366', 'freelance' => '#FE8433', 'internship' => '#000000');
    $labels = array('fulltime' => __('Full Time', 'wpjobads'), 'parttime' => __('Part Time', 'wpjobads'), 'freelance' => __('Freelance', 'wpjobads'), 'internship' => __('Internship', 'wpjobads'));
?>
    <?php if (isset($_GET['message'])) : ?>
    <div id="message" class="updated fade"><p><?php echo $messages[$_GET['message']]; ?></p></div>
    <?php endif; ?>

    <div class="wrap">
        <div id="icon-edit" class="icon32">
            <br/>
        </div>
        <h2><?php _e('Job Listing (<a href="#addjob">add new</a>)', 'wpjobads') ?></h2>
        <form style="position:relative;" method="get">
            <ul class="subsubsub">
                <li><a class="current"><?php _e('All', 'wpjobads') ?></a></li>
            </ul>
            <p class="search-box">
                <input type="hidden" name="page" value="<?php echo attribute_escape(stripslashes($_GET['page'])) ?>">
                <input type="text" class="search-input" name="s" value="<?php echo attribute_escape(stripslashes($_GET['s'])) ?>" />
                <input type="submit" value="Search Jobs" class="button" style="font-size:13px;padding:3px;" />
            </p>
            <div class="clear" />
        </form>
        <br class="clear" />
        <table class="widefat">
            <thead>
                <tr>
                    <th style="text-align: center;" scope="col"><?php _e('ID', 'wpjobads') ?></th>
                    <th scope="col"><?php _e('When', 'wpjobads') ?></th>
                    <th scope="col"><?php _e('Title', 'wpjobads') ?></th>
                    <th style="text-align: center;" scope="col"><?php _e('Published', 'wpjobads') ?></th>
                    <th style="text-align: center;" scope="col"><?php _e('Approved', 'wpjobads') ?></th>
                    <th style="text-align: center;" scope="col"><?php _e('Price', 'wpjobads') ?></th>
                    <th style="text-align: center;" scope="col"><?php _e('Paid', 'wpjobads') ?></th>
                    <th colspan="2" style="text-align: center;" scope="col"><?php _e('Action', 'wpjobads') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                <tr><td colspan="7"><?php _e('No jobs found.', 'wpjobads') ?></td></tr>
                <?php endif ?>
                <?php foreach ($jobs as $i => $job): ?>
                <?php if ($i % 2): ?>
                <tr>
                <?php else: ?>
                <tr class="alternate">
                <?php endif ?>
                    <td style="text-align: center;"><?php echo attribute_escape($job['id']) ?></td>
                    <td>
                        <?php echo attribute_escape(date('Y-m-d', strtotime($job['posted']) + $gmt_offset)) ?>
                        <br/>
                        <?php echo attribute_escape(date('g:i:s a', strtotime($job['posted']) + $gmt_offset)) ?>
                    </td>
                    <td>
                        <span style="color:white;background-color:<?php echo $colors[$job['type']] ?>;padding:1px;font-family:Helvetica;font-size:8px;font-weight:bold;text-transform:uppercase;"><?php echo attribute_escape($labels[$job['type']]) ?></span>
                        <?php if ($job['company_url']): ?>
                        <?php echo sprintf(__('%1$s at %2$s', 'wpjobads'), attribute_escape($job['title']), '<a href="'.attribute_escape($job['company_url']).'">'.attribute_escape($job['company_name'])).'</a>' ?>
                        <?php else: ?>
                        <?php echo sprintf(__('%1$s at %2$s', 'wpjobads'), attribute_escape($job['title']), attribute_escape($job['company_name'])) ?>
                        <?php endif ?>
                    </td>
                    <td style="text-align: center;"><?php $job['ad_published'] ? _e('Yes') : _e('No') ?></td>
                    <td style="text-align: center;"><?php $job['ad_approved'] ? _e('Yes') : _e('No') ?></td>
                    <?php if ($job['ad_price']): ?>
                    <td style="text-align: center;"><?php echo $job['ad_currency'] ?> <?php echo $job['ad_price'] ?></td>
                    <td style="text-align: center;"><?php $job['ad_paid'] ? _e('Yes') : _e('No') ?></td>
                    <?php else: ?>
                    <td style="text-align: center;" colspan="2"><?php _e('Free listing', 'wpjobads') ?></td>
                    <?php endif ?>
                    <td><a class="edit" href="admin.php?page=wpjobads-admin-jobs&amp;action=edit&amp;job_ID=<?php echo attribute_escape($job['id']) ?>"><?php _e('Edit', 'wpjobads') ?></a></td>
                    <td><a class="delete" href="<?php echo wp_nonce_url('admin.php?page=wpjobads-admin-jobs&amp;action=delete&amp;job_ID=' . $job['id'], 'delete-job_' . $job['id']) ?>" ><?php _e('Delete', 'wpjobads') ?></a></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <div class="navigation">
            <?php if ($prev): ?>
            <div class="alignleft">
                <a href="admin.php?page=wpjobads-admin-jobs&amp;paged=<?php echo $page + 1 ?>"><?php _e('&laquo; Previous Entries', 'wpjobads') ?></a>
            </div>
            <?php endif ?>
            <?php if ($next): ?>
            <div class="alignright">
                <a href="admin.php?page=wpjobads-admin-jobs&amp;paged=<?php echo $page - 1 ?>"><?php _e('Next Entries &raquo;', 'wpjobads') ?></a>
            </div>
            <?php endif ?>
        </div>
    </div>

    <div class="wrap">
        <h2><?php echo $heading ?></h2>
        <?php wpjobads_admin_add_job_form(null, $form, $action, $nonce_action, $submit_text) ?>
    </div>

<?php
}// }}}

function wpjobads_admin_job_form($job, $form, $action, $nonce_action, $submit_text)// {{{
{
    global $wp_version;
    $categories = wpjobads_get_all_categories();
    $types = wpjobads_get_all_types();
    $currencies = wpjobads_get_all_currencies();
    $gmt_offset = intval(get_option('gmt_offset')) * 3600;
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    gmdate('Y-m-d H:i:s', $now + intval($job['ad_duration']) * 86400)
?>
    <?php echo $form ?>
        <input type="hidden" value="<?php echo $action ?>" name="action" />
        <input type="hidden" value="<?php echo attribute_escape($job['id']) ?>" name="job_ID"/>
        <?php wp_nonce_field($nonce_action) ?>
        <?php if (version_compare($wp_version, '2.5', '>=') == TRUE): ?>
        <table class="form-table" width="100%" cellspacing="2" cellpadding="5">
        <?php else: ?>
        <table class="editform" width="100%" cellspacing="2" cellpadding="5">
        <?php endif ?>
            <tr>
                <th scope="row" valign="middle"><label for="title"><?php _e('Job title:', 'wpjobads') ?></label></th>
                <td><input type="text" class="regular-text" id="title" name="title" size="40" value="<?php echo attribute_escape($job['title']) ?>" tabindex="1" /></td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="category"><?php _e('Category:', 'wpjobads') ?></label></th>
                <td>
                    <select id="category" name="category" tabindex="2">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo attribute_escape($cat['id']) ?>" <?php wpjobads_selected($job['category'], $cat['id']) ?>><?php echo attribute_escape($cat['name']) ?></option>
                        <?php endforeach ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="type"><?php _e('Type:', 'wpjobads') ?></label></th>
                <td>
                    <select id="type" name="type" tabindex="3">
                        <?php foreach ($types as $type): ?>
                        <option value="<?php echo attribute_escape($type['id']) ?>" <?php wpjobads_selected($job['type'], $type['id']) ?>><?php echo attribute_escape($type['name']) ?></option>
                        <?php endforeach ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="description"><?php _e('Description:', 'wpjobads') ?></label></th>
                <td>
                    <textarea id="description" name="description" rows="7" cols="30" tabindex="3"><?php echo attribute_escape($job['description']) ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="how_to_apply"><?php _e('How to apply:', 'wpjobads') ?></label></th>
                <td>
                    <textarea id="how_to_apply" name="how_to_apply" rows="4" cols="30" tabindex="4"><?php echo attribute_escape($job['how_to_apply']) ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="location"><?php _e('Job location:', 'wpjobads') ?></label></th>
                <td><input type="text" class="regular-text" id="location" name="location" size="40" value="<?php echo attribute_escape($job['location']) ?>" tabindex="5" /></td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="zipcode"><?php _e('Zipcode (optional):', 'wpjobads') ?></label></th>
                <td><input type="text" class="regular-text" id="zipcode" name="zipcode" size="10" value="<?php echo attribute_escape($job['zipcode']) ?>" tabindex="6" /></td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="company_name"><?php _e('Company name:', 'wpjobads') ?></label></th>
                <td><input type="text" class="regular-text" id="company_name" name="company_name" size="40" value="<?php echo attribute_escape($job['company_name']) ?>" tabindex="7" /></td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="company_url"><?php _e('Company URL (optional):', 'wpjobads') ?></label></th>
                <td>
                    <input type="text" class="regular-text" id="company_url" name="company_url" size="40" value="<?php echo attribute_escape($job['company_url']) ?>" tabindex="8" />
                    <br/>
                    <?php _e("Don't forget the http:// part.", 'wpjobads') ?>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="contact_name"><?php _e('Contact name:', 'wpjobads') ?></label></th>
                <td><input type="text" class="regular-text" id="contact_name" name="contact_name" size="40" value="<?php echo attribute_escape($job['contact_name']) ?>" tabindex="9" /></td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="contact_email"><?php _e('Contact email:', 'wpjobads') ?></label></th>
                <td><input type="text" class="regular-text" id="contact_email" name="contact_email" size="40" value="<?php echo attribute_escape($job['contact_email']) ?>" tabindex="10" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="ad_duration"><?php _e('Duration (in days):', 'wpjobads') ?></label></th>
                <td>
                    <input type="text" id="ad_duration" name="ad_duration" value="<?php echo attribute_escape($job['ad_duration']) ?>" size="3" tabindex="11" />
                    Expiration date: <?php echo date($date_format . ' ' . $time_format, strtotime($job['expired']) + $gmt_offset) ?>
                    <br/>
                    <?php _e('Setting the duration to -1 means the duration is indefinite.', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="ad_currency"><?php _e('Currency &amp; price:', 'wpjobads') ?></label></th>
                <td>
                    <select id="ad_currency" name="ad_currency" tabindex="12">
                        <?php foreach ($currencies as $cur => $txt): ?>
                        <option value="<?php echo attribute_escape($cur) ?>" <?php wpjobads_selected($job['ad_currency'], $cur) ?>><?php echo attribute_escape($txt) ?></option>
                        <?php endforeach ?>
                    </select>
                    <input type="text" name="ad_price" value="<?php echo attribute_escape($job['ad_price']) ?>" size="3" tabindex="13" />
                    <br/>
                    <?php _e('Setting the price to 0 means free job listings.', 'wpjobads') ?>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="ad_paid"><?php _e('Paid:', 'wpjobads') ?></label></th>
                <td>
                    <select id="ad_paid" name="ad_paid" tabindex="14">
                        <option value="0" <?php wpjobads_selected($job['ad_paid'], 0) ?>><?php _e('No') ?></option>
                        <option value="1" <?php wpjobads_selected($job['ad_paid'], 1) ?>><?php _e('Yes') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="ad_approved"><?php _e('Approved:', 'wpjobads') ?></label></th>
                <td>
                    <select id="ad_approved" name="ad_approved" tabindex="15">
                        <option value="0" <?php wpjobads_selected($job['ad_approved'], 0) ?>><?php _e('No') ?></option>
                        <option value="1" <?php wpjobads_selected($job['ad_approved'], 1) ?>><?php _e('Yes') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="ad_published"><?php _e('Published:', 'wpjobads') ?></label></th>
                <td>
                    <select id="ad_published" name="ad_published" tabindex="16">
                        <option value="0" <?php wpjobads_selected($job['ad_published'], 0) ?>><?php _e('No') ?></option>
                        <option value="1" <?php wpjobads_selected($job['ad_published'], 1) ?>><?php _e('Yes') ?></option>
                    </select>
                </td> 
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="txn_id"><?php _e('PayPal Transaction ID:', 'wpjobads') ?></th>
                <td><input type="text" class="regular-text" id="txn_id" name="txn_id" size="40" value="<?php echo attribute_escape($job['txn_id']) ?>" tabindex="17" /></td>
            </tr>
        </table>
        <p class="submit">
        <input type="submit" name="Submit" value="<?php echo attribute_escape($submit_text) ?>" tabindex="18" />
        </p>
    </form>
<?php
}// }}}

function wpjobads_admin_add_job_form($job, $form, $action, $nonce_action, $submit_text)// {{{
{
    if (is_null($job)) {
        $wpjobads_options = get_option('wpjobads_options');
        $job = array();
        $job['id'] = '';
        $job['posted'] = '';
        $job['modified'] = $job['posted'];
        $job['expired'] = gmdate('Y-m-d H:i:s', time() + intval($wpjobads_options['duration']) * 86400);
        $job['title'] = '';
        $job['category'] = $wpjobads_options['default_category'];
        $job['description'] = '';
        $job['how_to_apply'] = '';
        $job['company_name'] = '';
        $job['location'] = '';
        $job['zipcode'] = '';
        $job['company_url'] = '';
        $job['contact_name'] = '';
        $job['contact_email'] = '';
        $job['ad_duration'] = intval($wpjobads_options['duration']);
        $job['ad_currency'] = $wpjobads_options['currency'];
        $job['ad_price'] = 0;
        $job['ad_paid'] = 0;
        $job['ad_approved'] = 0;
        $job['ad_published'] = 0;
        $job['txn_id'] = '';
    }
    wpjobads_admin_job_form($job, $form, $action, $nonce_action, $submit_text);
}// }}}

function wpjobads_admin_edit_job_form($job, $form, $action, $nonce_action, $submit_text)// {{{
{
    wpjobads_admin_job_form($job, $form, $action, $nonce_action, $submit_text);
}// }}}

function wpjobads_admin_load_options()// {{{
{
    if ($_POST['action'] == 'activate') return wpjobads_admin_activate_license();
    if ($_POST['action'] == 'update') return wpjobads_admin_update_options();
}// }}}

function wpjobads_admin_options()// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    if (empty($wpjobads_options['license_key'])) return wpjobads_license_form('wpjobads-admin-options');

    $messages[1] = __('Options updated.', 'wpjobads');
    $messages[2] = __('Options not updated.', 'wpjobads');

    $form = '<form method="post" action="admin.php?page=wpjobads-admin-options&amp;action=update">';
    $action = 'update';
    $nonce_action = 'update-options';
    $heading = __('Options', 'wpjobads');
    $submit_text = __('Save Changes');
?>
    <?php if (isset($_GET['message'])) : ?>
    <div id="message" class="updated fade"><p><?php echo $messages[$_GET['message']]; ?></p></div>
    <?php endif; ?>

    <div class="wrap">
        <div id="icon-options-general" class="icon32">
            <br/>
        </div>
        <h2><?php echo $heading ?></h2>
        <?php wpjobads_admin_options_form($wpjobads_options, $form, $action, $nonce_action, $submit_text) ?>
    </div>
<?php
}// }}}

function wpjobads_admin_load_uninstall()// {{{
{
    if ($_POST['action'] == 'uninstall') return wpjobads_uninstall();
}// }}}

function wpjobads_admin_uninstall()// {{{
{
    $heading = __('Uninstall', 'wpjobads');
?>
    <div class="wrap">
        <h2><?php echo $heading ?></h2>
        <p>Remove all data associated with this plugin and deactivate it.</p>
        <form method="post" action="admin.php?page=wpjobads-admin-uninstall">
            <input type="hidden" name="action" value="uninstall">
            <input type="submit" class="button-secondary action" value="Uninstall Now" />
        </form>
        <p>Warning: This action <em>cannot</em> be undone.</p>
    </div>
<?php
}// }}}

function wpjobads_get_all_currencies()// {{{
{
    return array('USD' => 'USD', 'CAD' => 'CAD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'YEN' => 'YEN', 'AUD' => 'AUD', 'NZD' => 'NZD', 'CHF' => 'CHF', 'HKD' => 'HKD', 'SGD' => 'SGD', 'SEK' => 'SEK', 'DKK' => 'DKK', 'PLN' => 'PLN', 'NOK' => 'NOK', 'HUF' => 'HUF', 'CZK' => 'CZK');
}// }}}

function wpjobads_admin_options_form($wpjobads_options, $form, $action, $nonce_action, $submit_text)// {{{
{
    global $wpdb, $wp_version;
    if (!$wpjobads_options) {
        $wpjobads_options = get_option('wpjobads_options');
    }
    $currencies = wpjobads_get_all_currencies();

?>
    <?php echo $form ?>
        <input type="hidden" name="action" value="<?php echo $action ?>" />
        <?php wp_nonce_field($nonce_action) ?>
        <?php if (version_compare($wp_version, '2.5', '>=') == TRUE): ?>
        <table class="form-table">
        <?php else: ?>
        <table class="optiontable">
        <?php endif ?>
            <tr valign="top">
                <th scope="row"><?php _e('Job board title:', 'wpjobads') ?></th>
                <td>
                    <input type="text" class="regular-text" name="title" value="<?php echo attribute_escape($wpjobads_options['title']) ?>" size="40" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Description:', 'wpjobads') ?></th>
                <td>
                    <textarea name="description" class="large-text" rows="10" cols="50"><?php echo $wpjobads_options['description'] ?></textarea>
                    <br/>
                    <?php _e('Shown at the very top of "Post new job" form.', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Invite:', 'wpjobads') ?></th>
                <td>
                    <input type="text" class="regular-text" name="invite" value="<?php echo attribute_escape($wpjobads_options['invite']) ?>" size="40" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Enable new job listings?', 'wpjobads') ?></th>
                <td>
                    <input type="hidden" name="enable_frontend" value="0" />
                    <input type="checkbox" id="enable_frontend" name="enable_frontend" value="1" <?php wpjobads_checked($wpjobads_options['enable_frontend'], 1) ?> tabindex="1" />
                    <label for="enable_frontend"><?php _e('Checking this will enable the "Post new job" form.', 'wpjobads') ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Auto approve listings?', 'wpjobads') ?></th>
                <td>
                    <input type="hidden" name="auto_approve" value="0" />
                    <input type="checkbox" id="auto_approve" name="auto_approve" value="1" <?php wpjobads_checked($wpjobads_options['auto_approve'], 1) ?> />
                    <label for="auto_approve"><?php _e('Checking this will automatically approve any incoming job ads', 'wpjobads') ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Send payment email on auto approve?', 'wpjobads') ?></th>
                <td>
                    <input type="hidden" name="force_payment_email" value="0" />
                    <input type="checkbox" id="force_payment_email" name="force_payment_email" value="1" <?php wpjobads_checked($wpjobads_options['force_payment_email'], 1) ?> />
                    <label for="force_payment_email"><?php _e('Checking this will send the payment email message (if any) even if auto approve is on', 'wpjobads') ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Duration (in days):', 'wpjobads') ?></th>
                <td><input type="text" name="duration" value="<?php echo attribute_escape($wpjobads_options['duration']) ?>" size="3" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Currency:', 'wpjobads') ?></th>
                <td>
                    <select name="currency">
                        <?php foreach ($currencies as $cur => $txt): ?>
                        <option value="<?php echo attribute_escape($cur) ?>" <?php wpjobads_selected($wpjobads_options['currency'], $cur) ?>><?php echo attribute_escape($txt) ?></option>
                        <?php endforeach ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th><?php _e('Full time price:', 'wpjobads') ?></th>
                <td>
                    <input type="text" name="fulltime_price" value="<?php echo attribute_escape($wpjobads_options['fulltime_price']) ?>" size="3" />
                </td>
            </tr>
            <tr valign="top">
                <th><?php _e('Part time price:', 'wpjobads') ?></th>
                <td>
                    <input type="text" name="parttime_price" value="<?php echo attribute_escape($wpjobads_options['parttime_price']) ?>" size="3" />
                </td>
            </tr>
            <tr valign="top">
                <th><?php _e('Freelance price:', 'wpjobads') ?></th>
                <td>
                    <input type="text" name="freelance_price" value="<?php echo attribute_escape($wpjobads_options['freelance_price']) ?>" size="3" />
                </td>
            </tr>
            <tr valign="top">
                <th><?php _e('Internship price:', 'wpjobads') ?></th>
                <td>
                    <input type="text" name="internship_price" value="<?php echo attribute_escape($wpjobads_options['internship_price']) ?>" size="3" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('PayPal email:', 'wpjobads') ?></th>
                <td>
                    <input type="text" class="code" name="paypal_email" value="<?php echo attribute_escape($wpjobads_options['paypal_email']) ?>" size="40" />
                    <br/>
                    <?php _e('This email address must match your PayPal email address.', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('PayPal URL:', 'wpjobads') ?></th>
                <td>
                    <input type="text" class="code" name="paypal_url" value="<?php echo attribute_escape($wpjobads_options['paypal_url']) ?>" size="40" />
                    <br/>
                    <span class="code"><?php _e('This should generally be https://www.paypal.com/cgi-bin/webscr', 'wpjobads') ?></span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('PayPal verification URL') ?></th>
                <td>
                    <input type="text" class="code" name="paypal_verification_url" value="<?php echo attribute_escape($wpjobads_options['paypal_verification_url']) ?>" size="40" />
                    <br/>
                    <span class="code"><?php _e('This should generally be ssl://www.paypal.com:443/cgi-bin/webscr', 'wpjobads') ?></span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('SSL availability:', 'wpjobads') ?></th>
                <td>
                    <?php if (extension_loaded('openssl')): ?>
                    <?php _e('Installed.', 'wpjobads') ?>
                    <?php else: ?>
                    <?php _e('Not installed. In order for PayPal to work right, you <em>must</em> enable SSL (openssl) support.', 'wpjobads') ?>
                    <?php endif ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Job listing date format:', 'wpjobads') ?></th>
                <td>
                    <input type="text" class="regular-text" name="date_format" value="<?php echo attribute_escape($wpjobads_options['date_format']) ?>" size="30" />
                    <br/>
                    Output: <strong><?php echo date($wpjobads_options['date_format'], time()) ?></strong>
                <br/>
                <?php _e('<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date formatting</a>. Click "Save Changes" to update sample output.', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" valign="top"><?php _e('Terms:', 'wpjobads') ?></th>
                <td>
                    <textarea name="terms" class="large-text" rows="10" cols="50"><?php echo attribute_escape($wpjobads_options['terms']) ?></textarea>
                    <br/>
                    <?php _e('Shown to ad buyers prior to submission.', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" valign="top"><?php _e('Payment email:', 'wpjobads') ?></th>
                <td>
                    Subject<br/><input type="text" class="regular-text" name="payment_email_subject" value="<?php echo attribute_escape($wpjobads_options['payment_email_subject']) ?>" size="40" /><br/>
                    Message<br/><textarea name="payment_email_message" class="large-text" rows="10" cols="50"><?php echo attribute_escape($wpjobads_options['payment_email_message']) ?></textarea><br/>
                    <?php _e('Sent to advertisers prior to payment.', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" valign="top"><?php _e('Publish email:', 'wpjobads') ?></th>
                <td>
                    Subject<br/><input type="text" class="regular-text" name="publish_email_subject" value="<?php echo attribute_escape($wpjobads_options['publish_email_subject']) ?>" size="40" /><br/>
                    Message<br/><textarea name="publish_email_message" class="large-text" rows="10" cols="50"><?php echo attribute_escape($wpjobads_options['publish_email_message']) ?></textarea><br/>
                    <?php _e('Sent to advertisers when their ad is published.', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" valign="top"><?php _e('Email from:', 'wpjobads') ?></th>
                <td>
                    <input type="text" name="email_from_name" value="<?php echo attribute_escape($wpjobads_options['email_from_name']) ?>" size="20" />
                    <input type="text" name="email_from" value="<?php echo attribute_escape($wpjobads_options['email_from']) ?>" size="20" />
                    <br/>
                    <?php _e('Name and email address all outgoing mails are from', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" valign="top"><?php _e('Email notification:', 'wpjobads') ?></th>
                <td>
                    <input type="text" name="email_notification" value="<?php echo attribute_escape($wpjobads_options['email_notification']) ?>" size="40" />
                    <br/>
                    <?php _e('Email address to be notified when a new listing is submitted. Empty this field if you do not want notifications.', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" valign="top"><?php _e('Notification email:', 'wpjobads') ?></th>
                <td>
                    Subject<br/><input type="text" class="regular-text" name="notification_email_subject" value="<?php echo attribute_escape($wpjobads_options['notification_email_subject']) ?>" size="40" /><br/>
                    Message<br/><textarea name="notification_email_message" class="large-text" rows="10" cols="50"><?php echo attribute_escape($wpjobads_options['notification_email_message']) ?></textarea><br/>
                    <?php _e('Sent to email notification address defined above when a new listing is submitted.', 'wpjobads') ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" valign="top"><?php _e('Expired ads viewable?', 'wpjobads') ?></th>
                <td>
                    <input type="hidden" name="viewable_expired_ads" value="0" />
                    <input type="checkbox" id="viewable_expired_ads" name="viewable_expired_ads" value="1" <?php wpjobads_checked($wpjobads_options['viewable_expired_ads'], 1) ?> />
                    <label for="viewable_expired_ads"><?php _e('Checking this will allow your site users to view expired ads', 'wpjobads') ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" valign="top"><?php _e('Enable random job ad in posts?', 'wpjobads') ?></th>
                <td>
                    <input type="hidden" name="enable_random_ad" value="0" />
                    <input type="checkbox" id="enable_random_ad" name="enable_random_ad" value="1" <?php wpjobads_checked($wpjobads_options['enable_random_ad'], 1) ?> />
                    <label for="enable_random_ad"><?php _e('Checking this will enable random job ad in every post', 'wpjobads') ?></label>
                </td>
            </tr>
        </table>
        <input type="hidden" name="page_options" value="title,description,invite,enable_frontend,auto_approve,force_payment_email,duration,currency,fulltime_price,parttime_price,freelance_price,internship_price,paypal_email,paypal_url,paypal_verification_url,date_format,terms,payment_email_subject,payment_email_message,publish_email_subject,publish_email_message,email_from_name,email_from,email_notification,notification_email_subject,notification_email_message,viewable_expired_ads,enable_random_ad" />
        <p class="submit">
            <input type="submit" name="Submit" value="<?php echo attribute_escape($submit_text) ?>" />
        </p>
    </form>
<?php
}// }}}

function wpjobads_admin_activate_license()// {{{
{
    check_admin_referer('activate-license');

    if (!current_user_can('manage_options'))
        wp_die(__('Cheatin&#8217; uh?'));

    $_POST['license_key'] = strtoupper($_POST['license_key']);
    if (!preg_match('/[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}/', $_POST['license_key'])) {
        wp_redirect('admin.php?page=' . $_POST['r'] . '&m=' . urlencode(base64_encode('Invalid license key')));
        return;
    }
    $wpjobads_options = get_option('wpjobads_options');
    $wpjobads_options['license_key'] = $_POST['license_key'];

    update_option('wpjobads_options', $wpjobads_options);
    wp_redirect('admin.php?page=' . $_POST['r']);
}// }}}

function wpjobads_admin_update_options()// {{{
{
    check_admin_referer('update-options');

    if (!current_user_can('manage_options'))
        wp_die(__('Cheatin&#8217; uh?'));

    if (wpjobads_update_options(stripslashes_deep($_POST))) {
        wp_redirect('admin.php?page=wpjobads-admin-options&message=1');
    } else {
        wp_redirect('admin.php?page=wpjobads-admin-options&message=2');
    }
}// }}}

function wpjobads_update_options($options)// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    $page_options = explode(',', $options['page_options']);
    foreach ($page_options as $key) {
        if (isset($options[$key]))
            $wpjobads_options[$key] = $options[$key];
    }
    return update_option('wpjobads_options', $wpjobads_options);
}// }}}

function wpjobads_admin_add_category()// {{{
{
    check_admin_referer('add-job-category');

    if (!current_user_can('manage_options'))
        wp_die(__('Cheatin&#8217; uh?'));

    if(wpjobads_insert_category(stripslashes_deep($_POST))) {
        wp_redirect('admin.php?page=wpjobads-admin-categories&message=1');
    } else {
        wp_redirect('admin.php?page=wpjobads-admin-categories&message=4');
    }
}// }}}

function wpjobads_admin_edit_category()// {{{
{
    $cat_ID = (int) $_GET['cat_ID'];
    $category = wpjobads_get_category($cat_ID);
    $heading = __('Edit Job Category', 'wpjobads');
    $submit_text = __('Update Job Category', 'wpjobads');
    $form = '<form name="editcat" id="editcat" method="post" action="admin.php?page=wpjobads-admin-categories&amp;action=update">';
    $action = 'update';
    $nonce_action = 'update-job-category_' . $cat_ID;
?>
    <div class="wrap">
        <h2><?php echo $heading ?></h2>
        <?php wpjobads_admin_category_form($category, $form, $action, $nonce_action, $submit_text) ?>
    </div>
<?php
}// }}}

function wpjobads_admin_update_category()// {{{
{
    $cat_ID = (int) $_POST['cat_ID'];
    check_admin_referer('update-job-category_' . $cat_ID);

    if (!current_user_can('manage_options'))
        wp_die(__('Cheatin&#8217; uh?'));

    if (wpjobads_update_category(stripslashes_deep($_POST)))
        wp_redirect('admin.php?page=wpjobads-admin-categories&message=3');
    else
        wp_redirect('admin.php?page=wpjobads-admin-categories&message=5');
}// }}}

function wpjobads_admin_delete_category()// {{{
{
    $cat_ID = (int) $_GET['cat_ID'];
    check_admin_referer('delete-job-category_' .  $cat_ID);

    if (!current_user_can('manage_options'))
        wp_die(__('Cheatin&#8217; uh?'));

    $cat_name = wpjobads_get_catname($cat_ID);

    $wpjobads_options = get_option('wpjobads_options');

    if ($cat_ID == $wpjobads_options['default_category'])
        wp_die(sprintf(__('Can&#8217;t delete the <strong>%s</strong> category: this is the default one'), $cat_name));

    wpjobads_delete_category($cat_ID);

    wp_redirect('admin.php?page=wpjobads-admin-categories&message=2');
}// }}}

function wpjobads_admin_add_job()// {{{
{
    check_admin_referer('add-job-listing');

    if (!current_user_can('manage_options'))
        wp_die(__('Cheatin&#8217; uh?'));

    $_POST['ad_paid'] = intval($_POST['ad_price']) == 0 ? 1 : 0;
    if(wpjobads_insert_job(stripslashes_deep($_POST))) {
        wp_redirect('admin.php?page=wpjobads-admin-jobs&message=1');
    } else {
        wp_redirect('admin.php?page=wpjobads-admin-jobs&message=4');
    }
}// }}}

function wpjobads_admin_edit_job()// {{{
{
    $job_ID = (int) $_GET['job_ID'];
    $job = wpjobads_get_job($job_ID);
    $heading = __('Edit Job', 'wpjobads');
    $submit_text = __('Update Job', 'wpjobads');
    $form = '<form name="editjob" id="editjob" method="post" action="admin.php?page=wpjobads-admin-jobs&amp;action=update">';
    $action = 'update';
    $nonce_action = 'update-job_' . $job_ID;
?>
    <div class="wrap">
        <h2><?php echo $heading ?></h2>
        <?php wpjobads_admin_edit_job_form($job, $form, $action, $nonce_action, $submit_text) ?>
    </div>
<?php
}// }}}

function wpjobads_admin_update_job()// {{{
{
    $job_ID = (int) $_POST['job_ID'];
    check_admin_referer('update-job_' . $job_ID);

    if (!current_user_can('manage_options'))
        wp_die(__('Cheatin&#8217; uh?'));

    if (wpjobads_update_job(stripslashes_deep($_POST)))
        wp_redirect('admin.php?page=wpjobads-admin-jobs&message=3');
    else
        wp_redirect('admin.php?page=wpjobads-admin-jobs&message=5');
}// }}}

function wpjobads_admin_delete_job()// {{{
{
    $job_ID = (int) $_GET['job_ID'];
    check_admin_referer('delete-job_' .  $job_ID);

    if (!current_user_can('manage_options'))
        wp_die(__('Cheatin&#8217; uh?'));

    wpjobads_delete_job($job_ID);
    wp_redirect('admin.php?page=wpjobads-admin-jobs&message=2');
}// }}}

// ----------------------------------------------------------------------------

// Data functions

function wpjobads_get_category($cat_ID)// {{{
{
    global $wpdb;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
    $sql = "SELECT id, name, priority, job_count FROM $table_category WHERE id = " . intval($cat_ID) . " LIMIT 1";
    $category = $wpdb->get_row($sql, ARRAY_A);
    return $category;
}// }}}

function wpjobads_get_all_categories()// {{{
{
    global $wpdb;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
    $sql = "SELECT id, name, priority, job_count FROM $table_category ORDER BY priority ASC, name ASC";
    $categories = $wpdb->get_results($sql, ARRAY_A);
    return $categories;
}// }}}

function wpjobads_get_catname($cat_ID)// {{{
{
    global $wpdb;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
    $sql = "SELECT name FROM $table_category WHERE id = " . intval($cat_ID) . " LIMIT 1";
    $name = $wpdb->get_var($sql);
    return $name;
}// }}}

function wpjobads_insert_category($category)// {{{
{
    global $wpdb;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
    if (empty($category['name']))
        return false;

    $category['priority'] = intval($category['priority']);

    if (!$category['priority'])
        return false;
    $sql = "INSERT INTO $table_category (name, priority)
            VALUES ('" . $wpdb->escape($category['name']) . "', " . $category['priority'] . ")";
    $result = $wpdb->query($sql);
    return $result;
}// }}}

function wpjobads_update_category($category)// {{{
{
    global $wpdb;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
    if (empty($category['cat_ID']) || empty($category['name']) || empty($category['priority']))
        return false;

    $category['id']       = intval($category['cat_ID']);
    $category['priority'] = intval($category['priority']);

    if ($category['priority'] < 1) return false;
    $sql = "UPDATE $table_category SET name = '" . $wpdb->escape($category['name']) . "',
            priority = " . $category['priority'] . " WHERE id = " . $category['id'];
    $result = $wpdb->query($sql);
    return $result;
}// }}}

function wpjobads_delete_category($cat_ID)// {{{
{
    global $wpdb;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
    $sql = "DELETE FROM $table_category WHERE id = " . intval($cat_ID);
    $result = $wpdb->query($sql);
    return $result;
}// }}}

function wpjobads_get_all_types()// {{{
{
    return array(
        array('id' => 'fulltime', 'name' => __('Full Time', 'wpjobads')),
        array('id' => 'parttime', 'name' => __('Part Time', 'wpjobads')),
        array('id' => 'freelance', 'name' => __('Freelance', 'wpjobads')),
        array('id' => 'internship', 'name' => __('Internship', 'wpjobads'))
    );
}// }}}

function wpjobads_job_is_viewable($job_ID, $strict = true)// {{{
{
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    if ($strict) {
        $now = gmdate('Y-m-d H:i:s', time());
        $sql = "SELECT COUNT(id) FROM $table_job WHERE id = " . $job_ID . " AND ad_paid = 1 AND ad_approved = 1 AND expired > '$now'";
    } else {
        $sql = "SELECT COUNT(id) FROM $table_job WHERE id = " . $job_ID . " AND ad_paid = 1 AND ad_approved = 1";
    }
    $viewable = $wpdb->get_var($sql);
    return (bool) $viewable;
}// }}}

function wpjobads_get_job($job_ID)// {{{
{
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $sql = "SELECT * FROM $table_job WHERE id = " . intval($job_ID) . " LIMIT 1";
    $job = $wpdb->get_row($sql, ARRAY_A);
    return $job;
}// }}}

function wpjobads_get_all_jobs($cat_ID = null, $type = null, $ad_approved = 1, $ad_paid = 1, $ad_published = 1)// {{{
{
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
    $columns = "$table_job.id, $table_job.posted, $table_job.title, $table_job.company_name, $table_job.location, $table_job.zipcode, $table_job.company_url, $table_category.id AS category_id, $table_category.name AS category_name, $table_job.type";
    $now = gmdate('Y-m-d H:i:s', time());
    $categories = wpjobads_get_all_categories();
    $conditions = array();
    if ($cat_ID) {
        $cat_ID = $wpdb->escape($cat_ID);
        $conditions[] = "$table_category.id = $cat_ID";
    }
    if ($type) {
        $type = $wpdb->escape($type);
        $conditions[] = "$table_job.type = '$type'";
    }
    $conditions = empty($conditions) ? '' : 'AND ' . implode(' AND ', $conditions);
    $sql = "SELECT $columns FROM $table_job INNER JOIN $table_category ON $table_job.category = $table_category.id WHERE ad_approved = $ad_approved AND ad_paid = $ad_paid AND ad_published = $ad_published AND (expired > '$now' OR ad_duration = -1) $conditions ORDER BY posted DESC";
    $jobs = array();
    $_jobs = $wpdb->get_results($sql, ARRAY_A);
    $__jobs = array();
    foreach ($categories as $category) {
        $__jobs[$category['id']]['label'] = $category['name'];
        $__jobs[$category['id']]['listing'] = array();
    }
    if (!empty($_jobs)) {
        foreach ($_jobs as $job) {
            $__jobs[$job['category_id']]['listing'][] = $job;
        }
        foreach ($__jobs as $cat => $job) {
            if (!empty($job['listing'])) {
                $jobs[$cat] = $job;
            }
        }
    } elseif (isset($cat_ID)) {
        $jobs[$cat_ID] = array('label' => wpjobads_get_catname($cat_ID), 'listing' => array());
    }
    return $jobs;
}// }}}

function wpjobads_search_all_jobs($query = '', $ad_approved = 1, $ad_paid = 1, $ad_published = 1)// {{{
{
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
    $columns = "$table_job.id, $table_job.posted, $table_job.title, $table_job.company_name, $table_job.location, $table_job.zipcode, $table_job.company_url, $table_category.id AS category_id, $table_category.name AS category_name, $table_job.type";
    $now = gmdate('Y-m-d H:i:s', time());
    $categories = wpjobads_get_all_categories();
    if ($query) {
        $query = $wpdb->escape($query);
        $sql = "SELECT $columns FROM $table_job INNER JOIN $table_category ON $table_job.category = $table_category.id WHERE ad_approved = $ad_approved AND ad_paid = $ad_paid AND ad_published = $ad_published AND (expired > '$now' OR ad_duration = -1) AND ($table_job.title LIKE '%$query%' OR $table_job.description LIKE '%$query%') ORDER BY posted DESC";
    }
    $jobs = array();
    $_jobs = $wpdb->get_results($sql, ARRAY_A);
    $__jobs = array();
    foreach ($categories as $category) {
        $__jobs[$category['id']]['label'] = $category['name'];
        $__jobs[$category['id']]['listing'] = array();
    }
    if (!empty($_jobs)) {
        foreach ($_jobs as $job) {
            $__jobs[$job['category_id']]['listing'][] = $job;
        }
        foreach ($__jobs as $cat => $job) {
            if (!empty($job['listing'])) {
                $jobs[$cat] = $job;
            }
        }
    } elseif (isset($cat_ID)) {
        $jobs[$cat_ID] = array('label' => wpjobads_get_catname($cat_ID), 'listing' => array());
    }
    return $jobs;
}// }}}

function wpjobads_valid_job($job)// {{{
{
    if (empty($job['title']))         return false;
    if (!intval($job['category']))    return false;
    if (empty($job['description']))   return false;
    if (empty($job['how_to_apply']))  return false;
    if (empty($job['location']))      return false;
    if (empty($job['company_name']))  return false;
    //if (empty($job['company_url']))   return false;
    if (empty($job['contact_name']))  return false;
    if (empty($job['contact_email'])) return false;
    return true;
}// }}}

function wpjobads_insert_job($job)// {{{
{
    global $wpdb;
    $wpjobads_options = get_option('wpjobads_options');
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    if (!wpjobads_valid_job($job)) return false;
    $now = time();

    $job['posted']        = $job['modified'] = $wpdb->escape(gmdate('Y-m-d H:i:s', $now));
    $job['expired']       = $wpdb->escape(gmdate('Y-m-d H:i:s', $now + intval($job['ad_duration']) * 86400));
    $job['title']         = $wpdb->escape($job['title']);
    $job['category']      = intval($job['category']);
    $job['type']          = $wpdb->escape($job['type']);
    $job['description']   = $wpdb->escape($job['description']);
    $job['how_to_apply']  = $wpdb->escape($job['how_to_apply']);
    $job['location']      = $wpdb->escape($job['location']);
    $job['zipcode']       = $wpdb->escape($job['zipcode']);
    $job['company_name']  = $wpdb->escape($job['company_name']);
    $job['company_url']   = $wpdb->escape($job['company_url']);
    $job['contact_name']  = $wpdb->escape($job['contact_name']);
    $job['contact_email'] = $wpdb->escape($job['contact_email']);
    $job['ad_duration']   = $wpdb->escape($job['ad_duration']);
    $job['ad_currency']   = $wpdb->escape($job['ad_currency']);
    $job['ad_price']      = $wpdb->escape($job['ad_price']);
    $job['ad_paid']       = $wpdb->escape($job['ad_paid']);
    $job['ad_approved']   = $wpdb->escape($job['ad_approved']);
    $job['ad_published']  = $wpdb->escape($job['ad_published']);
    $job['txn_id']        = $wpdb->escape(trim($job['txn_id']));
    $job['ip_address']    = $wpdb->escape(preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']));

    $sql = "INSERT INTO $table_job 
            (`posted`, `modified`, `expired`, `title`, `category`, `type`, `description`,
             `how_to_apply`, `company_name`, `location`, `zipcode`, `company_url`, `contact_name`, `contact_email`, `ip_address`, `ad_approved`, `ad_paid`, `ad_duration`, `ad_currency`, `ad_price`, `ad_published`".($job['txn_id']?', `txn_id`':null).")
            VALUES
            ('".$job['posted']."', '".$job['modified']."', '".$job['expired']."', '".$job['title']."', ".$job['category'].", '".$job['type']."', '".$job['description']."',
             '".$job['how_to_apply']."', '".$job['company_name']."', '".$job['location']."', '".$job['zipcode']."', '".$job['company_url']."', '".$job['contact_name']."', '".$job['contact_email']."', '".$job['ip_address']."', ".$job['ad_approved'].", ".$job['ad_paid'].", ".$job['ad_duration'].", '".$job['ad_currency']."', ".$job['ad_price'].", ".$job['ad_published'].($job['txn_id']?", '".$job['txn_id']."'":null).")";
    $result = $wpdb->query($sql);
    if ($result) {
        $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
        $sql = "UPDATE $table_category SET job_count = job_count + 1 WHERE id = " . $job['category'];
        $wpdb->query($sql);
    }
    return $result;
}// }}}

function wpjobads_update_job($job)// {{{
{
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    if (!wpjobads_valid_job($job)) return false;

    $job['id']            = $job['job_ID'];
    $job['modified']      = $wpdb->escape(gmdate('Y-m-d H:i:s', time()));
    $job['title']         = $wpdb->escape($job['title']);
    $job['category']      = intval($job['category']);
    $job['type']          = $wpdb->escape($job['type']);
    $job['description']   = $wpdb->escape($job['description']);
    $job['how_to_apply']  = $wpdb->escape($job['how_to_apply']);
    $job['location']      = $wpdb->escape($job['location']);
    $job['zipcode']       = $wpdb->escape($job['zipcode']);
    $job['company_name']  = $wpdb->escape($job['company_name']);
    $job['company_url']   = $wpdb->escape($job['company_url']);
    $job['contact_name']  = $wpdb->escape($job['contact_name']);
    $job['contact_email'] = $wpdb->escape($job['contact_email']);
    $job['ad_duration']   = $wpdb->escape($job['ad_duration']);
    $job['ad_currency']   = $wpdb->escape($job['ad_currency']);
    $job['ad_price']      = $wpdb->escape($job['ad_price']);
    $job['ad_paid']       = $wpdb->escape($job['ad_paid']);
    $job['ad_approved']   = $wpdb->escape($job['ad_approved']);
    $job['ad_published']  = $wpdb->escape($job['ad_published']);
    $job['txn_id']        = $wpdb->escape(trim($job['txn_id']));

    $old = wpjobads_get_job($job['job_ID']);
    $sql = "UPDATE $table_job SET 
        `modified` = '".$job['modified']."', `title` = '".$job['title']."', `category` = ".$job['category'].", `type` = '".$job['type']."', `description` = '".$job['description']."',
        `how_to_apply` = '".$job['how_to_apply']."', `company_name` = '".$job['company_name']."', `location` = '".$job['location']."', `zipcode` = '".$job['zipcode']."',
        `company_url` = '".$job['company_url']."', `contact_name` = '".$job['contact_name']."', `contact_email` = '".$job['contact_email']."', `ad_duration` = ".$job['ad_duration'].", `ad_currency` = '".$job['ad_currency']."', `ad_price` = ".$job['ad_price'].", `ad_paid` = ".$job['ad_paid'].", `ad_approved` = ".$job['ad_approved'].", `ad_published` = ".$job['ad_published'].($job['txn_id']?", `txn_id` = '".$job['txn_id']."'":null)." WHERE `id` = ".$job['id'];
    $result = $wpdb->query($sql);
    if ($result and ($old['category'] != $job['category'])) {
        $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
        $wpdb->query("UPDATE $table_category SET job_count = job_count - 1 WHERE job_count > 0 AND id = " . intval($old['category']));
        $wpdb->query("UPDATE $table_category SET job_count = job_count + 1 WHERE id = " . $job['category']);
    }
    return $result;
}// }}}

function wpjobads_delete_job($job_ID)// {{{
{
    global $wpdb;
    $job = wpjobads_get_job($job_ID);
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $sql = "DELETE FROM $table_job WHERE id = " . intval($job_ID);
    $result = $wpdb->query($sql);
    if ($result) {
        $table_category = $wpdb->prefix . WPJOBADS_CATEGORY;
        $sql = "UPDATE $table_category SET job_count = job_count - 1 WHERE id = " . $job['category'];
        $wpdb->query($sql);
    }

    return $result;
}// }}}

// ----------------------------------------------------------------------------

// PayPal functions

function wpjobads_paypal_form($job)// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    ob_start();
    $permalink = wpjobads_get_permalink();
    $parsed_url = parse_url($permalink);
    $path = $parsed_url['path'];
    $permalink .= ($path{strlen($path)-1} == '/') ? '#wpjobads' : '/#wpjobads';
?>
    <form method="post" action="<?php echo attribute_escape($wpjobads_options['paypal_url']) ?>">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="<?php echo attribute_escape($wpjobads_options['paypal_email']) ?>">
        <input type="hidden" name="item_name" value="<?php echo attribute_escape(sprintf(__('Job Ad: %s', 'wpjobads'), $job['title']))?>">
        <input type="hidden" name="item_number" value="<?php echo attribute_escape($job['id']) ?>">
        <input type="hidden" name="amount" value="<?php echo attribute_escape($job['ad_price']) ?>">
        <input type="hidden" name="no_shipping" value="2">
        <input type="hidden" name="no_note" value="1">
        <input type="hidden" name="currency_code" value="<?php echo attribute_escape($job['ad_currency']) ?>">
        <input type="hidden" name="return" value="<?php echo attribute_escape(wpjobads_get_permalink('action=paypal-return')) ?>">
        <input type="hidden" name="notify_url" value="<?php echo attribute_escape($permalink) ?>">
        <input type="hidden" name="rm" value="1">
        <input type="hidden" name="bn" value="PP-BuyNowBF">
        <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
        <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
    </form>
<?php
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
}// }}}

function wpjobads_paypal_ipn()// {{{
{
    $request[] = 'cmd=_notify-validate';
    foreach ($_POST as $key => $value) {
        $request[] = urlencode(stripslashes($key)) . '=' . urlencode(stripslashes($value));
    }
    $job = wpjobads_get_job($_POST['item_number']);
    if (!$job) {
        wpjobads_log('PayPal payment_status incomplete (' . $_POST['payment_status'] . ') for job #' . $job['id'] . ' - "' . $job['title'] . '"');
        header('HTTP/1.1 200 OK');
        exit;
    }
    $wpjobads_options = get_option('wpjobads_options');
    $paypal_url = $wpjobads_options['paypal_url'];
    //list($header, $response) = wpjobads_http_post($paypal_url, implode('&', $request), array(), 30);
    list($header, $response) = wpjobads_paypal_verify(implode('&', $request), array(), 30);
    // make sure we send HTTP 200 response
    header('HTTP/1.1 200 OK');
    if ($response !== false) {
        // check for validation
        if (strcmp(strtoupper($response), 'VERIFIED') == 0) {
            // check that the payment_status is Completed
            if ($_POST['payment_status'] != 'Completed') {
                wpjobads_log('PayPal payment_status incomplete (' . $_POST['payment_status'] . ') for job #' . $job['id'] . ' - "' . $job['title'] . '"');
                exit;
            }
            // check that txn_id has not been previously processed
            if (wpjobads_txn_id_exists($_POST['txn_id'])) {
                wpjobads_log('PayPal txn_id "'.$_POST['txn_id'].'" already exists for job #' . $job['id'] . ' - "' . $job['title'] . '"');
                exit;
            }
            // check that receiver_email is your primary PayPal email
            if ($_POST['receiver_email'] != $wpjobads_options['paypal_email']) {
                wpjobads_log('PayPal receiver_email error for job #' . $job['id'] . ' - "' . $job['title'] . '"');
                exit;
            }
            // check that item_number, payment_amount and payment_currency are all correct
            if ($_POST['item_number'] != $job['id'] or $_POST['mc_gross'] != $job['ad_price'] or $_POST['mc_currency'] != $job['ad_currency']) {
                wpjobads_log('PayPal item_number/payment_amount/payment_currency error for job #' . $job['id'] . ' - "' . $job['title'] . '"');
                exit;
            }

            // set job ad to "paid" and update its txn_id in one go
            wpjobads_set_paid($job, $_POST['txn_id']);

            // send email to advertiser
            wpjobads_send_publish_email($job);

            wpjobads_log(
                'PayPal VERIFIED response for job #' . $job['id'] . ' - "' . $job['title'] . '"',
                'Job #' . $job['id'] . ' - "' . $job['title'] . '" is set to "paid" and an email has been sent to ' . $job['contact_email']
            );
            // done!
            exit;
        } elseif (strcmp(strtoupper($response), 'INVALID') == 0) {
            // silently log for manual investigation later
            wpjobads_log('PayPal INVALID response for job #' . $job['id'] . ' - "' . $job['title'] . '"');
            header('HTTP/1.1 200 OK');
            exit;
        }
    } else {
        wpjobads_log('PayPal unable to receive verification response for job #' . $job['id'] . ' - "' . $job['title'] . '"');
        exit;
    }
}// }}}

function wpjobads_log($log)// {{{
{
    if (!WPJOBADS_WRITE_LOG) return;
    $time = gmdate('H:i:s', time());
    $logs = func_get_args();
    $logs_dir = realpath(ABSPATH . PLUGINDIR . '/wpjobads/logs/');
    if (is_writable($logs_dir)) {
        $log_filename = $logs_dir . DIRECTORY_SEPARATOR . gmdate('Y-m-d', time()) . '.txt';
        $fp = fopen($log_filename, 'a+');
        if ($fp) {
            foreach ($logs as $log)
                fwrite($fp, "$time - $log\n");
            fclose($fp);
        }
    }
}// }}}

function wpjobads_paypal_return()// {{{
{
    ob_start();
?>
    <p><?php _e('Thank you for your purchase. Your payment is being processed.', 'wpjobads') ?></p>
<?php
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
}// }}}

function wpjobads_txn_id_exists($txn_id)// {{{
{
    global $wpdb;
    $table_job = $wpdb->prefix . WPJOBADS_JOB;
    $txn_id = $wpdb->escape($txn_id);
    $total = $wpdb->get_var("SELECT COUNT(txn_id) AS total FROM $table_job WHERE txn_id = '$txn_id'");
    return $total != '0' ? true : false;
}// }}}

// ----------------------------------------------------------------------------

// Mail functions

function wpjobads_send_payment_email($job)// {{{
{
    if (!WPJOBADS_SEND_EMAIL) return true;
    $wpjobads_options = get_option('wpjobads_options');
    $to = $job['contact_email'];
    $subject = $wpjobads_options['payment_email_subject'];
    $message = $wpjobads_options['payment_email_message'];

    $subject = preg_replace('/%job_title%/', $job['title'], $subject);
    $subject = preg_replace('/%contact_name%/', $job['contact_name'], $subject);

    $message = preg_replace('/%job_title%/', $job['title'], $message);
    $message = preg_replace('/%payment_url%/', wpjobads_get_permalink('action=paypal&job_id=' . $job['id']), $message);
    $message = preg_replace('/%payment_link%/', '<a href="' . wpjobads_get_permalink('action=paypal&job_id=' . $job['id']) . '">' . attribute_escape($job['title']) . '</a>', $message);
    $message = preg_replace('/%company_name%/', $job['company_name'], $message);
    $message = preg_replace('/%company_url%/', $job['company_url'], $message);
    $message = preg_replace('/%contact_name%/', $job['contact_name'], $message);
    $message = preg_replace('/%contact_email%/', $job['contact_email'], $message);
    $message = preg_replace('/%ad_expiration%/', $job['expired'], $message);

    add_filter('wp_mail_from_name', 'wpjobads_mail_from_name');
    add_filter('wp_mail_from', 'wpjobads_mail_from');
    $sent = wp_mail($to, $subject, $message, $headers = '');
    remove_filter('wp_mail_from_name', 'wpjobads_mail_from_name');
    remove_filter('wp_mail_from', 'wpjobads_mail_from');
    return $sent;
}// }}}

function wpjobads_send_publish_email($job)// {{{
{
    if (!WPJOBADS_SEND_EMAIL) return true;
    $wpjobads_options = get_option('wpjobads_options');
    $to = $job['contact_email'];
    $subject = $wpjobads_options['publish_email_subject'];
    $message = $wpjobads_options['publish_email_message'];

    $subject = preg_replace('/%job_title%/', $job['title'], $subject);
    $subject = preg_replace('/%contact_name%/', $job['contact_name'], $subject);

    $message = preg_replace('/%job_title%/', $job['title'], $message);
    $message = preg_replace('/%ad_url%/', wpjobads_get_permalink('job_id=' . $job['id']), $message);
    $message = preg_replace('/%ad_link%/', '<a href="' . wpjobads_get_permalink('job_id=' . $job['id']) . '">' . attribute_escape($job['title']) . '</a>', $message);
    $message = preg_replace('/%company_name%/', $job['company_name'], $message);
    $message = preg_replace('/%company_url%/', $job['company_url'], $message);
    $message = preg_replace('/%contact_name%/', $job['contact_name'], $message);
    $message = preg_replace('/%contact_email%/', $job['contact_email'], $message);
    $message = preg_replace('/%ad_expiration%/', $job['expired'], $message);

    add_filter('wp_mail_from_name', 'wpjobads_mail_from_name');
    add_filter('wp_mail_from', 'wpjobads_mail_from');
    $sent = wp_mail($to, $subject, $message, $headers = '');
    remove_filter('wp_mail_from_name', 'wpjobads_mail_from_name');
    remove_filter('wp_mail_from', 'wpjobads_mail_from');
    return $sent;
}// }}}

function wpjobads_send_notification_email($job)// {{{
{
    if (!WPJOBADS_SEND_EMAIL) return true;
    $wpjobads_options = get_option('wpjobads_options');
    $to = $wpjobads_options['email_notification'];
    $subject = $wpjobads_options['notification_email_subject'];
    $message = $wpjobads_options['notification_email_message'];

    $subject = preg_replace('/%job_id%/', $job['id'], $subject);
    $subject = preg_replace('/%job_title%/', $job['title'], $subject);

    $message = preg_replace('/%job_id%/', $job['id'], $message);
    $message = preg_replace('/%job_title%/', $job['title'], $message);
    $message = preg_replace('/%company_name%/', $job['company_name'], $message);
    $message = preg_replace('/%company_url%/', $job['company_url'], $message);
    $message = preg_replace('/%contact_name%/', $job['contact_name'], $message);
    $message = preg_replace('/%contact_email%/', $job['contact_email'], $message);
    $message = preg_replace('/%ad_expiration%/', $job['expired'], $message);

    add_filter('wp_mail_from_name', 'wpjobads_mail_from_name');
    add_filter('wp_mail_from', 'wpjobads_mail_from');
    $sent = wp_mail($to, $subject, $message, $headers = '');
    remove_filter('wp_mail_from_name', 'wpjobads_mail_from_name');
    remove_filter('wp_mail_from', 'wpjobads_mail_from');
    return $sent;
}// }}}

function wpjobads_mail_from_name($value)// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    return $wpjobads_options['email_from_name'];
}// }}}

function wpjobads_mail_from($value)// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    return $wpjobads_options['email_from'];
}// }}}

// ----------------------------------------------------------------------------

// Widget functions

function wpjobads_widget_init()// {{{
{
    if (!function_exists('wp_register_sidebar_widget') or !function_exists('wp_register_widget_control')) {
        return;
    } else {
        wp_register_sidebar_widget('wpjobads', 'WPJobAds', 'wpjobads_widget', array('classname' => 'widget_wpjobads', 'description' => __('WPJobAds list of job categories', 'wpjobads')));
        wp_register_widget_control('wpjobads', 'WPJobAds', 'wpjobads_widget_control');

        wp_register_sidebar_widget('wpjobads_search', 'WPJobAds Search', 'wpjobads_widget_search', array('classname' => 'widget_wpjobads', 'description' => __('WPJobAds search widget', 'wpjobads')));
    }
}// }}}

add_action('plugins_loaded', 'wpjobads_widget_init');

function wpjobads_widget($args)// {{{
{
    extract($args);

    $wpjobads_options = get_option('wpjobads_options');

    $widget_title = empty($wpjobads_options['widget_title']) ? attribute_escape(__('Job Board', 'wpjobads')) : attribute_escape($wpjobads_options['widget_title']);
    $widget_invite = empty($wpjobads_options['widget_invite']) ? attribute_escape(__('Post new job', 'wpjobads')) : attribute_escape($wpjobads_options['widget_invite']);

    $categories = wpjobads_get_all_categories();
    $out = '<li><a href="' . wpjobads_get_permalink() . '">' . __('All Jobs', 'wpjobads') . '</a></li>';
    foreach ($categories as $cat) {
        $out .= '<li><a href="' . wpjobads_get_permalink('jobcat=' . attribute_escape($cat['id'])) . '">' . attribute_escape($cat['name']) . '</a></li>';
    }
    if ($wpjobads_options['enable_frontend'])
        $out .= '<p><a href="' . wpjobads_get_permalink('action=postjob') . '">' . $widget_invite . '</a></p>';
    $out .= '<p>' . sprintf(__('Powered by %s', 'wpjobads'), '<a href="http://www.wpjobads.com">WPJobAds</a>') . '</p>';
?>
    <?php echo $before_widget ?>
    <?php echo $before_title . $widget_title . $after_title ?>
    <ul>
        <?php echo $out ?>
    </ul>
    <?php echo $after_widget ?>
<?php
}// }}}

function wpjobads_widget_control()// {{{
{
    $wpjobads_options = get_option('wpjobads_options');
    if ($_POST['wpjobads-submit']) {
        $new_options = $wpjobads_options;
        $widget_title = strip_tags(stripslashes($_POST['wpjobads-title']));
        if ($wpjobads_options['widget_title'] != $widget_title) {
            $new_options['widget_title'] = $widget_title;
        }
        $invite = strip_tags(stripslashes($_POST['wpjobads-invite']));
        if ($wpjobads_options['widget_invite'] != $invite) {
            $new_options['widget_invite'] = $invite;
        }
        if ($wpjobads_options != $new_options) {
            $wpjobads_options = $new_options;
            update_option('wpjobads_options', $wpjobads_options);
        }
    }
?>
    <p><label for="wpjobads-title"><?php _e('Title:', 'wpjobads'); ?></label> <input id="wpjobads-title" class="widefat" name="wpjobads-title" type="text" value="<?php echo attribute_escape($wpjobads_options['widget_title']) ?>" /></p>
    <p><label for="wpjobads-invite"><?php _e('Invite:', 'wpjobads'); ?></label> <input id="wpjobads-invite" class="widefat" name="wpjobads-invite" type="text" value="<?php echo attribute_escape($wpjobads_options['widget_invite']) ?>" /></p>
    <input type="hidden" id="wpjobads-submit" name="wpjobads-submit" value="1" />
<?php
}// }}}

function wpjobads_widget_search($args)// {{{
{
    extract($args);
    $search_url = get_option('home');
    $wpjobads_options = get_option('wpjobads_options');
?>
    <form method="get" action="<?php echo attribute_escape($search_url) ?>">
        <label class="hidden" for="jobsearch"><?php _e('Search Job:', 'wpjobads') ?></label>
        <div>
            <input type="hidden" value="<?php echo attribute_escape($wpjobads_options['post_id']) ?>" name="page_id" />
            <input type="text" value="<?php echo attribute_escape($_GET['search']) ?>" name="search" id="search" />
            <input type="submit" value="<?php _e('Search Job', 'wpjobads') ?>" id="jobsearchsubmit" />
        </div>
    </form>
<?php
}// }}}

function wpjobads_rss($args)// {{{
{
    $wpjobads_rss_url = wpjobads_get_permalink('jobfeed=rss2');
?>
    <li><a href="<?php echo attribute_escape($wpjobads_rss_url) ?>" title="<?php echo attribute_escape(__('Syndicate this site using RSS 2.0')); ?>"><?php _e('Jobs <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
<?php
}// }}}

add_action('wp_meta', 'wpjobads_rss');

