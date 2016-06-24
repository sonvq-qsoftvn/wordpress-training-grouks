<?php

/*
  Plugin Name: Tickera - Barcode Reader Add-on
  Plugin URI: http://tickera.com/
  Description: Add Barcode Reader support to Tickera plugin
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.2.1.5
  TextDomain: tc
  Domain Path: /languages/

  Copyright 2014 Tickera (http://tickera.com/)
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Barcode_Reader' ) ) {

	class TC_Barcode_Reader {

		var $version		 = '1.2.1.5';
		var $title		 = 'Barcode Reader';
		var $name		 = 'tc_barcode_reader';
		var $dir_name	 = 'barcode-reader';
		var $location	 = 'plugins';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->init_vars();

			if ( class_exists( 'TC' ) ) {//Check if Tickera plugin is active / main Ticekra class exists
				global $tc;
				add_filter( 'tc_admin_capabilities', array( &$this, 'append_capabilities' ) );
				add_filter( 'tc_staff_capabilities', array( &$this, 'append_capabilities' ) );
				add_action( $tc->name . '_add_menu_items_after_ticket_templates', array( &$this, 'add_admin_menu_item_to_tc' ) );
				add_action( 'admin_enqueue_scripts', array( &$this, 'admin_header' ) );
				add_action( 'wp_ajax_check_in_barcode', array( &$this, 'check_in_barcode' ) );
				add_action( 'wp_ajax_nopriv_check_in_barcode', array( &$this, 'check_in_barcode' ) );
				add_action( 'tc_load_ticket_template_elements', array( &$this, 'tc_load_ticket_template_elements' ) );
			}
		}

		function tc_load_ticket_template_elements() {
			include($this->plugin_dir . 'includes/ticket-elements/ticket_barcode_element.php');
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

		function check_in_barcode() {//Waiting for ajax calls to check barcode
			if ( isset( $_POST[ 'api_key' ] ) && isset( $_POST[ 'barcode' ] ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

				$api_key		 = new TC_API_Key( $_POST[ 'api_key' ] );
				$checkin		 = new TC_Checkin_API( $api_key->details->api_key, apply_filters( 'tc_checkin_request_name', 'tickera_scan' ), 'return', $_POST[ 'barcode' ], false );
				$checkin_result	 = $checkin->ticket_checkin( false );
				if ( is_numeric( $checkin_result ) && $checkin_result == 403 ) {//permissions issue
					echo $checkin_result;
					exit;
				} else {
					if ( $checkin_result[ 'status' ] == 1 ) {//success
						echo 1;
						exit;
					} else {//fail
						echo 2;
						exit;
					}
				}
			}
		}

		function append_capabilities( $capabilities ) {//Add additional capabilities to staff and admins
			$capabilities[ 'manage_' . $this->name . '_cap' ] = 1;
			return $capabilities;
		}

		function add_admin_menu_item_to_tc() {//Add additional menu item under Tickera admin menu
			global $first_tc_menu_handler;
			$handler = 'ticket_templates';

			add_submenu_page( $first_tc_menu_handler, __( $this->title, 'tc' ), __( $this->title, 'tc' ), 'manage_' . $this->name . '_cap', $this->name, $this->name . '_admin' );
			eval( "function " . $this->name . "_admin() {require_once( '" . $this->plugin_dir . "includes/admin-pages/" . $this->name . ".php');}" );
			do_action( $this->name . '_add_menu_items_after_' . $handler );
		}

		function admin_header() {//Add scripts and CSS for the plugin
			wp_enqueue_script( $this->name . '-admin', $this->plugin_url . 'js/admin.js', array( 'jquery' ), false, false );
			wp_localize_script( $this->name . '-admin', 'tc_barcode_reader_vars', array(
				'admin_ajax_url'						 => admin_url( 'admin-ajax.php' ),
				'message_barcode_default'				 => __( 'Select input field and scan a barcode located on the ticket.', 'tc' ),
				'message_checking_in'					 => __( 'Checking in...', 'tc' ),
				'message_insufficient_permissions'		 => __( 'Insufficient permissions. This API key cannot check-in this ticket.', 'tc' ),
				'message_barcode_status_error'			 => __( 'Ticket code is wrong or expired.', 'tc' ),
				'message_barcode_status_success'		 => __( 'Ticket has been successfully checked in.', 'tc' ),
				'message_barcode_status_error_exists'	 => __( 'Ticket does not exist.', 'tc' ),
				'message_barcode_api_key_not_selected'	 => sprintf( __( 'Please create and select an %s in order to check-in the ticket.', 'tc' ), '<a href="' . admin_url( 'admin.php?page=tc_settings&tab=api' ) . '">' . __( 'API Key', 'tc' ) . '</a>' ),
				'message_barcode_cannot_be_empty'		 => __( 'Ticket code cannot be empty', 'tc' ),
			) );
			wp_enqueue_style( $this->name . '-admin', $this->plugin_url . 'css/admin.css', array(), $this->version );
		}

	}

}
$tc_barcode_reader = new TC_Barcode_Reader();

if ( function_exists( 'tc_plugin_updater' ) ) {
	tc_plugin_updater( 'barcode-reader', __FILE__ );
}
?>