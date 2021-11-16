/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( "#upfile" ).on( "submit", function( e ) {
    e.preventDefault();
    var file = $( "#file" )[0].files[0];
    var formData = new FormData();
    formData.append( "file", file );

    $.ajax({
        url: "ajax.php?action=upload",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: showResponse,
        error: showError,
    });
});

var info = $('#alertmsg');
var info2 = $('#alertmsg2');
$.fn.scrollTo = function (speed) {
    if (typeof(speed) === 'undefined')
        speed = 2000;

    $('html, body').animate({
        scrollTop: parseInt($(this).offset().top)
    }, speed);
};
    function showResponse(responseText, statusText, xhr, $form)
    {

    console.log(responseText);
    var jsr = responseText;
    var json = jQuery.parseJSON(responseText);
         htmlData = '<div class="alert alert-success fade show" role="alert">'+json.box+'</div>';
            info.find('#msga').html(htmlData);
         htmlData2 = '<p>Codice proposta/Proposal No.: '+json.code+'<br/><br/>Username: '+json.user+'<br/>Password: '+json.passwd+'</p>';
            info2.find('#details').html(htmlData2);  
            $("#propdata").show();
$('#propdata').scrollTo();           

var filePath = 'ajax.php?action=receipt&uuid='+json.uuid+'&nonce='+json.nonce;        
document.getElementById('dl').src = filePath;
    
    var filePath = 'ajax.php?action=getelab&uuid='+json.uuid+'&nonce='+json.nonce;        
document.getElementById('dl2').src = filePath;
    }
    
    function showError(responseText, statusText, xhr, $form)
    {
       
      var jsr = responseText;
      console.log(responseText);
         htmlData = '<div class="alert alert-danger" role="alert">'+jsr['responseText']+'</div>';
            info.find('#msga').html(htmlData);
    }    
