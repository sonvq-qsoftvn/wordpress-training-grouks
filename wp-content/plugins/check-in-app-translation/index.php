<?php

/*
  Plugin Name: Tickera Check-in App Translation
  Plugin URI: http://tickera.com/
  Description: Translate Tickera check-in apps
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0.4
  Copyright 2015 Tickera (http://tickera.com/)
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly


if ( !class_exists( 'TC_Check_in_app_translation' ) ) {

	class TC_Check_in_app_translation {

		var $version		 = '1.0.4';
		var $title		 = 'Check-in App Translation';
		var $name		 = 'tc-check-in-app-translation';
		var $dir_name	 = 'check-in-app-translation';
		var $location	 = 'plugins';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->init_vars();
			add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
			add_action( 'tc_settings_menu_tickera_check_in_app_translation', array( &$this, 'tc_settings_menu_tickera_check_in_app_translation_show_page' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_header' ) );
			add_filter( 'tc_translation_data_output', array( &$this, 'tc_translation_data_output_translated' ) );
			add_filter( 'tc_check_in_status_title', array( &$this, 'tc_check_in_status_title_translated' ) );
			add_filter( 'tc_ticket_checkin_custom_field_title', array( &$this, 'tc_ticket_checkin_custom_field_title_translated' ) );
		}

		function init_vars() {
//setup proper directories
			if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename( __FILE__ ) ) ) {
				$this->location		 = 'subfolder-plugins';
				$this->plugin_dir	 = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
				$this->plugin_url	 = plugins_url( '/', __FILE__ );
			} else if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
				$this->location		 = 'plugins';
				$this->plugin_dir	 = WP_PLUGIN_DIR . '/';
				$this->plugin_url	 = plugins_url( '/', __FILE__ );
			} else if ( is_multisite() && defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
				$this->location		 = 'mu-plugins';
				$this->plugin_dir	 = WPMU_PLUGIN_DIR;
				$this->plugin_url	 = WPMU_PLUGIN_URL;
			} else {
				wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'tc' ), $this->title ) );
			}
		}

		function tc_translation_data_output_translated( $data ) {
			$tc_checkin_api_translation_settings = get_option( 'tc_checkin_api_translation_settings', false );

			$data = array(
				'WORDPRESS_INSTALLATION_URL' => isset( $tc_checkin_api_translation_settings[ 'WORDPRESS_INSTALLATION_URL' ] ) ? $tc_checkin_api_translation_settings[ 'WORDPRESS_INSTALLATION_URL' ] : 'WORDPRESS INSTALLATION URL',
				'API_KEY'					 => isset( $tc_checkin_api_translation_settings[ 'API_KEY' ] ) ? $tc_checkin_api_translation_settings[ 'API_KEY' ] : 'API KEY',
				'AUTO_LOGIN'				 => isset( $tc_checkin_api_translation_settings[ 'AUTO_LOGIN' ] ) ? $tc_checkin_api_translation_settings[ 'AUTO_LOGIN' ] : 'AUTO LOGIN',
				'SIGN_IN'					 => isset( $tc_checkin_api_translation_settings[ 'SIGN_IN' ] ) ? $tc_checkin_api_translation_settings[ 'SIGN_IN' ] : 'SIGN IN',
				'SOLD_TICKETS'				 => isset( $tc_checkin_api_translation_settings[ 'SOLD_TICKETS' ] ) ? $tc_checkin_api_translation_settings[ 'SOLD_TICKETS' ] : 'TICKETS SOLD',
				'CHECKED_IN_TICKETS'		 => isset( $tc_checkin_api_translation_settings[ 'CHECKED_IN_TICKETS' ] ) ? $tc_checkin_api_translation_settings[ 'CHECKED_IN_TICKETS' ] : 'CHECKED-IN TICKETS',
				'HOME_STATS'				 => isset( $tc_checkin_api_translation_settings[ 'HOME_STATS' ] ) ? $tc_checkin_api_translation_settings[ 'HOME_STATS' ] : 'Home - Stats',
				'LIST'						 => isset( $tc_checkin_api_translation_settings[ 'LIST' ] ) ? $tc_checkin_api_translation_settings[ 'LIST' ] : 'LIST',
				'SIGN_OUT'					 => isset( $tc_checkin_api_translation_settings[ 'SIGN_OUT' ] ) ? $tc_checkin_api_translation_settings[ 'SIGN_OUT' ] : 'SIGN OUT',
				'CANCEL'					 => isset( $tc_checkin_api_translation_settings[ 'CANCEL' ] ) ? $tc_checkin_api_translation_settings[ 'CANCEL' ] : 'CANCEL',
				'SEARCH'					 => isset( $tc_checkin_api_translation_settings[ 'SEARCH' ] ) ? $tc_checkin_api_translation_settings[ 'SEARCH' ] : 'Search',
				'ID'						 => isset( $tc_checkin_api_translation_settings[ 'ID' ] ) ? $tc_checkin_api_translation_settings[ 'ID' ] : 'ID',
				'PURCHASED'					 => isset( $tc_checkin_api_translation_settings[ 'PURCHASED' ] ) ? $tc_checkin_api_translation_settings[ 'PURCHASED' ] : 'PURCHASED',
				'CHECKINS'					 => isset( $tc_checkin_api_translation_settings[ 'CHECKINS' ] ) ? $tc_checkin_api_translation_settings[ 'CHECKINS' ] : 'CHECK-INS',
				'CHECK_IN'					 => isset( $tc_checkin_api_translation_settings[ 'CHECK_IN' ] ) ? $tc_checkin_api_translation_settings[ 'CHECK_IN' ] : 'CHECK IN',
				'SUCCESS'					 => isset( $tc_checkin_api_translation_settings[ 'SUCCESS' ] ) ? $tc_checkin_api_translation_settings[ 'SUCCESS' ] : 'SUCCESS',
				'SUCCESS_MESSAGE'			 => isset( $tc_checkin_api_translation_settings[ 'SUCCESS_MESSAGE' ] ) ? $tc_checkin_api_translation_settings[ 'SUCCESS_MESSAGE' ] : 'Ticket has been check in',
				'OK'						 => isset( $tc_checkin_api_translation_settings[ 'OK' ] ) ? $tc_checkin_api_translation_settings[ 'OK' ] : 'OK',
				'ERROR'						 => isset( $tc_checkin_api_translation_settings[ 'ERROR' ] ) ? $tc_checkin_api_translation_settings[ 'ERROR' ] : 'ERROR',
				'ERROR_MESSAGE'				 => isset( $tc_checkin_api_translation_settings[ 'ERROR_MESSAGE' ] ) ? $tc_checkin_api_translation_settings[ 'ERROR_MESSAGE' ] : 'Wrong ticket code',
				'PASS'						 => isset( $tc_checkin_api_translation_settings[ 'PASS' ] ) ? $tc_checkin_api_translation_settings[ 'PASS' ] : 'Pass',
				'FAIL'						 => isset( $tc_checkin_api_translation_settings[ 'FAIL' ] ) ? $tc_checkin_api_translation_settings[ 'FAIL' ] : 'Fail',
				'ERROR_LOADING_DATA'		 => isset( $tc_checkin_api_translation_settings[ 'ERROR_LOADING_DATA' ] ) ? $tc_checkin_api_translation_settings[ 'ERROR_LOADING_DATA' ] : 'Error loading data. Please check the URL and API KEY provided',
				'API_KEY_LOGIN_ERROR'		 => isset( $tc_checkin_api_translation_settings[ 'API_KEY_LOGIN_ERROR' ] ) ? $tc_checkin_api_translation_settings[ 'API_KEY_LOGIN_ERROR' ] : 'Error. Please check the URL and API KEY provided',
				'APP_TITLE'					 => isset( $tc_checkin_api_translation_settings[ 'APP_TITLE' ] ) ? $tc_checkin_api_translation_settings[ 'APP_TITLE' ] : 'Ticket Check-in',
				'TICKET_TYPE'				 => isset( $tc_checkin_api_translation_settings[ 'TICKET_TYPE' ] ) ? $tc_checkin_api_translation_settings[ 'TICKET_TYPE' ] : 'Ticket Type',
				'BUYER_NAME'				 => isset( $tc_checkin_api_translation_settings[ 'BUYER_NAME' ] ) ? $tc_checkin_api_translation_settings[ 'BUYER_NAME' ] : 'Buyer Name',
				'BUYER_EMAIL'				 => isset( $tc_checkin_api_translation_settings[ 'BUYER_EMAIL' ] ) ? $tc_checkin_api_translation_settings[ 'BUYER_EMAIL' ] : 'Buyer E-mail',
				'PLEASE_WAIT'				 => isset( $tc_checkin_api_translation_settings[ 'PLEASE_WAIT' ] ) ? $tc_checkin_api_translation_settings[ 'PLEASE_WAIT' ] : 'Please wait...',
				'EMPTY_LIST'				 => isset( $tc_checkin_api_translation_settings[ 'EMPTY_LIST' ] ) ? $tc_checkin_api_translation_settings[ 'EMPTY_LIST' ] : 'The list is empty...',
				'BARCODE_SCAN_INFO'			 => isset( $tc_checkin_api_translation_settings[ 'BARCODE_SCAN_INFO' ] ) ? $tc_checkin_api_translation_settings[ 'BARCODE_SCAN_INFO' ] : 'Select input field and scan a barcode',
				'CHECK_IN_RECORDS_SYNCED'	 => isset( $tc_checkin_api_translation_settings[ 'CHECK_IN_RECORDS_SYNCED' ] ) ? $tc_checkin_api_translation_settings[ 'CHECK_IN_RECORDS_SYNCED' ] : 'check-in records synced with the online database successfully.',
				'ATTENDEES_DOWNLOADED'		 => isset( $tc_checkin_api_translation_settings[ 'ATTENDEES_DOWNLOADED' ] ) ? $tc_checkin_api_translation_settings[ 'ATTENDEES_DOWNLOADED' ] : 'Attendees and tickets data has been downloaded successfully.',
				'INFO'						 => isset( $tc_checkin_api_translation_settings[ 'INFO' ] ) ? $tc_checkin_api_translation_settings[ 'INFO' ] : 'Info',
			);
			return $data;
		}

		function tc_check_in_status_title_translated( $string ) {
			$tc_checkin_api_translation_settings = get_option( 'tc_checkin_api_translation_settings', false );

			if ( $string == 'Pass' ) {
				$string = isset( $tc_checkin_api_translation_settings[ 'PASS' ] ) ? $tc_checkin_api_translation_settings[ 'PASS' ] : 'Pass';
			}

			if ( $string == 'Fail' ) {
				$string = isset( $tc_checkin_api_translation_settings[ 'FAIL' ] ) ? $tc_checkin_api_translation_settings[ 'FAIL' ] : 'Fail';
			}

			return $string;
		}

		function tc_ticket_checkin_custom_field_title_translated( $string ) {
			$tc_checkin_api_translation_settings = get_option( 'tc_checkin_api_translation_settings', false );

			if ( $string == 'Ticket Type' ) {
				$string = isset( $tc_checkin_api_translation_settings[ 'TICKET_TYPE' ] ) ? $tc_checkin_api_translation_settings[ 'TICKET_TYPE' ] : $string;
			}

			if ( $string == 'Buyer Name' ) {
				$string = isset( $tc_checkin_api_translation_settings[ 'BUYER_NAME' ] ) ? $tc_checkin_api_translation_settings[ 'BUYER_NAME' ] : $string;
			}

			if ( $string == 'Buyer E-mail' ) {
				$string = isset( $tc_checkin_api_translation_settings[ 'BUYER_EMAIL' ] ) ? $tc_checkin_api_translation_settings[ 'BUYER_EMAIL' ] : $string;
			}

			return $string;
		}

		function tc_settings_new_menus_additional( $settings_tabs ) {
			$settings_tabs[ 'tickera_check_in_app_translation' ] = __( 'Check-in App Translation', 'tc' );
			return $settings_tabs;
		}

		function tc_settings_menu_tickera_check_in_app_translation_show_page() {
			require_once( $this->plugin_dir . 'includes/admin-pages/settings-tickera_check_in_app_translation.php' );
		}

		function admin_header() {
			wp_enqueue_style( $this->name . '-admin', $this->plugin_url . 'css/admin.css', array(), $this->version );
		}

	}

}

$TC_Check_in_app_translation = new TC_Check_in_app_translation();

//Addon updater 
if ( function_exists( 'tc_plugin_updater' ) ) {
	tc_plugin_updater( 'check-in-app-translation', __FILE__ );
}
?>