<?php
if ( !function_exists( 'tc_yes_no_email' ) ) {

	function tc_yes_no_email( $field_name, $default_value = '' ) {
		$tc_email_settings = get_option( 'tc_email_setting', false );

		if ( isset( $tc_email_settings[ $field_name ] ) ) {
			$checked = $tc_email_settings[ $field_name ];
		} else {
			if ( $default_value !== '' ) {
				$checked = $default_value;
			} else {
				$checked = 'no';
			}
		}
		?>
		<label>
			<input type="radio" name="tc_email_setting[<?php echo esc_attr( $field_name ); ?>]" value="yes" <?php checked( $checked, 'yes', true ); ?>  /><?php _e( 'Yes', 'tc' ); ?>
		</label>
		<label>
			<input type="radio" name="tc_email_setting[<?php echo esc_attr( $field_name ); ?>]" value="no" <?php checked( $checked, 'no', true ); ?> /><?php _e( 'No', 'tc' ); ?>
		</label>
		<?php
	}

}

function show_tc_wb_event_attributes() {
	global $post;
	?>
	<table id="tc-wb-event-shortcode" class="shortcode-table" style="display:none">
		<?php
		if ( $post->post_type !== 'tc_events' ) {
			?>
			<tr>
				<th scope="row"><?php _e( 'Event', 'tc' ); ?></th>
				<td>
					<select name="id">
						<?php
						$wp_events_search = new TC_Events_Search( '', '', -1 );
						foreach ( $wp_events_search->get_results() as $event ) {
							$event = new TC_Event( $event->ID );
							?>
							<option value="<?php echo esc_attr( $event->details->ID ); ?>"><?php echo $event->details->post_title; ?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>
			<?php
		} else {
			?>
			<tr>
				<th scope="row"><?php _e( 'Event', 'tc' ); ?></th>
				<td><?php _e( 'Current Event', 'tc' ); ?></td>
			</tr>
			<?php
		}
		?>
		<tr>
			<th scope="row"><?php _e( 'Ticket Type Column Title', 'tc' ); ?></th>
			<td>
				<input type="text" name="ticket_type_title" value="" placeholder="<?php echo esc_attr( __( 'Ticket Type', 'tc' ) ); ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Price Column Title', 'tc' ); ?></th>
			<td>
				<input type="text" name="price_title" value="" placeholder="<?php echo esc_attr( __( 'Price', 'tc' ) ); ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Cart Column Title', 'tc' ); ?></th>
			<td>
				<input type="text" name="cart_title" value="" placeholder="<?php echo esc_attr( __( 'Cart', 'tc' ) ); ?>" />
			</td>
		</tr>

	</table>	
	<?php
}

function show_add_to_cart_attributes() {
	?>
	<table id="add-to-cart-shortcode" class="shortcode-table" style="display:none">
		<tr>
			<th scope="row"><?php _e( 'Ticket Type', 'tc' ); ?></th>
			<td>
				<select name="id">
					<?php
					$args = array(
						'post_type'		 => 'product',
						'post_status'	 => 'publish',
						'posts_per_page' => -1,
						'meta_query'	 => array(
							'relation' => 'OR',
							array(
								'key'		 => '_tc_is_ticket',
								'compare'	 => '=',
								'value'		 => 'yes'
							),
						)
					);

					$ticket_types = get_posts( $args );

					foreach ( $ticket_types as $ticket_type ) {
						?>
						<option value="<?php echo esc_attr( $ticket_type->ID ); ?>"><?php echo $ticket_type->post_title; ?></option>
						<?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e( 'Show Price', 'tc' ); ?></th>
			<td>
				<select name="show_price" data-default-value="false">
					<option value="false"><?php _e( 'No', 'tc' ); ?></option>
					<option value="true"><?php _e( 'Yes', 'tc' ); ?></option>
				</select>
			</td>
		</tr>
		<tr style="display: none;">
			<th scope="row"><?php _e( 'Style', 'tc' ); ?></th>
			<td>
				<input type="hidden" name="style" value="border:none;" placeholder="" />
			</td>
		</tr>
	</table>	
	<?php
}
?>