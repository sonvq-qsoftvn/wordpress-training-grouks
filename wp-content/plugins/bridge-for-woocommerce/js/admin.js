jQuery( function ( $ ) {

    /**
     * is ticket control
     */
    $( 'input#_tc_is_ticket' ).change( function () {
        tc_show_and_hide_panels();
    } );

    function tc_show_and_hide_panels() {

        var is_ticket = $( 'input#_tc_is_ticket:checked' ).size();

        if ( is_ticket ) {
            $( '.show_if_tc_ticket' ).show();
        } else {
            $( '.show_if_tc_ticket' ).hide();
        }

    }

    /**
     * _ticket_checkin_availability check
     */

    $( 'input[name="_ticket_checkin_availability"]' ).change( function () {
        tc_show_and_hide_ticket_checkin_availability_dates();
    } );

    function tc_show_and_hide_ticket_checkin_availability_dates() {
        var _ticket_checkin_availability_element_exist = $( 'input[name="_ticket_checkin_availability"]' ).size();
        if ( _ticket_checkin_availability_element_exist ) {
            var _ticket_availability = $( 'input[name="_ticket_checkin_availability"]:checked' ).val();

            if ( _ticket_availability == 'open_ended' ) {
                $( '#_ticket_checkin_availability_dates' ).hide();
            } else {
                $( '#_ticket_checkin_availability_dates' ).show();
            }
        }
    }

    /**
     * General
     */
    $( window ).load( function () {
        var is_ticket = $( 'input#_tc_is_ticket:checked' ).size();

        if ( is_ticket ) {
            $( 'input#_tc_is_ticket' ).prop( 'checked', true );
            tc_show_and_hide_panels();
        }
        tc_show_and_hide_ticket_checkin_availability_dates();
    } );

} );