jQuery(document).ready( function($) {

	jQuery( '.avp-message-box > a' ).click(function(){
		close_box( $(this) );
	});

});

function close_box( obj ) {

    jQuery( obj ).parent( ).remove( );

};
