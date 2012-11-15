jQuery(document).ready( function($) {

	jQuery( '.nav-tab' ).click(function(){
		switch_tabs( $(this) );
	});

});
 
function switch_tabs(obj)
{
    //  Test to see if the menu tab is already active
    //  Only process the click if the tab is inactive
    if ( ! obj.hasClass( 'nav-tab-active' ) )
    {
        //  Hide the active menu tab and all the contents panels
    	jQuery( '.nav-tab-active' ).removeClass( 'nav-tab-active' );
    	jQuery( '.nav-tab-contents' ).hide( );
    
        //  Get the value of the 'rel' attribute of the selected element object
        //  Translate the value into the id reference of the target panel
    	var id = '#' + obj.attr( 'rel' );
    
        //  Set the selected menu tab to active
        //  Show the associated contents panel where the ID matches the object 'rel' identifier
    	obj.addClass( 'nav-tab-active' );
    	jQuery( id ).show( );
    }
};
