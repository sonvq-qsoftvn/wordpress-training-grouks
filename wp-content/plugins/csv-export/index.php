<?php

/*
  Plugin Name: Tickera CSV Export
  Plugin URI: http://tickera.com/
  Description: Export attendees data in CSV file format
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.2.3.8
  Copyright 2015 Tickera (http://tickera.com/)
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Export_Csv_Mix' ) ) {

	class TC_Export_Csv_Mix {

		var $version		 = '1.2.3.8';
		var $title		 = 'Tickera CSV Export';
		var $name		 = 'tickera-csv-export';
		var $dir_name	 = 'csv-export';
		var $location	 = 'plugins';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {

			$this->init_vars();

			add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
			add_action( 'tc_settings_menu_tickera_export_csv_mixed_data', array( &$this, 'tc_settings_menu_tickera_export_csv_mixed_data_show_page' ) );
			add_action( 'wp_ajax_tc_export_attendee_list', array( &$this, 'tc_export_attendee_list' ) );
			add_action( 'wp_ajax_tc_export_csv', array( &$this, 'tc_export' ) );

			if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'tc_settings' && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'tickera_export_csv_mixed_data' ) {
				add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
			}
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

		function enqueue_scripts() {

			wp_enqueue_style( $this->name . '-jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( $this->name . '-admin', $this->plugin_url . 'includes/css/admin.css' );

			wp_enqueue_script( 'jquery-ui-progressbar' );
			wp_enqueue_script( $this->name . '-admin', $this->plugin_url . 'includes/js/admin.js', array( 'jquery' ), false, false );

			$admin_url = strtok( admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http' ) ), '?' );
			wp_localize_script( $this->name . '-admin', 'tc_csv_vars', array(
				'ajaxUrl' => $admin_url,
			) );
		}

		function array2csv( array $array ) {
			if ( count( $array ) == 0 ) {
				return null;
			}

			ob_start();
			$df = fopen( "php://output", 'w' );
			fputcsv( $df, array_keys( reset( $array ) ) );
			foreach ( $array as $row ) {
				fputcsv( $df, $row );
			}

			fclose( $df );
			return ob_get_clean();
		}

		function tc_settings_new_menus_additional( $settings_tabs ) {
			$settings_tabs[ 'tickera_export_csv_mixed_data' ] = __( 'Export CSV', 'tc' );
			return $settings_tabs;
		}

		function tc_settings_menu_tickera_export_csv_mixed_data_show_page() {
			require_once( $this->plugin_dir . 'includes/admin-pages/settings-tickera_export_csv_mixed_data.php' );
		}

		function set_max( $value ) {
			if ( $value > 100 ) {
				$value = 100;
			}
			return round( $value, 0 );
		}

		function tc_export_attendee_list() {

			error_reporting( E_ERROR );

			if ( !session_id() ) {
				session_start();
			}

			$order_status = $_POST[ 'tc_limit_order_type' ];

			ini_set( 'max_input_time', 3600 * 3 );
			ini_set( 'max_execution_time', 3600 * 3 );
			set_time_limit( 0 );

			$per_page = apply_filters( 'tc_csv_export_per_page_limit', 50 );

			$page = max( 1, $_POST[ 'page_num' ] );

			$query = new WP_Query( array(
				'cache_results'			 => false,
				'update_post_term_cache' => false,
				'post_type'				 => 'tc_tickets_instances',
				'post_status'			 => 'publish',
				'posts_per_page'		 => $per_page,
				'paged'					 => $page,
			) );

			if ( $page == 1 ) {
				unset( $_SESSION[ 'tc_csv_array' ] );
				$tc_csv_array				 = array();
				$_SESSION[ 'tc_csv_array' ]	 = $tc_csv_array;
			} else {
				$tc_csv_array = $_SESSION[ 'tc_csv_array' ];
			}

			$paids = 0;

			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				//search all the tickets from the event that are confirmed
				$instance	 = new TC_Ticket_Instance( $post_id );
				$ticket_type = new TC_Ticket( apply_filters( 'tc_ticket_type_id', $instance->details->ticket_type_id ) );

				$event_name_meta = apply_filters( 'tc_event_name_field_name', 'event_name' );

				$event_name = $ticket_type->details->{$event_name_meta};

				$tc_event_id = $_POST[ 'tc_export_csv_event_data' ];

				if ( $event_name == $tc_event_id ) {

					$order = new TC_Order( $instance->details->post_parent );

					if ( $order->details->post_status == $order_status || $order_status == 'any' ) {

						$payment_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), apply_filters( 'tc_ticket_checkin_order_date', $order->details->tc_order_date, $order->details->ID ), false );

						//CHECK TO SEE IF OWNER FIRST NAME IS CHECKED
						if ( isset( $_POST[ 'col_owner_first_name' ] ) ) {
							$tc_first_name_array = array( __( 'First Name', 'tc' ) => $instance->details->first_name );
						} else {
							$tc_first_name_array = array();
						}
						do_action( 'tc_export_csv_after_owner_first_name', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF OWNER LAST NAME IS CHECKED
						if ( isset( $_POST[ 'col_owner_last_name' ] ) ) {
							$tc_last_name_array = array( __( 'Last Name', 'tc' ) => $instance->details->last_name );
						} else {
							$tc_last_name_array = array();
						}
						do_action( 'tc_export_csv_after_owner_first_name', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF OWNER NAME IS CHECKED
						if ( isset( $_POST[ 'col_owner_name' ] ) ) {
							$tc_name_array = array( __( 'Name', 'tc' ) => $instance->details->first_name . ' ' . $instance->details->last_name );
						} else {
							$tc_name_array = array();
						}
						do_action( 'tc_export_csv_after_owner_name', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF OWNER EMAIL IS CHECKED
						if ( isset( $_POST[ 'col_owner_email' ] ) ) {
							$tc_owner_email_array = array( __( 'Attendee E-mail', 'tc' ) => $instance->details->owner_email );
						} else {
							$tc_owner_email_array = array();
						}
						do_action( 'tc_export_csv_after_owner_email', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF PAYMENT DATE IS CHECKED
						if ( isset( $_POST[ 'col_payment_date' ] ) ) {
							$tc_payment_array = array( __( 'Payment Date', 'tc' ) => $payment_date );
						} else {
							$tc_payment_array = array();
						}


						// add_action( 'tc_export_csv_after_payment_date', isset( $_POST ) ? $_POST : ''  );

						if ( isset( $_POST[ 'col_order_number' ] ) ) {
							$tc_order_number_array = array( __( 'Order Number', 'tc' ) => apply_filters( 'tc_export_order_number_column_value', $order->details->post_title, $order->details->ID ) );
						} else {
							$tc_order_number_array = array();
						}
						do_action( 'tc_export_csv_after_order_number', isset( $_POST ) ? $_POST : ''  );

						if ( isset( $_POST[ 'col_payment_gateway' ] ) ) {
							$tc_payment_gateway_array = array( __( 'Payment Gateway', 'tc' ) => apply_filters( 'tc_order_payment_gateway_name', isset( $order->details->tc_cart_info[ 'gateway_admin_name' ] ) ? $order->details->tc_cart_info[ 'gateway_admin_name' ] : '', $order->details->ID ) );
						} else {
							$tc_payment_gateway_array = array();
						}
						do_action( 'tc_export_csv_after_payment_gateway', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF DISCOUNT CODE IS CHECKED
						if ( isset( $_POST[ 'col_discount_code' ] ) ) {
							$tc_discount_array = array( __( 'Discount Code', 'tc' ) => $order->details->tc_discount_code );
						} else {
							$tc_discount_array = array();
						}

						do_action( 'tc_export_csv_after_discount_value', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF ORDER STATUS IS CHECKED

						$order_st = '';

						if ( $order->details->post_status == 'order_paid' ) {
							$order_st = __( 'Paid', 'tc' );
						}
						if ( $order->details->post_status == 'order_received' ) {
							$order_st = __( 'Received / Pending', 'tc' );
						}
						if ( $order->details->post_status == 'order_fraud' ) {
							$order_st = __( 'Fraud Detected', 'tc' );
						}

						$order_st = apply_filters( 'tc_order_status_title', $order_st, $order->details->ID, $order->details->post_status );

						if ( isset( $_POST[ 'col_order_status' ] ) ) {
							$tc_order_status_array = array( __( 'Order Status', 'tc' ) => $order_st );
						} else {
							$tc_order_status_array = array();
						}
						do_action( 'tc_export_csv_after_order_status', isset( $_POST ) ? $_POST : ''  );

						if ( isset( $_POST[ 'col_order_total' ] ) ) {
							$order_total			 = round( $order->details->tc_payment_info[ 'total' ], 2 );
							$tc_order_total_array	 = array( __( 'Order Total', 'tc' ) => round( $order_total, 2 ) );
						} else {
							$tc_order_total_array = array();
						}

						do_action( 'tc_export_csv_after_order_total', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF TICKET ID IS CHECKED
						if ( isset( $_POST[ 'col_ticket_id' ] ) ) {
							$tc_ticket_id_array = array( __( 'Ticket Code', 'tc' ) => $instance->details->ticket_code );
						} else {
							$tc_ticket_id_array = array();
						}

						do_action( 'tc_export_csv_after_ticket_id', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF TICKET TYPE IS CHECKED
						if ( isset( $_POST[ 'col_ticket_type' ] ) ) {
							$tc_ticket_type_array = array( __( 'Ticket Type', 'tc' ) => apply_filters( 'tc_checkout_owner_info_ticket_title', $ticket_type->details->post_title, isset( $instance->details->ticket_type_id ) ? $instance->details->ticket_type_id : $ticket_type->details->ID  ) );
						} else {
							$tc_ticket_type_array = array();
						}

						do_action( 'tc_export_csv_after_ticket_type', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF BUYER FIRST NAME IS CHECKED
						if ( isset( $_POST[ 'col_buyer_first_name' ] ) ) {
							$buyer_first_name				 = isset( $order->details->tc_cart_info[ 'buyer_data' ][ 'first_name_post_meta' ] ) ? ($order->details->tc_cart_info[ 'buyer_data' ][ 'first_name_post_meta' ]) : '';
							$tc_buyer_first_name_info_array	 = array( __( 'Buyer First Name', 'tc' ) => apply_filters( 'tc_ticket_checkin_buyer_first_name', $buyer_first_name, $order->details->ID ) );
						} else {
							$tc_buyer_first_name_info_array = array();
						}
						do_action( 'tc_export_csv_after_buyer_first_name', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF BUYER NAME IS CHECKED
						if ( isset( $_POST[ 'col_buyer_last_name' ] ) ) {
							$buyer_last_name				 = isset( $order->details->tc_cart_info[ 'buyer_data' ][ 'last_name_post_meta' ] ) ? ($order->details->tc_cart_info[ 'buyer_data' ][ 'last_name_post_meta' ]) : '';
							$tc_buyer_last_name_info_array	 = array( __( 'Buyer Last Name', 'tc' ) => apply_filters( 'tc_ticket_checkin_buyer_last_name', $buyer_last_name, $order->details->ID ) );
						} else {
							$tc_buyer_last_name_info_array = array();
						}
						do_action( 'tc_export_csv_after_buyer_last_name', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF BUYER NAME IS CHECKED
						if ( isset( $_POST[ 'col_buyer_name' ] ) ) {
							$buyer_full_name	 = isset( $order->details->tc_cart_info[ 'buyer_data' ][ 'first_name_post_meta' ] ) ? ($order->details->tc_cart_info[ 'buyer_data' ][ 'first_name_post_meta' ] . ' ' . $order->details->tc_cart_info[ 'buyer_data' ][ 'last_name_post_meta' ]) : '';
							$tc_buyer_info_array = array( __( 'Buyer Name', 'tc' ) => apply_filters( 'tc_ticket_checkin_buyer_full_name', $buyer_full_name, $order->details->ID ) );
						} else {
							$tc_buyer_info_array = array();
						}

						do_action( 'tc_export_csv_after_buyer_name', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF BUYER E-MAIL IS CHECKED
						if ( isset( $_POST[ 'col_buyer_email' ] ) ) {
							$buyer_email			 = isset( $order->details->tc_cart_info[ 'buyer_data' ][ 'email_post_meta' ] ) ? $order->details->tc_cart_info[ 'buyer_data' ][ 'email_post_meta' ] : '';
							$tc_buyer_email_array	 = array( __( 'Buyer E-Mail', 'tc' ) => apply_filters( 'tc_ticket_checkin_buyer_email', $buyer_email, $order->details->ID ) );
						} else {
							$tc_buyer_email_array = array();
						}

						do_action( 'tc_export_csv_after_email', isset( $_POST ) ? $_POST : ''  );

						//CHECK TO SEE IF ATTENDEE IS CHECKED-IN
						if ( isset( $_POST[ 'col_checked_in' ] ) ) {

							$checkins = get_post_meta( $instance->details->ID, 'tc_checkins', true );

							if ( count( $checkins ) > 0 && is_array( $checkins ) ) {
								foreach ( $checkins as $checkin ) {
									tc_format_date( $checkin[ 'date_checked' ] );
								}
							} else {
								$checked_in = __( 'No', 'tc' );
							}

							$tc_checked_in_array = array( __( 'Checked-in', 'tc' ) => $checked_in );
						} else {
							$tc_checked_in_array = array();
						}

						do_action( 'tc_export_csv_after_checked_in', isset( $_POST ) ? $_POST : ''  );

						//CHECK-INS LIST FOR AN ATTENDEE
						if ( isset( $_POST[ 'col_checkins' ] ) ) {

							$checkins		 = get_post_meta( $instance->details->ID, 'tc_checkins', true );
							$checkins_list	 = array();
							if ( count( $checkins ) > 0 && is_array( $checkins ) ) {
								foreach ( $checkins as $checkin ) {
									$checkins_list[] = tc_format_date( $checkin[ 'date_checked' ] );
								}
								$checkins = implode( "\r\n", $checkins_list );
							} else {
								$checkins = '';
							}

							$tc_checkins_array = array( __( 'Check-ins', 'tc' ) => $checkins );
						} else {
							$tc_checkins_array = array();
						}

						do_action( 'tc_export_csv_after_checkins', isset( $_POST ) ? $_POST : ''  );

						$tc_csv_array[]				 = apply_filters( 'tc_csv_array', array_merge( $tc_first_name_array, $tc_last_name_array, $tc_name_array, $tc_owner_email_array, $tc_payment_array, $tc_order_number_array, $tc_payment_gateway_array, $tc_order_status_array, $tc_order_total_array, $tc_discount_array, $tc_ticket_id_array, $tc_ticket_type_array, $tc_buyer_first_name_info_array, $tc_buyer_last_name_info_array, $tc_buyer_info_array, $tc_buyer_email_array, $tc_checked_in_array, $tc_checkins_array ), $order, $instance, $_POST );
						$_SESSION[ 'tc_csv_array' ]	 = $tc_csv_array;
					}
				}
			}

			$exported = ($page * $per_page);

			$response = array(
				'exported'	 => $this->set_max( ceil( $exported / ($query->found_posts / 100) ) ),
				'page'		 => $page + 1,
				'done'		 => false,
			);

			if ( $exported >= $query->found_posts ) {
				$response[ 'done' ] = true;
			}

			wp_send_json_success( $response );
			exit;
		}

		function tc_export() {
			if ( !session_id() ) {
				session_start();
			}
			$this->download_send_headers( $_GET[ 'document_title' ] . ".csv" );
			echo $this->array2csv( $_SESSION[ 'tc_csv_array' ] );
			exit;
		}

		function download_send_headers( $filename ) {
			// disable caching
			if ( !empty( $_GET[ 'document_title' ] ) ) {
				$now = gmdate( "D, d M Y H:i:s" );
				header( "Expires: Tue, 03 Jul 2001 06:00:00 GMT" );
				header( "Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate" );
				header( "Last-Modified: {$now} GMT" );

				// force download  
				header( "Content-Type: application/force-download" );
				header( "Content-Type: application/octet-stream" );
				header( "Content-Type: application/download" );

				// disposition / encoding on response body
				header( "Content-Disposition: attachment;filename={$filename}" );
				header( "Content-Transfer-Encoding: binary" );
				//unset( $_POST );
			}
		}

	}

}

$tc_export_csv_mix = new TC_Export_Csv_Mix();

//Addon updater 
if ( function_exists( 'tc_plugin_updater' ) ) {
	tc_plugin_updater( 'csv-export', __FILE__ );
}
?>