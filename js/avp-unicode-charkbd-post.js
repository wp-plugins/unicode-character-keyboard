jQuery(document).ready( function($) {

    $( '.avp-meta-box-control' ).click(function(){
        switch_tabs($(this));
    });
 
});
 
function switch_tabs(obj)
{
    //  Get the ID from the passed object and the 'rel' identifier for it
    var id = '#' + obj.attr( 'rel' );

    //  Show the associated contents panel with the ID that matches the object 'rel' identifier
    jQuery( id ).toggle( );
};
