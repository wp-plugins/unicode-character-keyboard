jQuery(document).ready(function($) {

    jQuery( '#findCharacter' ).validate( {
        rules: {
            character: "required"
        },
        messages: {
            character: "Please enter a valid Unicode character or equivalent."
        }
    });

});
