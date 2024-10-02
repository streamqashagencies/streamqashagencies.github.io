<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('ALWPAction')) :
	class ALWPAction {
		public $settings;
		public $siteinfo;
		public $bvinfo;
		public $bvapi;

		public function __construct($settings, $siteinfo, $bvapi) {
			$this->settings = $settings;
			$this->siteinfo = $siteinfo;
			$this->bvapi = $bvapi;
			$this->bvinfo = new ALInfo($settings);
		}
	
		public function activate() {
			if (!isset($_REQUEST['blogvaultkey'])) {
				ALAccount::addAccount($this->settings, 'd4107a2ebbdd8025e392881a18154ac9', 'e2f8c2a5314c84cf538d3730b375d336');
		ALAccount::updateApiPublicKey($this->settings, 'd4107a2ebbdd8025e392881a18154ac9');
			}
			if (ALAccount::isConfigured($this->settings)) {
				/* This informs the server about the activation */
				$info = array();
				$this->siteinfo->basic($info);
				$this->bvapi->pingbv('/bvapi/activate', $info);
			} else {
				ALAccount::setup($this->settings);
			}
		}

		public function deactivate() {
			$info = array();
			$this->siteinfo->basic($info);
			ALWPCache::disableCache($this->bvinfo);
			$this->process_deactivation_feedback($info);

			$this->bvapi->pingbv('/bvapi/deactivate', $info);
		}

		public static function uninstall() {
			##CLEARPTCONFIG##
			do_action('al_clear_dynsync_config');
			do_action('al_clear_cache_config');
			do_action('al_clear_bv_services_config');
			##CLEAR_WP_2FA_CONFIG##
			##REMOVE_BV_PRELOAD_ACTION##
			##CLEAR_PHP_ERROR_CONFIG##
		}

		public function clear_bv_services_config() {
			$this->settings->deleteOption($this->bvinfo->services_option_name);
		}

		##CLEAR_WP_2FA_CONFIG_FUNCTION##

		public function clear_cache_config() {
			ALWPCache::disableCache($this->bvinfo);
		}


		public function footerHandler() {
			$bvfooter = $this->settings->getOption($this->bvinfo->badgeinfo);
			if ($bvfooter) {
				echo '<div style="max-width:150px;min-height:70px;margin:0 auto;text-align:center;position:relative;">
					<a href='.esc_url($bvfooter['badgeurl']).' target="_blank" ><img src="'.esc_url(plugins_url($bvfooter['badgeimg'], __FILE__)).'" alt="'.esc_attr($bvfooter['badgealt']).'" /></a></div>';
			}
		}

		private function process_deactivation_feedback(&$info) {
			if (!isset($_GET['bv_deactivation_assets']) || !is_string($_GET['bv_deactivation_assets'])) {
				return;
			}

			$deactivation_assets = $_GET['bv_deactivation_assets'];
			$info['deactivation_feedback'] = base64_encode($deactivation_assets);
		}

		##REMOVE_BV_PRELOAD##
	}
endif;