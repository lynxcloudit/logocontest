<?php
require "../lib/logocon.php";


            require '/vendor/autoload.php';
        require_once 'lib/_autoload.php';
session_start();
        $auth = new \SimpleSAML\Auth\Simple('');


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$session = SimpleSAML_Session::getSessionFromRequest();
$session->cleanup();

$_SESSION['var'];
switch ($_GET['action']) {
    case 'upload':
    
        if(!isset($_FILES['file']['error']) || is_array($_FILES['file']['error'])) 
        {
            http_response_code(400);
            die("No file uploaded.");
        }
        $fileError = $_FILES['file']['error']; 
        switch($fileError) {
            case UPLOAD_ERR_INI_SIZE:
                break;
            case UPLOAD_ERR_PARTIAL:
                break;
            case UPLOAD_ERR_NO_FILE:
                http_response_code(400);
                die("No file uploaded.");                
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                break;
            case UPLOAD_ERR_CANT_WRITE:
                break;
            default:
                break;
        }        
    if ( 0 < $_FILES['file']['error'] ) {
        http_response_code(400);
        die('Error: ' . $_FILES['file']['error']);
    }
    else 
    {
        $guid = logocon::guidv4();
        $filename = $_FILES['file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);       
        $target_file = '../uploads/'.$guid.".".$ext;
            
        if(!file_exists($target_file)) 
        {
            $file = $_FILES['file'];            
            $allowed = array('pdf');

            if (!in_array($ext, $allowed)) {
                http_response_code(415);
                die("Unrecognised file format.");
            }
            if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file))
            {
                $result = logocon::apply($guid, $target_file);
                http_response_code(200); 
                
                $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
                $cryptuuid = sodium_crypto_secretbox($guid.":".$result['passwd'], $nonce, sodium_hex2bin(NACL));
                                
                $response = array("box" => "File successfully uploaded.", "user" => $result['user'], "code" => $result['code'], "passwd" => $result['passwd'], "nonce" => sodium_bin2hex($nonce), "uuid" => sodium_bin2hex($cryptuuid)/*, "cl" => $guid*/);
                die(json_encode($response));
            }
            else
            {
                http_response_code(500);
                die("Unknown error, contact IT support at sd.areait@runpolito.it.");                 
            }
        } 
        else 
        {
            http_response_code(400);
            die("File exists.");
        }       
    }

        break;
        
    case "receipt":

$receipt = logocon::getReceipt($_GET['uuid'],$_GET['nonce']);
header("Content-type: application/octet-stream");
header("Content-disposition: attachment;filename=LogoContest_".$receipt['code']."_receipt.pdf");

echo base64_decode($receipt['file']);                
        break;
    case "getelab":
$elab = logocon::getElab($_GET['uuid'],$_GET['nonce']);
header("Content-type: application/octet-stream");
header("Content-disposition: attachment;filename=LogoContest_".$elab['code']."_proposal.pdf");

echo base64_decode($elab['file']);                
        break;    
    case "fetchall":
        $auth->requireAuth();
        echo logocon::getDataTable();
        break;
    case "updstatus":
        $auth->requireAuth();
        $response = logocon::updatestatus($_GET['id'], $_GET['status']);
        if($response)
        {
            echo '<script> alert("Azione eseguita."); </script>';
            echo '<script> window.setTimeout("window.close()", 10); </script>';
            die();            
        }
            echo '<script> alert("Errore."); </script>';
            echo '<script> window.setTimeout("window.close()", 10); </script>';
            die();
        break;
        
    case "download":
        $auth->requireAuth();
        $elab = logocon::dlElab($_GET['id']);
        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment;filename=LogoContest_".$elab['code']."_proposal.pdf");

        echo base64_decode($elab['file']);  
        
    case "downloaduser":
        $elab = logocon::userdlElab($_SESSION);
        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment;filename=LogoContest_".$elab['code']."_proposal.pdf");

        echo base64_decode($elab['file']);          
    case "login":
        $response = logocon::login($_POST['username'],$_POST['password']);
        http_response_code($response['status']);
        if($response['status'] !== 200)
        {
            die();
        }
        else 
        {
            $_SESSION["loggedin"] = true;
            $_SESSION["userid"] = $response['userid'];
            $_SESSION["tokencr"] = sodium_bin2hex($response['crtoken']);              
            
            header("Location: user.php");
        }
        die(json_encode($response));
        break;
    default:
        http_response_code(400);
        die();
        break;
}
die();