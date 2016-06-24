<div class="wrap tc_wrap tc_csv_export">
    <div id="poststuff" class="metabox-holder tc-settings">
        <form id="tc_form_attendees_csv_export" method="post">
			<input type="hidden" name="action" value="tc_export_attendee_list" />
			<input type="hidden" name="page_num" id="page_num" value="1" />
            <div id="store_settings" class="postbox">
                <h3 class="hndle"><span><?php _e( 'Attendee List (CSV Export)', 'tc' ); ?></span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody> 
                            <tr valign="top">
                                <th scope="row"><label for="tc_export_csv_event_data"><?php _e( 'Event', 'tc' ); ?></label></th>
                                <td>
									<?php
									$wp_events_search = new TC_Events_Search( '', '', -1 );
									?>
                                    <select name="tc_export_csv_event_data" id="tc_export_csv_event_data">
										<?php
										foreach ( $wp_events_search->get_results() as $event ) {

											$event_obj		 = new TC_Event( $event->ID );
											$event_object	 = $event_obj->details;
											?>
											<option value="<?php echo $event_object->ID; ?>"><?php echo $event_object->post_title; ?></option>
											<?php
										}
										?>
                                    </select>
									<?php ?>
                                </td>
                            </tr>


                            <tr valign="top">
                                <th scope="row"><label for="attendee_export_field"><?php _e( 'Show Columns', 'tc' ); ?></label></th>
                                <td><fieldset>
									<?php
									$csv_fields = apply_filters( 'tc_csv_admin_fields', array(
										'col_owner_first_name'	 => __( 'Attendee First Name', 'tc' ),
										'col_owner_last_name'	 => __( 'Attendee Last Name', 'tc' ),
										'col_owner_name'		 => __( 'Attendee Full Name', 'tc' ),
										'col_owner_email'		 => __( 'Attendee E-mail', 'tc' ),
										'col_payment_date'		 => __( 'Payment Date', 'tc' ),
										'col_order_number'		 => __( 'Order Number', 'tc' ),
										'col_order_total'		 => __( 'Order Total', 'tc' ),
										'col_payment_gateway'	 => __( 'Payment Gateway', 'tc' ),
										'col_order_status'		 => __( 'Order Status', 'tc' ),
										'col_discount_code'		 => __( 'Discount Code', 'tc' ),
										'col_ticket_id'			 => __( 'Ticket Code', 'tc' ),
										'col_ticket_type'		 => __( 'Ticket Type', 'tc' ),
										'col_buyer_first_name'	 => __( 'Buyer First Name', 'tc' ),
										'col_buyer_last_name'	 => __( 'Buyer Last Name', 'tc' ),
										'col_buyer_name'		 => __( 'Buyer Full Name', 'tc' ),
										'col_buyer_email'		 => __( 'Buyer Email', 'tc' ),
										'col_checked_in'		 => __( 'Checked-in', 'tc' ),
										'col_checkins'			 => __( 'Check-ins', 'tc' ),
									) );

									$csv_unchecked_by_default = array(
										'col_owner_first_name',
										'col_owner_last_name',
										'col_buyer_first_name',
										'col_buyer_last_name',
										'col_payment_gateway',
										'col_order_total',
										'col_payment_date',
										'col_order_status',
										'col_discount_code',
										'col_checked_in',
										'col_checkins'
									);

									foreach ( $csv_fields as $key => $val ) {
										if ( !in_array( $key, $csv_unchecked_by_default ) ) {
											$checked = 'checked="checked"';
										} else {
											$checked = '';
										}
										?>
                                                                                        <label for="<?php echo esc_attr( $key ); ?>" class="tc_checkboxes_label">
                                                                                            <input type="checkbox" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $checked ); ?>>
                                                                                            <?php echo esc_attr( $val ); ?>
                                                                                        </label>
										<?php
									}
									?>

									<?php do_action( 'tc_csv_admin_columns' ); ?>
                                    </fieldset>
                                </td>
                            </tr>

							<tr valign="top">
                                <th scope="row"><label for="tc_limit_order_type"><?php _e( 'Order Status', 'tc' ); ?></label></th>
                                <td>
                                    <select name="tc_limit_order_type" id="tc_limit_order_type">
										<?php
										$payment_statuses = apply_filters( 'tc_csv_payment_statuses', array(
											'any'			 => __( 'Any', 'tc' ),
											'order_paid'	 => __( 'Paid', 'tc' ),
											'order_received' => __( 'Pending / Received', 'tc' ),
										) );
										foreach ( $payment_statuses as $payment_status_key => $payment_status_value ) {
											?>
											<option value="<?php echo esc_attr( $payment_status_key ); ?>"><?php echo esc_attr( $payment_status_value ); ?></option>
											<?php
										}
										?>
                                    </select>
                                </td>
                            </tr>


                            <tr valign="top">
                                <th scope="row"><label for="document_title"><?php _e( 'Document Title', 'tc' ); ?></label></th>
                                <td>
                                    <input type="text" name='document_title' id="document_title" value='<?php _e( 'Attendee List', 'tc' ); ?>' />
                                </td>
                            </tr>

                        </tbody>
                    </table>

					<div id="csv_export_progressbar"><div class="progress-label"></div></div>

                    <p class="submit">
                        <input type="submit" name="export_csv_event_data" id="export_csv_event_data" class="button button-primary" value="Export Data">
                    </p>

                </div><!-- .inside -->

            </div><!-- .postbox -->

        </form>
    </div><!-- #poststuff -->
</div><!-- .wrap -->