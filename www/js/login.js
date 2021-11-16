/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( "#login" ).on( "submit", function( e ) {
    e.preventDefault();

    $.ajax({
        url: "ajax.php?action=login",
        type: "POST",
        dataType:  'text', 
        processData: false,
        contentType: false,
        success: showResponse,
        error: showError,
    });
});

var info = $('#alertmsg');
    function showResponse(responseText, statusText, xhr, $form)
    {

    console.log(responseText);
    var jsr = responseText;
    var json = jQuery.parseJSON(responseText);
         htmlData = '<div class="alert alert-success fade show" role="alert">'+json.box+'</div>';
            info.find('#msga').html(htmlData); 
    }
    
    function showError(responseText, statusText, xhr, $form)
    {
       
      var jsr = responseText;
      console.log(responseText);
         htmlData = '<div class="alert alert-danger" role="alert">'+jsr['responseText']+'</div>';
            info.find('#msga').html(htmlData);
    }    
