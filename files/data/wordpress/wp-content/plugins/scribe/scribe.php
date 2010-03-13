<?php
/*
 Plugin Name: Scribe
 Plugin URI: http://scribeseo.com
 Description: Quickly and easily check your content against SEO best practices utilizing the Scribe Content Optimizer.  You will need a <a href="https://my.scribeseo.com" title="Get Scribe API key">Scribe API Key</a> in order to use the application. If you do not have an API Key, go to <a href="http://scribeseo.com" title="Get Scribe API Key">http://scribeseo.com</a>. Requires one of the following installed and activated - <a href="http://www.diythemes.com" title="Thesis Theme for WordPress">Thesis</a>, <a href="http://themehybrid.com" title="Hybrid Theme">Hybrid</a>, <a href="http://www.headwaythemes.com" title="Headway Theme for WordPress">Headway</a> or <a href="http://wordpress.org/extend/plugins/all-in-one-seo-pack/" title="All In One SEO">All in One SEO Pack</a> plugin.
 Version: 1.0.10
 Author: Scribe
 Author URI: http://scribeseo.com
 */

define( 'ECORDIA_DEBUG', false );

include ('lib/ecordia-access/ecordia-content-optimizer.class.php');
include ('lib/ecordia-access/ecordia-user-account.class.php');
if (!function_exists('json_encode') && file_exists(ABSPATH.'/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php')) {
	require_once (ABSPATH.'/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php');
	function json_encode($data) {
		$json == new Moxiecode_JSON();
		return $json->encode($data);
	}
}

if (!class_exists('Ecordia')) {
	class Ecordia {

		var $version = '1.0.10';
		var $_meta_seoInfo = '_ecordia_seo_info';
		var $_option_ecordiaSettings = '_ecordia_settings';
		var $_option_cachedUserInfo = '_ecordia_cachedUserInfo';
		var $settings = null;

		function Ecordia() {
			$this->addActions();
			$this->addFilters();


			wp_register_style('ecordia', plugins_url('resources/ecordia.css', __FILE__), array(), $this->version);
			wp_register_script('ecordia', plugins_url('resources/ecordia.js', __FILE__), array('jquery'), $this->version);
		}

		function addActions() {
			add_action('admin_head', array(&$this, 'addAdminHeaderCode'));
			add_action('admin_init', array(&$this, 'settingsSave'));
			add_action('admin_menu', array(&$this, 'addAdminInterfaceItems'));
			add_action('manage_posts_custom_column', array(&$this, 'displayEcordiaPostsColumns'), 10, 2);
			add_action('save_post', array(&$this, 'saveSerializedValueToPreventOverriding'), 10, 2);

			$settings = $this->getSettings();
			if ( empty($settings['api-key'])) {
				add_action('admin_notices', array(&$this, 'displayAdminNoticeRegardingAPIKey'));
			}

			// Thickbox interfaces
			add_action('media_upload_ecordia-score', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-keyword-analysis', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-change-keywords', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-tags', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-serp', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-seo-best-practices', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-error', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-debug', array(&$this, 'displayThickboxInterface'));

			// AJAX stuff
			add_action('wp_ajax_ecordia_analyze', array(&$this, 'analyzeSeoContent'));
			add_action('wp_ajax_ecordia_user_info', array(&$this, 'fetchUserInfo'));
		}

		function addFilters() {
			add_filter('manage_posts_columns', array(&$this, 'addEcordiaPostsColumns'));
		}

		function addAdminHeaderCode() {
			global $pagenow;
			if (false !== strpos($pagenow, 'post') || false !== strpos($pagenow, 'page') || false !== strpos($pagenow, 'media-upload')) {
				include ('views/admin-header.php');
			}
		}

		function addAdminInterfaceItems() {
			add_options_page(__('Scribe Settings'), __('Scribe Settings'), 'manage_options', 'scribe', array(&$this, 'displaySettingsPage'));

			$dependency = $this->getEcordiaDependency();
			$title = __('Scribe Content Optimizer');
			if ( empty($dependency)) {
				$displayFunction = array(&$this, 'displayMetaBoxError');
				$title .= __(' &mdash; <span class="ecordia-error">Error</span>');
			} else {
				$displayFunction = array(&$this, 'displayMetaBox');
			}
			add_meta_box('ecordia', $title, $displayFunction, 'post', 'side', 'core');
			add_meta_box('ecordia', $title, $displayFunction, 'page', 'side', 'core');

			global $pagenow;
			if (false !== strpos($pagenow, 'post') || false !== strpos($pagenow, 'page') || false !== strpos($pagenow, 'scribe') || $_GET['page'] == 'scribe' || false !== strpos($pagenow, 'edit')) {
				wp_enqueue_style('ecordia');
				wp_enqueue_script('ecordia');
				add_filter('tiny_mce_before_init', array(&$this, 'addInitInstanceCallback'));
			}
		}

		function addEcordiaPostsColumns($columns) {
			$authorKey = array_search('author', array_keys($columns));
			//removed preserve_keys parameter for lt PHP5.0.2
			$before = array_slice($columns, 0, $authorKey + 1);
			$before['seo-score'] = __('Scribe Optimizer');
			$before['primary-seo-keywords'] = __('Primary Keywords');
				
			//removed preserve_keys parameter for lt PHP5.0.2
			$after = array_slice($columns, $authorKey + 1, count($columns) + 1);
			$columns = array_merge($before, $after);
			return $columns;
		}

		function addInitInstanceCallback($initArray) {
			$initArray['init_instance_callback'] = 'ecordia_addTinyMCEEvent';
			return $initArray;
		}

		function analyzeSeoContent() {
			$title = trim(stripslashes($_POST['title']));
			$description = trim(stripslashes($_POST['description']));
			$content = trim(stripslashes($_POST['content']));
			$url = site_url('/');
			$pid = intval($_POST['pid']);
			$settings = $this->getSettings();
			$results = array('success'=>false, 'message'=>__(''), 'extended'=>__(''));

			if ( empty($settings['api-key'])) {
				$results['message'] = __('You need to set your API key.');
				$results['extended'] = 'show-settings-prompt';
			} else {
				$optimizer = new EcordiaContentOptimizer($settings['api-key'], $settings['use-ssl']);
				$optimizer->GetAnalysis($title, $description, $content, $url);
				if ($optimizer->hasError()) {
					$results['message'] = __('Analysis Failure');
					$results['extended'] = $optimizer->getErrorMessage();
				} else {
					$serialized = base64_encode(serialize($optimizer->getRawResults()));
					update_post_meta($pid, $this->_meta_seoInfo, $serialized);
					$results['success'] = true;
					ob_start();
					global $post;
					$post = get_post($pid);
					include ('views/meta-box/after.php');
					$results['meta'] = ob_get_clean();
				}
			}


			print json_encode($results);
			exit();
		}

		function displayAdminNoticeRegardingAPIKey() {
			print '<div id="ecordia-empty-api-key" class="error"><p>'.sprintf(__('Your Scribe API Key is Empty.  Please <a href="%s">configure the Scribe Content Optimizer plugin</a>.'), admin_url('options-general.php?page=scribe')).'</p></div>';
		}

		function displayEcordiaPostsColumns($columnName, $postId) {
			switch ($columnName) {
				case 'seo-score':
					$score = $this->getSeoScoreForPost($postId);
					if ($score) {
						printf(__('<span class="%1$s">%2$s%%</span>'), $this->getSeoScoreClassForPost($score), $score);
					} else {
						_e('NA');
					}
					break;
				case 'primary-seo-keywords':
					$keywords = $this->getSeoPrimaryKeywordsForPost($postId);
					if (is_array($keywords)) {
						if ( empty($keywords)) {
							echo '<span class="ecordia-error">'.__('None').'</span>';
						} else {
							$output = '<ul>';
							foreach ($keywords as $keyword) {
								$output .= "<li>{$keyword}</li>";
							}
							$output .= '</ul>';
						}
						print $output;
					} else {
						_e('NA');
					}
					break;
			}
		}

		function fetchUserInfo() {
			$userInfo = $this->getUserInfo(true);
			include('views/account-info.php');
			exit();
		}

		
		function saveSerializedValueToPreventOverriding($postId) {
			if(isset( $_POST['serialized-ecordia-results']) && !empty($_POST['serialized-ecordia-results'])) {
				if(false !== (wp_is_post_autosave($postId) || wp_is_post_revision($postId))) {
					return;
				}
				update_post_meta($postId, $this->_meta_seoInfo, stripslashes($_POST['serialized-ecordia-results']));
			}
		}

		// DISPLAY CALLBACKS

		function displayMetaBoxError() {
			include ('views/meta-box/error.php');
		}

		function displayMetaBox() {
			global $post;
			if ($this->postHasBeenAnalyzed($post->ID)) {
				include ('views/meta-box/after.php');
			} else {
				include ('views/meta-box/before.php');
			}
		}

		function displaySettingsPage() {
			include ('views/settings.php');
		}

		function displayThickboxInterface() {
			wp_enqueue_style('ecordia');
			wp_enqueue_script('ecordia');
			wp_enqueue_style('global');
			wp_enqueue_style('media');
			wp_iframe('ecordia_thickbox_include');
		}

		function thickboxInclude() {
			$pages = array('ecordia-score', 'ecordia-keyword-analysis', 'ecordia-change-keywords', 'ecordia-tags', 'ecordia-serp', 'ecordia-seo-best-practices', 'ecordia-error');
			if( defined( 'ECORDIA_DEBUG' ) && ECORDIA_DEBUG ) {
				$pages[] = 'ecordia-debug';
			}
			$tab = in_array($_GET['tab'], $pages) ? $_GET['tab'] : 'ecordia-score';
			$page = str_replace('ecordia-', '', $tab);


			if (false === strpos($tab, 'error')) {
				add_filter('media_upload_tabs', array(&$this, 'thickboxTabs'));
				media_upload_header();
			}

			$info = $this->getSeoInfoForPost($_GET['post']);
			if (false === $info && false === strpos($tab, 'error')) {
				print '<form><p>No analysis present.</p></form>';
				return;
			}

			include ('views/popup/'.$page.'.php');
		}

		function settingsSave() {
			if (isset($_POST['save-ecordia-api-key-information']) && current_user_can('manage_options') && check_admin_referer('save-ecordia-api-key-information')) {
				$settings = $this->getSettings();
				$settings['api-key'] = trim(stripslashes($_POST['ecordia-api-key']));
				$settings['use-ssl'] = stripslashes($_POST['ecordia-connection-method']) == 'https';
				$this->setSettings($settings);
				wp_redirect(admin_url('options-general.php?page=scribe&updated=true'));
				exit();
			}
		}

		function thickboxTabs($tabs) {
			$pages = array('ecordia-score'=>__('SEO Score'), 'ecordia-keyword-analysis'=>__('Keyword Analysis'), 'ecordia-change-keywords'=>__('Change Keywords'), 'ecordia-tags'=>__('Tags'), 'ecordia-serp'=>__('SERP'), 'ecordia-seo-best-practices'=>__('SEO Best Practices'));
			if( defined( 'ECORDIA_DEBUG' ) && ECORDIA_DEBUG ) {
				$pages['ecordia-debug'] = __( 'Debug Info' );
			}
			return $pages;
		}

		// UTILITY - changed the order to support AIOSEO first before themes

		function getEcordiaDependency() {
			$themeName = substr(trim(get_current_theme()),0,6);
			if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
				return 'aioseo';
			} else if (is_plugin_active('all-in-one-seo-pack-pro/all_in_one_seo_pack.php')) {
				return 'aioseo';
			}
			else if (in_array($themeName, array('Thesis', 'Hybrid', 'Headwa'))) {
				return strtolower($themeName);
			}
		}

		function getSeoInfoForPost($postId) {
			$info = get_post_meta($postId, $this->_meta_seoInfo, true);
			if ( empty($info)) {
				$info = false;
			} else {
				if (is_array($info)) {
					$info = base64_encode(serialize($info));
				}
				$info = unserialize(base64_decode($info));
			}
			return $info;
		}

		function getSeoScoreForPost($postId) {
			$info = $this->getSeoInfoForPost($postId);
			if (@is_numeric($info['GetAnalysisResult']['Analysis']['SeoScore']['Score']['Value'])) {
				return intval($info['GetAnalysisResult']['Analysis']['SeoScore']['Score']['Value']);
			}
			return false;
		}

		function getSeoScoreClassForPost($score) {
			$score = intval($score);
			if ($score <= 50) {
				return 'ecordia-score-low';
			} elseif ($score <= 75) {
				return 'ecordia-score-medium';
			} else {
				return 'ecordia-score-high';
			}
		}

		function getSeoPrimaryKeywordsForPost($postId) {
			$info = $this->getSeoInfoForPost($postId);
			if (false === $info) {
				return array();
			} else {
				$allKeywords = (array) $info['GetAnalysisResult']['Analysis']['KeywordAnalysis']['Keywords']['Keyword'];
				$primaryKeywords = array();
				foreach ($allKeywords as $keyword) {
					if ($keyword['Rank'] == 'Primary') {
						$primaryKeywords[] = $keyword['Term'];
					}
				}
				return $primaryKeywords;
			}
		}

		function postHasBeenAnalyzed($postId) {
			return false !== $this->getSeoInfoForPost($postId);
		}

		function getPostSeoData($postId) {
			$seoData = get_post_meta($postId, $this->_meta_seoInfo, true);
			if (!$seoData) {
				return false;
			} else {
				return $seoData;
			}
		}

		function getUserInfo($live = false) {
			$settings = $this->getSettings();
			if ($live) {
				if ( empty($settings['api-key'])) {
					return new WP_Error(-1, __('You must set an API key.'));
				} else {
					$userAccountAccess = new EcordiaUserAccount($settings['api-key'], $settings['use-ssl']);
					$userAccountAccess->UserAccountStatus();
					if ($userAccountAccess->hasError()) {
						return new WP_Error($userAccountAccess->getErrorType(), $userAccountAccess->getErrorMessage() . $userAccountAccess->client->response . '<br /> ' .$userAccountAccess->client->request, $userAccountAccess);
					} else {
						update_option($this->_option_cachedUserInfo, $userAccountAccess);
						return $userAccountAccess;
					}
				}
			} else {
				$userAccountAccess = get_option($this->_option_cachedUserInfo);
				if (!$userAccountAccess) {
					return new WP_Error(-100, __('Fetching Information...'));
				} else {
					return $userAccountAccess;
				}
			}
		}

		function getSettings() {
			if (null === $this->settings) {
				$this->settings = get_option($this->_option_ecordiaSettings, array());
				$this->settings = is_array($this->settings) ? $this->settings : array();
			}
			return $this->settings;
		}

		function setSettings($settings) {
			if (!is_array($settings)) {
				return;
			}
			$this->settings = $settings;
			update_option($this->_option_ecordiaSettings, $this->settings);
		}

		function displaySection($section) {
			include ('views/misc/section-display.php');
		}
	}

	$ecordia = new Ecordia;
	function ecordia_thickbox_include() {
		global $ecordia;
		$ecordia->thickboxInclude();
	}
}
