jQuery(document).ready(function($) {

    jQuery( '#uploadFile' ).validate( {
        rules: {
            uploadFile: { accept: 'xml', required: true }
        },
        messages: {
            uploadFile: {
                accept: "Only XMl files cam be uploaded.",
                required: "Please select a valid XML file from the file browser."
            }
        }               
    });

    //  User must click the submit button, pressing Enter is ignored
    jQuery( '#uploadFile' ).keypress(function(e) {
        if (e.which == 13) {
            return false;
        }
    });

});
