/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    $("abbr").fadeTo("slow", 0.6); // This sets the opacity of the thumbs to fade down to 60% when the page loads

    $("abbr")
    .hover(function(){
        $(this).fadeTo("slow", 1.0); // This should set the opacity to 100% on hover
    })
    .blur(function() {
        $(this).fadeTo("slow", 0.6); // This should set the opacity back to 60% on mouseout
    });
});

//  Enable submit button when input field non-blank
$('#username').keyup(function() {
    $('#submit').attr('disabled', !$('#username').val()); 
});