<?php

class tc_ticket_barcode_element extends TC_Ticket_Template_Elements {

	var $element_name	 = 'tc_ticket_barcode_element';
	var $element_title	 = 'Barcode';
        var $font_awesome_icon = '<i class="fa fa-barcode"></i>';

	function on_creation() {
		$this->element_title = apply_filters( 'tc_ticket_barcode_element_title', __( 'Barcode', 'tc' ) );
	}

	function admin_content() {
		echo $this->get_1d_barcode_types();
		echo $this->get_1d_barcode_text_visibility();
		echo $this->get_1d_barcode_size();
		echo parent::get_font_sizes( 'Barcode Text Font Size (if visible)', 8 );
		echo parent::get_cell_alignment();
		echo parent::get_element_margins();
	}

	function get_1d_barcode_size() {
		?>
		<label><?php _e( 'Barcode Size', 'tc' ); ?>
			<input class="ticket_element_padding" type="text" name="<?php echo $this->element_name; ?>_1d_barcode_size_post_meta" value="<?php echo esc_attr( isset( $this->template_metas[ $this->element_name . '_1d_barcode_size' ] ) ? $this->template_metas[ $this->element_name . '_1d_barcode_size' ] : '50'  ); ?>" />
		</label>
		<?php
	}

	function get_1d_barcode_text_visibility() {
		$text_visibility = (isset( $this->template_metas[ $this->element_name . '_barcode_text_visibility' ] ) ? $this->template_metas[ $this->element_name . '_barcode_text_visibility' ] : 'visible');
		?>
		<label><?php _e( 'Barcode Text Visibility', 'tc' ); ?></label>
		<select name="<?php echo $this->element_name; ?>_barcode_text_visibility_post_meta">
			<option value="visible" <?php selected( $text_visibility, 'visible', true ); ?>><?php echo _e( 'Visible', 'tc' ); ?></option>
			<option value="invisible" <?php selected( $text_visibility, 'invisible', true ); ?>><?php echo _e( 'Invisible', 'tc' ); ?></option>
		</select>
		<?php
	}

	function get_1d_barcode_types() {
		?>
		<label><?php _e( 'Barcode Type', 'tc' ); ?></label>
		<?php $barcode_type = isset( $this->template_metas[ $this->element_name . '_barcode_type' ] ) ? $this->template_metas[ $this->element_name . '_barcode_type' ] : 'C39'; ?>
		<select name="<?php echo $this->element_name; ?>_barcode_type_post_meta">
			<option value="C39" <?php selected( $barcode_type, 'C39', true ); ?>><?php echo _e( 'C39', 'tc' ); ?></option>
			<option value="C39E" <?php selected( $barcode_type, 'C39E', true ); ?>><?php echo _e( 'C39E', 'tc' ); ?></option>
			<option value="C93" <?php selected( $barcode_type, 'C93', true ); ?>><?php echo _e( 'C93', 'tc' ); ?></option>
			<option value="C128" <?php selected( $barcode_type, 'C128', true ); ?>><?php echo _e( 'C128', 'tc' ); ?></option>
			<option value="C128A" <?php selected( $barcode_type, 'C128A', true ); ?>><?php echo _e( 'C128A', 'tc' ); ?></option>
			<option value="C128B" <?php selected( $barcode_type, 'C128B', true ); ?>><?php echo _e( 'C128B', 'tc' ); ?></option>
			<option value="EAN2" <?php selected( $barcode_type, 'EAN2', true ); ?>><?php echo _e( 'EAN2', 'tc' ); ?></option>
			<option value="EAN5" <?php selected( $barcode_type, 'EAN5', true ); ?>><?php echo _e( 'EAN5', 'tc' ); ?></option>
			<option value="EAN13" <?php selected( $barcode_type, 'EAN13', true ); ?>><?php echo _e( 'EAN13', 'tc' ); ?></option>
			<option value="UPCA" <?php selected( $barcode_type, 'UPCA', true ); ?>><?php echo _e( 'UPCA', 'tc' ); ?></option>
			<option value="UPCE" <?php selected( $barcode_type, 'UPCE', true ); ?>><?php echo _e( 'UPCE', 'tc' ); ?></option>
			<option value="MSI" <?php selected( $barcode_type, 'MSI', true ); ?>><?php echo _e( 'MSI', 'tc' ); ?></option>
			<option value="MSI+" <?php selected( $barcode_type, 'MSI+', true ); ?>><?php echo _e( 'MSI+', 'tc' ); ?></option>
<<<<<<< HEAD
=======
			<option value="PLANET" <?php selected( $barcode_type, 'PLANET', true ); ?>><?php echo _e( 'PLANET', 'tc' ); ?></option>
>>>>>>> 25d7a9b565fd496f5fce322537aaaf2f98e4b9bf
			<option value="RMS4CC" <?php selected( $barcode_type, 'RMS4CC', true ); ?>><?php echo _e( 'RMS4CC', 'tc' ); ?></option>
			<option value="IMB" <?php selected( $barcode_type, 'IMB', true ); ?>><?php echo _e( 'IMB', 'tc' ); ?></option>
			<?php do_action( 'tc_ticket_barcode_element_after_types_options', $barcode_type ); ?>
		</select>
		<span class="description"><?php _e( 'Following Barcode types are supported by the iOS check-in app: EAN-13, UPCA, C93, C128', 'tc' ); ?></span>
		<?php
	}

	function ticket_content( $ticket_instance_id = false ) {
		global $tc, $pdf;

		$cell_alignment = $this->template_metas[ $this->element_name . '_cell_alignment' ];

		if ( isset( $cell_alignment ) && $cell_alignment == 'right' ) {
			$cell_alignment = 'R';
		} elseif ( isset( $cell_alignment ) && $cell_alignment == 'left' ) {
			$cell_alignment = 'L';
		} elseif ( isset( $cell_alignment ) && $cell_alignment == 'center' ) {
			$cell_alignment = 'C';
		} else {
			$cell_alignment = 'C'; //default alignment
		}

		$text_visibility = (isset( $this->template_metas[ $this->element_name . '_barcode_text_visibility' ] ) ? $this->template_metas[ $this->element_name . '_barcode_text_visibility' ] : 'visible');

		if ( $text_visibility == 'visible' ) {
			$text_visibility = true;
		} else {
			$text_visibility = false;
		}

		if ( $ticket_instance_id ) {
			$ticket_instance = new TC_Ticket_Instance( $ticket_instance_id );
		}

		$barcode_params = $pdf->serializeTCPDFtagParameters(
		array(
			($ticket_instance) ? $ticket_instance->details->ticket_code : $tc->create_unique_id(), //code value
			(isset( $this->template_metas[ $this->element_name . '_barcode_type' ] ) ? $this->template_metas[ $this->element_name . '_barcode_type' ] : 'C128'), //type
			'', //x
			'', //y
			(isset( $this->template_metas[ $this->element_name . '_1d_barcode_size' ] ) ? $this->template_metas[ $this->element_name . '_1d_barcode_size' ] : 50), //w
			0, //h
			0.4, //xres
			array( //style
				'position'		 => apply_filters( 'tc_barcode_element_cell_alignment', $cell_alignment ),
				'border'		 => apply_filters( 'tc_show_barcode_border', true ),
				'padding'		 => apply_filters( 'tc_barcode_padding', 2 ),
				'fgcolor'		 => tc_hex2rgb( '#000000' ), //black (don't change it or won't be readable by the barcode reader)
				'bgcolor'		 => tc_hex2rgb( '#ffffff' ), //white (don't change it or won't be readable by the barcode reader)
				'text'			 => $text_visibility,
				'font'			 => apply_filters( 'tc_1d_barcode_font', 'helvetica' ),
				'fontsize'		 => isset( $this->template_metas[ $this->element_name . '_font_size' ] ) ? $this->template_metas[ $this->element_name . '_font_size' ] : 8,
				'cellfitalign'	 => true,
				'stretchtext'	 => 0 ),
			'N' ) );
		return '<tcpdf method="write1DBarcode" params="' . $barcode_params . '" />';
	}

}

tc_register_template_element( 'tc_ticket_barcode_element', __( 'Barcode', 'tc' ) );
