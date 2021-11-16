<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>RUN Polito | Logo Contest</title>
<style type="text/css">
body {background: #e67e22;  font-family: Verdana,Arial,Courier New; font-size: 0.7em; }
th { text-align: right; padding: 0.8em; }
.container2 { /*text-align: center;*/ width: 70%; margin: 5% auto; }
@font-face {font-family: "Uniform"; 
            src: url('https://cdn.runpolito.it/fonts/uniform/uniform-webfont.woff')
                format('embedded-opentype'), url('https://cdn.runpolito.it/fonts/uniform/uniform-webfont.woff') 
                format("woff2"), url('https://cdn.runpolito.it/fonts/uniform/uniform-webfont.woff2') 
                format("woff"), url('https://cdn.runpolito.it/fonts/uniform/uniform-webfont.woff') 
                format("truetype"); 
}     

@font-face {font-family: "dos"; 
            src: url('https://cdn.runpolito.it/fonts/dos/dos437.ttf')
                format('embedded-opentype'), url('https://cdn.runpolito.it/fonts/dos/dos437.ttf')
                format("woff2"), url('https://cdn.runpolito.it/fonts/dos/dos437.ttf') 
                format("woff"), url('https://cdn.runpolito.it/fonts/dos/dos437.ttf') 
                format("truetype"); 
}  

@font-face {font-family: "Cabin"; 
            src: url('https://cdn.runpolito.it/fonts/cabin/cabin.woff')
                format('embedded-opentype'), url('https://cdn.runpolito.it/fonts/cabin/cabin.woff')
                format("woff2"), url('https://cdn.runpolito.it/fonts/cabin/cabin.woff') 
                format("woff"), url('https://cdn.runpolito.it/fonts/cabin/cabin.woff') 
                format("truetype"); 
} 

@font-face {font-family: "Bitter"; 
            src: url('https://cdn.runpolito.it/fonts/bitter/bitter.woff')
                format('embedded-opentype'), url('https://cdn.runpolito.it/fonts/bitter/bitter.woff')
                format("woff2"), url('https://cdn.runpolito.it/fonts/bitter/bitter.woff')
                format("woff"), url('https://cdn.runpolito.it/fonts/bitter/bitter.woff') 
                format("truetype"); 
} 

@font-face {font-family: "CalibriLight"; 
            src: url('https://cdn.runpolito.it/fonts/calibrilight/calibril.woff')
                format('embedded-opentype'), url('https://cdn.runpolito.it/fonts/calibrilight/calibril.woff') 
                format("woff2"), url('https://cdn.runpolito.it/fonts/calibrilight/calibril.woff') 
                format("woff"), url('https://cdn.runpolito.it/fonts/calibrilight/calibril.woff') 
                format("truetype");  
}  

</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">-->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<!--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<link href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" rel="stylesheet" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js" crossorigin="anonymous"></script> 
<link href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css" rel="stylesheet" crossorigin="anonymous">   
<script defer src="js/main.js"></script>    
</head>
<body>
<?php

            require '/vendor/autoload.php';
        require_once 'lib/_autoload.php';

        $auth = new \SimpleSAML\Auth\Simple('');
if(!SimpleSAML_Session::getSessionFromRequest()->cleanup())
{
    session_start();
}
$auth->requireAuth();

$user = $auth->getAttributes();
?>
    
    <div style="background-color: #e67e22;">
        
        <div class="d-flex flex-row justify-content-between">
            <div class="p-2">
                <img src="https://cdn.runpolito.it/img/5E47B162B3030.svg" width="60px"/>
            </div>
            
            <div class="p-2 align-self-center">
                <div class="btn-group dropstart">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <a class="dropdown-item" target="_blank" href="user.php">Login</a>
                        <a class="dropdown-item" target="_blank" href="settings.php">Settings</a>
                    </ul>
                </div>
            </div>
        </div>
    </div>
  
    <div class="container2">
        Utente: <?php echo $user['displayName'][0]." (".$user['uid'][0].")"; ?>
                    <div class="alert" id="alertmsg">
                <div id="msga"></div>
            </div>        
        <script type="text/javascript">
$(document).ready(function() {
    $('#myTable').DataTable( {
        "ajax": "ajax.php?action=fetchall",     
        "columns": [
            { "title" : "Codice", "data": "code" },
            { "title" : "Inviato", "data": "datetime" },
            { "title" : "Stato", "data": "textstatus" },
            { "title" : "UUID Form LimeSurvey", "data": "surveyid" },            
            { "title" : "Azioni", "data": "buttons" },             
        ]
    } );
} );
</script>
        
        <table id="myTable" class="display"></table>
    </div>
    
    <div class="footer fixed-bottom" style="text-align: center; padding: 5px; background-color: #d3d3d3; font-family: Uniform;">
        <p>
            &copy; 2021 RUN Polito APS - ETS
        </p>
        <p>
            Sede Legale e Domicilio Fiscale: Corso Duca degli Abruzzi 24, 10129 Torino (TO)<br/>
            C.F. 97852870019<br/>
            Registro Regionale piemontese delle APS n. 383/TO<br/>
            Registro delle Associazioni di Torino n. 4.228
        </p>
        
        <p>
            Powered and developed by <a href="https://areait.runpolito.it" target="_blank" style="text-decoration: none; color: #000;"><span style="font-family: dos;"><span style="color: #e67e22">area://</span>it</span></a>
        </p>
    </div>
</body>
</html>