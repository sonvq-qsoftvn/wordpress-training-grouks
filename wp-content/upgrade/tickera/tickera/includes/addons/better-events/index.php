<?php
/*
  Plugin Name: Better Events
  Plugin URI: http://tickera.com/
  Description: Better events presentaton for Tickera
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0
  Copyright 2015 Tickera (http://tickera.com/)
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly


if ( !class_exists( 'TC_Better_Events' ) ) {

	class TC_Better_Events {

		var $version	 = '1.0';
		var $title	 = 'Better Events';
		var $name	 = 'better-events';

		function __construct() {

			global $post;

			if ( !isset( $post ) ) {
				$post = isset( $_GET[ 'post' ] ) ? $_GET[ 'post' ] : '';
			}

			$post_type = get_post_type( $post );

			if ( empty( $post_type ) ) {
				$post_type = isset( $_GET[ 'post_type' ] ) ? $_GET[ 'post_type' ] : '';
			}

			add_action( 'init', array( &$this, 'register_event_category' ), 1 );

			add_filter( 'tc_settings_general_sections', array( &$this, 'tc_settings_general_sections' ) );
			add_filter( 'tc_general_settings_page_fields', array( &$this, 'tc_general_settings_page_fields' ) );
			add_filter( 'tc_events_post_type_args', array( &$this, 'tc_events_post_type_args' ) );

			add_filter( 'manage_tc_events_posts_columns', array( &$this, 'manage_tc_events_columns' ) );
			add_action( 'manage_tc_events_posts_custom_column', array( &$this, 'manage_tc_events_posts_custom_column' ) );
			add_filter( "manage_edit-tc_events_sortable_columns", array( &$this, 'manage_edit_tc_events_sortable_columns' ) );

			add_action( 'post_submitbox_misc_actions', array( &$this, 'post_submitbox_misc_actions' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts_and_styles' ) );

			add_filter( 'tc_add_admin_menu_page', array( &$this, 'tc_add_admin_menu_page' ) );
			add_filter( 'first_tc_menu_handler', array( &$this, 'first_tc_menu_handler' ) );

			add_action( 'admin_menu', array( &$this, 'rename_events_menu_item' ) );

			add_action( 'add_meta_boxes', array( &$this, 'add_events_metaboxes' ) );
			add_action( 'save_post', array( $this, 'save_metabox_values' ) );
			add_action( 'delete_post', array( $this, 'delete_event_api_keys' ) );

			add_filter( 'the_content', array( $this, 'modify_the_content' ) );

			if ( $post_type == 'tc_events' ) {
				add_action( 'edit_form_after_editor', array( $this, 'edit_form_after_editor' ), 10, 1 );
				add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 10, 2 );
			}

			add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		}

		function post_updated_messages( $messages ) {

			$post				 = get_post();
			$post_type			 = get_post_type( $post );
			$post_type_object	 = get_post_type_object( $post_type );

			$event				 = new TC_Event( $post->ID );
			$event_ticket_types	 = $event->get_event_ticket_types();

			$no_ticket_types = count( $event_ticket_types ) == 0 ? true : false;

			$ticket_type_admin_url	 = apply_filters( 'tc_ticket_type_admin_url', admin_url( 'edit.php?post_type=tc_tickets' ) );
			$creation_messages[]	 = sprintf( __( 'Good work! Now go and create some <a href="%s">ticket types</a> for your event.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Great! Create some <a href="%s">ticket types</a> for this event.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Good start! Now go <a href="%s">here</a> and create ticket types for your event.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'You are almost there. Go <a href="%s">here</a> and create ticket types for your event.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Awesome! Go <a href="%s">here</a> and create ticket types for your event.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Lovely! You just need to <a href="%s">create ticket types</a> now.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Amazing! Now <a href="%s">create ticket types</a> for your event.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Cool! But one thing is missing. Go <a href="%s">here</a> and create ticket types for your event.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Saved. Now <a href="%s">create ticket types</a> for your event.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Done. Now <a href="%s">create ticket types</a> for your event.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Changes are saved. Consider adding <a href="%s">ticket types</a> for your event now.', 'tc' ), $ticket_type_admin_url );
			$creation_messages[]	 = sprintf( __( 'Good! It\'s time to add some <a href="%s">ticket types</a> for your next event.', 'tc' ), $ticket_type_admin_url );

			$creation_messages = apply_filters( 'tc_event_no_ticket_types_creation_messages', $creation_messages );

			$random_creation_message = $creation_messages[ rand( 0, count( $creation_messages ) - 1 ) ];

			$messages[ 'tc_events' ] = array(
				0	 => '', // Unused. Messages start at index 1.
				1	 => $no_ticket_types ? $random_creation_message : sprintf( __( 'Event post updated. <a href="%s">View post</a>', 'tc' ), esc_url( isset( $permalink ) ? $permalink : ''  ) ),
				2	 => __( 'Custom field updated.', 'tc' ),
				3	 => __( 'Custom field deleted.', 'tc' ),
				4	 => $no_ticket_types ? $random_creation_message : __( 'Event post updated.', 'tc' ),
				/* translators: %s: date and time of the revision */
				5	 => isset( $_GET[ 'revision' ] ) ? sprintf( __( 'Event post restored to revision from %s' ), wp_post_revision_title( (int) $_GET[ 'revision' ], false ) ) : false,
				6	 => $no_ticket_types ? $random_creation_message : __( 'Event post published.', 'tc' ),
				7	 => $no_ticket_types ? $random_creation_message : __( 'Event post saved.' ),
				8	 => $no_ticket_types ? $random_creation_message : sprintf( __( 'Event post submitted. <a target="_blank" href="%s">Preview post</a>' ), esc_url( add_query_arg( 'preview', 'true', isset( $permalink ) ? $permalink : ''  ) ) ),
				9	 => sprintf(
				__( 'Event post scheduled for: <strong>%1$s</strong>.' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
				),
				10	 => __( 'Event draft updated.' )
			);
			return $messages;
		}

		function edit_form_after_editor( $post ) {
			echo '<span class="description">' . __( 'You can add various shortcodes via shortcode builder located above the content editor. Make sure that you select "Show Tickets Automatically" option in the Publish box if you want to show tickets on the event\'s page automatically.', 'tc' ) . '</span>';
		}

		function enter_title_here( $enter_title_here, $post ) {
			if ( get_post_type( $post ) == 'tc_events' ) {
				$enter_title_here = __( 'Enter event title here', 'tc' );
			}
			return $enter_title_here;
		}

		/*
		 * Add Events Categories
		 */

		public function register_event_category() {

			$tc_general_settings = get_option( 'tc_general_setting', false );
			$event_slug			 = isset( $tc_general_settings[ 'tc_event_slug' ] ) && !empty( $tc_general_settings[ 'tc_event_slug' ] ) ? $tc_general_settings[ 'tc_event_slug' ] : 'tc-events';
			$event_category_slug = isset( $tc_general_settings[ 'tc_event_category_slug' ] ) && !empty( $tc_general_settings[ 'tc_event_category_slug' ] ) ? $tc_general_settings[ 'tc_event_category_slug' ] : 'tc-event-category';

			register_taxonomy( 'event_category', apply_filters( 'tc_events_category_availability', 'tc_events' ), apply_filters( 'tc_register_event_category', array(
				'hierarchical'		 => true,
				'labels'			 => array(
					'name'						 => _x( 'Event Categories', 'event_category', 'tc' ),
					'singular_name'				 => _x( 'Event Category', 'event_category', 'tc' ),
					'all_items'					 => __( 'All Event Categories', 'tc' ),
					'edit_item'					 => __( 'Edit Event Category', 'tc' ),
					'view_item'					 => __( 'View Event Category', 'tc' ),
					'update_item'				 => __( 'Update Event Category', 'tc' ),
					'add_new_item'				 => __( 'Add New Event Category', 'tc' ),
					'new_item_name'				 => __( 'New Event Category Name', 'tc' ),
					'parent_item'				 => __( 'Parent Event Category', 'tc' ),
					'parent_item_colon'			 => __( 'Parent Event Category:', 'tc' ),
					'search_items'				 => __( 'Search Event Categories', 'tc' ),
					'separate_items_with_commas' => __( 'Separate product categories with commas', 'tc' ),
					'add_or_remove_items'		 => __( 'Add or remove product categories', 'tc' ),
					'choose_from_most_used'		 => __( 'Choose from the most used product categories', 'tc' ),
					'not_found'					 => __( 'No product categories found', 'tc' ),
				),
				'capabilities'		 => array(
					'manage_categories'	 => 'manage_options',
					'edit_categories'	 => 'manage_options',
					'delete_categories'	 => 'manage_options',
					'assign_categories'	 => 'manage_options'
				),
				'show_ui'			 => true,
				'show_admin_column'	 => true,
				'rewrite'			 => array(
					'with_front' => false,
					'slug'		 => $event_category_slug,
				),
			) ) );
		}

		/*
		 * Mofify event post title
		 */

		function modify_the_content( $content ) {
			global $post, $post_type;
			if ( !is_admin() && $post_type == 'tc_events' ) {
				//Add date and location to the top of the content if needed
				$tc_general_settings = get_option( 'tc_general_setting', false );

				$tc_attach_event_date_to_title		 = isset( $tc_general_settings[ 'tc_attach_event_date_to_title' ] ) && !empty( $tc_general_settings[ 'tc_attach_event_date_to_title' ] ) ? $tc_general_settings[ 'tc_attach_event_date_to_title' ] : 'yes';
				$tc_attach_event_location_to_title	 = isset( $tc_general_settings[ 'tc_attach_event_location_to_title' ] ) && !empty( $tc_general_settings[ 'tc_attach_event_location_to_title' ] ) ? $tc_general_settings[ 'tc_attach_event_location_to_title' ] : 'yes';

				$new_content = '';

				if ( $tc_attach_event_date_to_title == 'yes' ) {
					$new_content .= '<span class="tc_event_date_title_front"><i class="fa fa-clock-o"></i>' . do_shortcode( '[tc_event_date]' ) . '</span>';
				}

				$event_location = do_shortcode( '[tc_event_location]' );

				if ( $tc_attach_event_location_to_title == 'yes' && !empty( $event_location ) ) {
					$new_content .= '<span class="tc_event_location_title_front"><i class="fa fa-map-marker"></i>' . '&nbsp;' . $event_location . '</span>';
				}

				$content = '<div class="tc_the_content_pre">' . $new_content . '</div>' . $content;

				//Add events shortcode to the end of the content if selected

				$show_tickets_automatically = get_post_meta( $post->ID, 'show_tickets_automatically', true );
				if ( !isset( $show_tickets_automatically ) ) {
					$show_tickets_automatically = false;
				}

				if ( $show_tickets_automatically ) {
					$content .= do_shortcode( apply_filters( 'tc_event_shortcode', '[tc_event]', $post->ID ) );
				}
			}
			return $content;
		}

		function delete_event_api_keys( $post_id ) {
			$api_key_post = array(
				'posts_per_page' => -1,
				'post_content'	 => '',
				'post_status'	 => 'publish',
				'post_type'		 => 'tc_api_keys',
				'meta_key'		 => 'event_name',
				'meta_value'	 => $post_id,
			);

			$posts = get_posts( $api_key_post );

			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID, true );
			}
		}

		/*
		 * Save event post meta values
		 */

		function save_metabox_values( $post_id ) {

			if ( get_post_type( $post_id ) == 'tc_events' ) {

				$metas								 = array();
				$metas[ 'event_presentation_page' ]	 = $post_id; //Event calendar support URL for better events interface

				if ( isset( $_POST[ 'show_tickets_automatically' ] ) ) {
					update_post_meta( $post_id, 'show_tickets_automatically', true );
				} else {
					update_post_meta( $post_id, 'show_tickets_automatically', false );
				}

				foreach ( $_POST as $field_name => $field_value ) {
					if ( preg_match( '/_post_meta/', $field_name ) ) {
						$metas[ sanitize_key( str_replace( '_post_meta', '', $field_name ) ) ] = sanitize_text_field( $field_value );
					}

					$metas = apply_filters( 'events_metas', $metas );

					if ( isset( $metas ) ) {
						foreach ( $metas as $key => $value ) {
							update_post_meta( $post_id, $key, $value );
						}
					}
				}

				//Create default API Key for this event if doesn't exists
				if ( apply_filters( 'tc_create_event_api_key_automatically', true ) == true ) {

					if ( !empty( $_POST[ 'post_title' ] ) ) {

						$wp_api_keys_search = new TC_API_Keys_Search( '', '', $post_id );

						if ( count( $wp_api_keys_search->get_results() ) == 0 ) {
							$api_key_post = array(
								'post_content'	 => '',
								'post_status'	 => 'publish',
								'post_title'	 => '',
								'post_type'		 => 'tc_api_keys',
							);

							$api_key_post	 = apply_filters( 'tc_event_api_key_post', $api_key_post );
							$api_key_post_id = wp_insert_post( $api_key_post );

							/* Add post metas for the API Key */
							$api_keys = new TC_API_Keys();

							if ( $api_key_post_id != 0 ) {
								update_post_meta( $api_key_post_id, 'event_name', (int) $post_id );
								update_post_meta( $api_key_post_id, 'api_key_name', sanitize_text_field( $_POST[ 'post_title' ] ) );
								update_post_meta( $api_key_post_id, 'api_key', $api_keys->get_rand_api_key() );
								update_post_meta( $api_key_post_id, 'api_username', '' );
							}
						}
					}
				}
			}
		}

		/*
		 * Rename "Events" to the plugin title ("Tickera" by default)
		 */

		function rename_events_menu_item() {
			global $menu, $tc;

			if ( $menu[ 100 ][ 2 ] = 'edit.php?post_type=tc_events' ) {
				$menu[ 100 ][ 0 ] = $tc->title;
			}
		}

		/*
		 * Disable Tickera legacy menu
		 */

		function tc_add_admin_menu_page() {
			return false;
		}

		/*
		 * Change menu item handler to regular post type's
		 */

		function first_tc_menu_handler( $handler ) {
			$handler = 'edit.php?post_type=tc_events';
			return $handler;
		}

		/*
		 * Enqueue scripts and styles
		 */

		function admin_enqueue_scripts_and_styles() {
			global $post, $post_type;
			if ( $post_type == 'tc_events' ) {
				wp_enqueue_style( 'tc-better-events', plugins_url( 'css/admin.css', __FILE__ ) );
			}
			//wp_enqueue_script( 'script-name', get_template_directory_uri() . '/js/example.js', array(), '1.0.0', true );
		}

		function tc_settings_general_sections( $sections ) {
			$sections[] = array(
				'name'			 => 'events_settings',
				'title'			 => __( 'Events Settings' ),
				'description'	 => '',
			);
			return $sections;
		}

		/*
		 * Adds additional field for Events slug under general settings > pages
		 */

		function tc_general_settings_page_fields( $pages_settings_default_fields ) {
			$pages_settings_default_fields[] = array(
				'field_name'	 => 'tc_event_slug',
				'field_title'	 => __( 'Event Slug', 'tc' ),
				'field_type'	 => 'texts',
				'default_value'	 => 'tc-events',
				'tooltip'		 => __( 'Defines value for the Events slug on the front-end. Please flush permalinks after changing this value.', 'tc' ),
				'section'		 => 'events_settings'
			);

			$pages_settings_default_fields[] = array(
				'field_name'	 => 'tc_event_category_slug',
				'field_title'	 => __( 'Event Category Slug', 'tc' ),
				'field_type'	 => 'texts',
				'default_value'	 => 'tc-event-category',
				'tooltip'		 => __( 'Defines value for the Events Category slug. Please flush permalinks after changing this value.', 'tc' ),
				'section'		 => 'events_settings'
			);

			$pages_settings_default_fields[] = array(
				'field_name'	 => 'tc_attach_event_date_to_title',
				'field_title'	 => __( 'Attach Event Date & Time to an event post title', 'tc' ),
				'field_type'	 => 'function',
				'function'		 => 'tc_yes_no',
				'default_value'	 => 'yes',
				'tooltip'		 => __( 'Automatically show event date & time under post title for event post type', 'tc' ),
				'section'		 => 'events_settings'
			);

			$pages_settings_default_fields[] = array(
				'field_name'	 => 'tc_attach_event_location_to_title',
				'field_title'	 => __( 'Attach Event Location to an event post title', 'tc' ),
				'field_type'	 => 'function',
				'function'		 => 'tc_yes_no',
				'default_value'	 => 'yes',
				'tooltip'		 => __( 'Automatically show event location under post title for event post type', 'tc' ),
				'section'		 => 'events_settings'
			);


			return $pages_settings_default_fields;
		}

		/*
		 * Change Events post type arguments
		 */

		function tc_events_post_type_args( $args ) {
			$tc_general_settings = get_option( 'tc_general_setting', false );

			$event_slug = isset( $tc_general_settings[ 'tc_event_slug' ] ) && !empty( $tc_general_settings[ 'tc_event_slug' ] ) ? $tc_general_settings[ 'tc_event_slug' ] : 'tc-events';

			$args[ 'menu_position' ] = 100;
			$args[ 'show_ui' ]		 = true;
			$args[ 'has_archive' ]	 = true;

			$args[ 'rewrite' ] = array(
				'slug'		 => $event_slug,
				'with_front' => false
			);

			$args[ 'supports' ] = array(
				'title',
				'editor',
				'thumbnail',
			);

			return $args;
		}

		/*
		 * Add table column titles
		 */

		function manage_tc_events_columns( $columns ) {
			$events_columns = TC_Events::get_event_fields();
			foreach ( $events_columns as $events_column ) {
				if ( isset( $events_column[ 'table_visibility' ] ) && $events_column[ 'table_visibility' ] == true && $events_column[ 'field_name' ] !== 'post_title' ) {
					$columns[ $events_column[ 'field_name' ] ] = $events_column[ 'field_title' ];
				}
			}
			unset( $columns[ 'date' ] );
			return $columns;
		}

		/*
		 * Add table column values
		 */

		function manage_tc_events_posts_custom_column( $name ) {
			global $post;
			$events_columns = TC_Events::get_event_fields();

			foreach ( $events_columns as $events_column ) {
				if ( isset( $events_column[ 'table_visibility' ] ) && $events_column[ 'table_visibility' ] == true && $events_column[ 'field_name' ] !== 'post_title' ) {
					if ( $events_column[ 'field_name' ] == $name ) {
						if ( isset( $events_column[ 'post_field_type' ] ) && $events_column[ 'post_field_type' ] == 'post_meta' ) {
							$value	 = get_post_meta( $post->ID, $events_column[ 'field_name' ], true );
							$value	 = !empty( $value ) ? $value : '-';
							echo $value;
						} else if ( $events_column[ 'field_name' ] == 'event_active' ) {
							$event_status	 = get_post_status( $post->ID );
							$on				 = $event_status == 'publish' ? 'tc-on' : '';
							echo '<div class="tc-control ' . $on . '" event_id="' . esc_attr( $post->ID ) . '"><div class="tc-toggle"></div></div>';
						} elseif ( $events_column[ 'field_name' ] == 'event_shortcode' ) {
							echo apply_filters( 'tc_event_shortcode_column', '[tc_event id="' . $post->ID . '"]', $post->ID );
						} else {
							//unknown column
						}
					}
				}
			}
		}

		function manage_edit_tc_events_sortable_columns( $columns ) {
			$custom = array(
				'event_location'		 => 'event_location',
				'event_date_time'		 => 'event_date_time',
				'event_end_date_time'	 => 'event_end_date_time',
			);
			return wp_parse_args( $custom, $columns );
		}

		/*
		 * Add control for setting an event as active or inactive
		 */

		function post_submitbox_misc_actions() {
			global $post, $post_type;

			$events_columns = TC_Events::get_event_fields();

			if ( $post_type == 'tc_events' ) {
				foreach ( $events_columns as $events_column ) {
					if ( isset( $events_column[ 'show_in_post_type' ] ) && $events_column[ 'show_in_post_type' ] == true && isset( $events_column[ 'post_type_position' ] ) && $events_column[ 'post_type_position' ] == 'publish_box' ) {
						?>
						<div class="misc-pub-section <?php echo esc_attr( $events_column[ 'field_name' ] ); ?>">
							<?php
							TC_Fields::render_post_type_field( 'TC_Event', $events_column, $post->ID, true );
							?>
						</div>
						<?php
					}
				}

				$event_status	 = get_post_status( $post->ID );
				$on				 = $event_status == 'publish' ? 'tc-on' : '';

				$show_tickets_automatically = get_post_meta( $post->ID, 'show_tickets_automatically', true );

				if ( !isset( $show_tickets_automatically ) ) {
					$show_tickets_automatically = false;
				}

				if ( current_user_can( apply_filters( 'tc_event_activation_capability', 'edit_others_events' ) ) || current_user_can( 'manage_options' ) ) {
					?>
					<div class="misc-pub-section misc-pub-visibility-activity" id="visibility">
						<span id="post-visibility-display"><?php echo '<div class="tc-control ' . $on . '" event_id="' . esc_attr( $post->ID ) . '"><div class="tc-toggle"></div></div>'; ?></span>
					</div>
				<?php } ?>

				<div class="misc-pub-section event_append_tickets" id="append_tickets">

					<span id="post_event_append_tickets"><input type="checkbox" id="show_tickets_automatically" name="show_tickets_automatically" value="1" <?php checked( $show_tickets_automatically, true, true ); ?> />
						<label for="show_tickets_automatically"><span></span><?php _e( 'Show Tickets Automatically', 'tc' ); ?></label></span>

				</div>
				<?php
			}
		}

		function non_visible_fields() {
			$fields = array(
				'event_shortcode',
				'event_date_time',
				'event_end_date_time',
				'post_title',
				'event_active',
				'event_presentation_page'
			);
			return $fields;
		}

		function add_events_metaboxes() {
			global $pagenow, $typenow, $post;

			if ( ('edit.php' == $pagenow) || ($post->post_type !== 'tc_events') ) {
				return;
			}

			$post_id = isset( $_GET[ 'post' ] ) ? (int) $_GET[ 'post' ] : 0;

			$events_columns = TC_Events::get_event_fields();

			foreach ( $events_columns as $events_column ) {
				if ( !in_array( $events_column[ 'field_name' ], $this->non_visible_fields() ) ) {
					eval( "function " . $events_column[ 'field_name' ] . "_metabox() {
						tc_render_metabox(" . $post_id . ", '" . $events_column[ 'field_name' ] . "');
						}" );
					add_meta_box( $events_column[ 'field_name' ] . '-tc-metabox-wrapper', $events_column[ 'field_title' ] . (isset( $events_column[ 'tooltip' ] ) ? tc_tooltip( $events_column[ 'tooltip' ], false ) : ''), $events_column[ 'field_name' ] . '_metabox', 'tc_events' ); //, isset( $events_column[ 'metabox_position' ] ) ? $events_column[ 'metabox_position' ] : 'core', isset( $events_column[ 'metabox_priority' ] ) ? $events_column[ 'metabox_priority' ] : 'low' 
				}
			}
		}

		/*
		 * Render fields by type (function, text, textarea, etc)
		 */

		public static function render_field( $field, $show_title = true ) {
			global $post;

			$event	 = new TC_Event( $post->ID );
			$post_id = $post->ID;

			if ( $show_title ) {
				?>
				<label><?php echo $field[ 'field_title' ]; ?>
					<?php
				}

				//Text
				if ( $field[ 'field_type' ] == 'text' ) {
					?>
					<input type="text" class="regular-<?php echo $field[ 'field_type' ]; ?>" value="<?php
					if ( isset( $event ) ) {
						if ( $field[ 'post_field_type' ] == 'post_meta' ) {
							echo esc_attr( isset( $event->details->{$field[ 'field_name' ]} ) ? $event->details->{$field[ 'field_name' ]} : ''  );
						} else {
							echo esc_attr( $event->details->{$field[ 'post_field_type' ]} );
						}
					}
					?>" id="<?php echo esc_attr( $field[ 'field_name' ] ); ?>" name="<?php echo esc_attr( $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ] ); ?>">
						   <?php
						   if ( isset( $field[ 'field_description' ] ) ) {
							   ?>
						<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
						<?php
					}
				}

				if ( $show_title ) {
					?>
				</label>
				<?php
			}
		}

	}

	global $better_events;
	$better_events = new TC_Better_Events();
}

function tc_render_metabox( $post_id, $field_name ) {
	$events_columns = TC_Events::get_event_fields();

	foreach ( $events_columns as $events_column ) {
		if ( $events_column[ 'field_name' ] == $field_name ) {
			TC_Fields::render_post_type_field( 'TC_Event', $events_column, $post_id, false );
		}
	}
}
?>
