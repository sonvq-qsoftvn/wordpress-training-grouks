<?php
/*
  Plugin Name: Tickera - Custom Ticket Template Fonts
  Plugin URI: http://tickera.com/
  Description: Add custom ticket template fonts
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0.1
  TextDomain: tc
  Domain Path: /languages/

  Copyright 2015 Tickera (http://tickera.com/)
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'TC_Custom_Ticket_Template_Fonts' ) ) {

	class TC_Custom_Ticket_Template_Fonts {

		var $version		 = '1.0.1';
		var $title		 = 'Custom Ticket Template Fonts';
		var $name		 = 'tc_custom_fields';
		var $dir_name	 = 'custom-ticket-template-fonts';
		var $location	 = 'plugins';
		var $plugin_dir	 = '';
		var $plugin_url	 = '';

		function __construct() {
			$this->init_vars();
			$this->init();

			require_once( $this->plugin_dir . 'includes/classes/class.custom_font.php' );
			require_once( $this->plugin_dir . 'includes/classes/class.custom_fonts.php' );
			require_once( $this->plugin_dir . 'includes/classes/class.custom_fonts_search.php' );

			add_filter( 'upload_mimes', array( &$this, 'add_custom_upload_mimes' ) );
			add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
			add_action( 'tc_settings_menu_tickera_custom_fonts', array( &$this, 'tc_settings_menu_tickera_custom_fonts_show_page' ) );
			add_action( 'tc_ticket_font', array( &$this, 'tc_load_custom_fonts' ), 10, 2 );
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

		function init() {
			add_action( 'init', array( &$this, 'localization' ), 10 );
			add_action( 'init', array( &$this, 'register_custom_posts' ), 0 );
		}

		//Plugin localization function
		function localization() {

// Load up the localization file if we're using WordPress in a different language
// Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
			if ( $this->location == 'mu-plugins' ) {
				load_muplugin_textdomain( 'tc', 'languages/' );
			} else if ( $this->location == 'subfolder-plugins' ) {
				//load_plugin_textdomain( 'tc-mijireh', false, $this->plugin_dir . '/languages/' );
				load_plugin_textdomain( 'tc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			} else if ( $this->location == 'plugins' ) {
				load_plugin_textdomain( 'tc', false, 'languages/' );
			} else {
				
			}

			$temp_locales	 = explode( '_', get_locale() );
			$this->language	 = ($temp_locales[ 0 ]) ? $temp_locales[ 0 ] : 'en';
		}

		function tc_load_custom_fonts( $selected_font, $default_font ) {
			global $pdf, $tc;

			require_once($tc->plugin_dir . 'includes/tcpdf/examples/tcpdf_include.php');

			$custom_fonts_search = new TC_Custom_Fonts_Search();
			foreach ( $custom_fonts_search->get_results() as $custom_font ) {
				$custom_font_obj = new TC_Custom_Font( $custom_font->ID );

				$current_font_title	 = $custom_font_obj->details->custom_font_name;
				$attachment_id		 = $this->get_attachment_id( $custom_font_obj->details->custom_font_file_url );
				$font				 = TCPDF_FONTS::addTTFfont( get_attached_file( $attachment_id ), 'TrueType', 'ansi', 32 );
				$current_font_name	 = $font; //$font;
				?>
				<option value='<?php echo esc_attr( $current_font_name ); ?>' <?php selected( !empty( $selected_font ) ? $selected_font : $default_font, $current_font_name, true ); ?>><?php echo $current_font_title; ?></option>
				<?php
			}
		}

		/*
		 * Get attachment ID by its URL
		 */

		function get_attachment_id( $url ) {
			global $wpdb;
			$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url ) );
			return $attachment[ 0 ];
		}

		function add_custom_upload_mimes( $mimes ) {
			$mimes = array_merge( $mimes, array(
				'ttf|woff' => 'application/x-font' ) );
			return $mimes;
		}

		function register_custom_posts() {
			$args = array(
				'labels'			 => array( 'name'				 => __( 'Custom Fonts', 'tc' ),
					'singular_name'		 => __( 'Custom Fonts', 'tc' ),
					'add_new'			 => __( 'Create New', 'tc' ),
					'add_new_item'		 => __( 'Create New Font', 'tc' ),
					'edit_item'			 => __( 'Edit Font', 'tc' ),
					'edit'				 => __( 'Edit', 'tc' ),
					'new_item'			 => __( 'New Font', 'tc' ),
					'view_item'			 => __( 'View Font', 'tc' ),
					'search_items'		 => __( 'Search Fonts', 'tc' ),
					'not_found'			 => __( 'No Fonts Found', 'tc' ),
					'not_found_in_trash' => __( 'No Fonts found in Trash', 'tc' ),
					'view'				 => __( 'View Font', 'tc' )
				),
				'public'			 => false,
				'show_ui'			 => false,
				'publicly_queryable' => false,
				'capability_type'	 => 'post',
				'hierarchical'		 => false,
				'query_var'			 => true,
			);

			register_post_type( 'tc_custom_fonts', $args );
		}

		function tc_settings_new_menus_additional( $settings_tabs ) {
			$settings_tabs[ 'tickera_custom_fonts' ] = __( 'Custom Ticket Fonts', 'tc' );
			return $settings_tabs;
		}

		function tc_settings_menu_tickera_custom_fonts_show_page() {
			require_once( $this->plugin_dir . 'includes/admin-pages/settings-tickera_custom_fonts.php' );
		}

	}

	$tc_custom_fonts = new TC_Custom_Ticket_Template_Fonts();
}

//Addon updater 
if ( function_exists( 'tc_plugin_updater' ) ) {
	tc_plugin_updater( 'custom-ticket-template-fonts', __FILE__ );
}