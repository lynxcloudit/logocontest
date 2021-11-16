<?php
require_once "conf.php";
require_once ('tcpdf/tcpdf_barcodes_1d.php');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of logocon
 *
 * @author Corrado Mulas - RUN Polito | Area IT <areait@runpolito.it>
 */

/*stati
 * 0 default 
 * 1 selezionato come vincitore
 * 2 eliminato
 */


class logocon {
    public function updatestatus($id, $status)
    {
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `users` WHERE code = ?");
        $query->bind_param('s',$id);
        $query->execute();    
        $result = mysqli_stmt_get_result($query);
        $rowuser = $result->fetch_array(MYSQLI_ASSOC);
        $query->close();
        
        $mysqli = new mysqli(DBH, DBN, DBP, DBU);
        $query = $mysqli->prepare("UPDATE `elaborati` SET `status`= ? WHERE uuid = ?");
        $query->bind_param('is', $status, $rowuser['elabuuid']);
        $resp = false;
        if($query->execute())
        {
            $resp = true;
        }
        $query->close();
        return $resp;
    }
    public function login($user, $password)
    {
        //verifica user/password, genera token + nonce e salvali in sessione (HMAC) per verificare auth su ajax.php ed evitare minchiate tipo manipolazioni sulle sessioni
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `users` WHERE userid = ?");
        $query->bind_param('s',trim($user));
        $query->execute();    
        $result = mysqli_stmt_get_result($query);
        $rows = $result->num_rows;
        $rowuser = $result->fetch_array(MYSQLI_ASSOC);
        $query->close();
        
        if($rows == 0)
        {
            return array("status" => 401);
        }
            //verifica password
        if(!password_verify(trim($password), $rowuser['password']))
        {
            return array("status" => 401);
        }        
        $query->close();           
        
        $token = logocon::random_str(64);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $hmac = hash_hmac('sha512', $rowuser['elabuuid'].$token, sodium_hex2bin(HMAC));
        
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `users` WHERE userid = ?");
        $query->bind_param('s',trim($user));
        $query->execute(); 
        $cryptoken = sodium_crypto_secretbox($rowuser['elabuuid'].$token, $nonce, sodium_hex2bin(NACL));

        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("UPDATE `users` SET `session` = ?, `nonce` = ? WHERE userid = ?");
        $query->bind_param('sss', $hmac, sodium_bin2hex($nonce), trim($user));
        
        if(!$query->execute())
        {
            return array("status" => 500); 
        }
        
        return array("status" => 200, "userid" => $user, "token" => $rowuser['elabuuid'].$token, "crtoken" => $cryptoken);
    }
    
    public function isloggedin($session)
    {
        if(!isset($session['loggedin']) || !$session['loggedin'] || !logocon::userexists($session['userid']) || !logocon::verifytoken($session['tokencr'], $session['userid']))
        {
            return false;
        }
        return true;
    }
    public function getuuid($tokencr, $userid)
    {
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `users` WHERE userid = ?");
        $query->bind_param('s',trim($userid));
        $query->execute();    
        $result = mysqli_stmt_get_result($query);
        $rowuser = $result->fetch_array(MYSQLI_ASSOC);
        $query->close();

        $nonce = $rowuser['nonce'];
        $tokendb = $rowuser['session'];
        $uuid = $rowuser['elabuuid'];
        
        $tokenunenc = sodium_crypto_secretbox_open(sodium_hex2bin($tokencr), sodium_hex2bin($nonce), sodium_hex2bin(NACL));
        $hmac = hash_hmac('sha512', $tokenunenc, sodium_hex2bin(HMAC));
        if(!hash_equals($tokendb, $hmac))
        {
            return false;
        }
        return $uuid;        
    }
    private function verifytoken($tokencr, $userid)
    {
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `users` WHERE userid = ?");
        $query->bind_param('s',trim($userid));
        $query->execute();    
        $result = mysqli_stmt_get_result($query);
        $rowuser = $result->fetch_array(MYSQLI_ASSOC);
        $query->close();

        $nonce = $rowuser['nonce'];
        $tokendb = $rowuser['session'];
        $uuid = $rowuser['elabuuid'];
        
        $tokenunenc = sodium_crypto_secretbox_open(sodium_hex2bin($tokencr), sodium_hex2bin($nonce), sodium_hex2bin(NACL));
        $hmac = hash_hmac('sha512', $tokenunenc, sodium_hex2bin(HMAC));
        if(!hash_equals($tokendb, $hmac))
        {
            return false;
        }
        return true;
    }
    
    private function userexists($user)
    {
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `users` WHERE userid = ?");
        $query->bind_param('s',trim($user));
        $query->execute();    
        $result = mysqli_stmt_get_result($query);
        $rows = $result->num_rows;
        
        if($rows == 0)
        {
            return false;
        }
        return true;
    }
            
    public function guidv4()
    {
        //Genera UUID v4
        $data = random_bytes(16);
        assert(strlen($data) == 16);
    
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); 
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        return strtoupper(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
    }   
    public function regfile($uuid, $filepath)
    {
        $time = time();
        $hash = hash_file('sha256', $filepath);
        
        $mysqli = new mysqli(DBH, DBN, DBP, DBU);
        $query = $mysqli->prepare("INSERT INTO `elaborati`(`uuid`, `timestamp`, `hash`, `endhash`, `filepath`) VALUES (?,?,?,?,?)");
        $query->bind_param('sisss',$uuid, $time, $hash, $hash, $filepath);
        $result = $query->execute();
        $query->close();        
        
        //Inserisci stamping

        if($result)
        {
            $details = logocon::getData($uuid);
            logocon::stamp($filepath, $details);            
            
            $hash = hash_file('sha256', $filepath);
            $mysqli = new mysqli(DBH, DBN, DBP, DBU);
            $query = $mysqli->prepare("UPDATE `elaborati` SET `endhash`= ? WHERE uuid = ?");
            $query->bind_param('ss', $hash, $uuid);
            $result = $query->execute();
            $query->close();              
            
            return $uuid;
        }
        return false;
    }
    
    public function reguser($elabuuid)
    {
        $pw = logocon::pwd_gen();
        $code = logocon::code_gen();
        $user = logocon::userid_gen();
        $passwd = password_hash($pw, PASSWORD_BCRYPT, ["cost" => 8]);
        
        $mysqli = new mysqli(DBH, DBN, DBP, DBU);
        $query = $mysqli->prepare("INSERT INTO `users`(`code`, `userid`, `password`, `surveyid`, `elabuuid`) VALUES (?,?,?,?,?)");
        $query->bind_param('issss',$code, $user, $passwd, $elabuuid, $elabuuid);
        $result = $query->execute();
        $query->close();        
        
        if($result)
        {
            return array("user" => $user, "code" => $code, "passwd" => $pw, "uuid" => $elabuuid);
        }
        return false;        
    }    
    public function apply($uuid, $filepath)
    {
        $guid = logocon::reguser($uuid);
        logocon::regfile($guid['uuid'], $filepath);
        return $guid;
    }
    public function updeval()
    {

    }  
    
    public function random_str(
    int $length = 64,
    string $keyspace = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&'
    ): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;      
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);

    }

    public function userid_gen()
    {
        $stop = 0;
        while(!$stop)
        {
            $user = "A".random_int(100000, 999999);
            $mysqli = new mysqli(DBH, DBU, DBP, DBN);
            $query = $mysqli->prepare("SELECT * FROM `users` WHERE userid = ?");
            $query->bind_param('s',$user);
            $query->execute();
            $result = mysqli_stmt_get_result($query);
            $rows = $result->num_rows;
            $query->close();              
            if($rows == 0)
            {
                $stop = true;
                return $user;
            }
        }
    }
    
    public function code_gen()
    {
        $stop = 0;
        while(!$stop)
        {
            $code = random_int(100000, 999999);
            $mysqli = new mysqli(DBH, DBU, DBP, DBN);
            $query = $mysqli->prepare("SELECT * FROM `users` WHERE code = ?");
            $query->bind_param('i',$code);
            $query->execute();
            $rows = $query->num_rows;
            $query->close();              
            if($rows == 0)
            {
                $stop = true;
                return $code;
            }
        }
    }
    
    public function pwd_valid($pwd)
    {
            $uppercase = preg_match('@[A-Z]@', $pwd);
        $lowercase = preg_match('@[a-z]@', $pwd);
        $number    = preg_match('@[0-9]@', $pwd);
        $specialChars = preg_match('@[^\w]@', $pwd);
        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($pwd) != 12)
        {
            return false;
        }
        else {
            return $pwd;
        }
    }

    public function pwd_gen()
    {
        $pwd = logocon::random_str(12);
        while(!logocon::pwd_valid($pwd))
        {
            $pwd = logocon::random_str(12);
        }
        return $pwd;
    }   
    public function getData($uuid)
    {
            $mysqli = new mysqli(DBH, DBU, DBP, DBN);
            $query = $mysqli->prepare("SELECT * FROM `users` WHERE elabuuid = ?");
            $query->bind_param('s',$uuid);
            $query->execute();    
            $result = mysqli_stmt_get_result($query);
            $rowuser = $result->fetch_array(MYSQLI_ASSOC);
            $query->close();   
                        
            $mysqli2 = new mysqli(DBH, DBU, DBP, DBN);
            $query2 = $mysqli2->prepare("SELECT * FROM `elaborati` WHERE uuid = ?");
            $query2->bind_param('s',$uuid);
            $query2->execute();          
            $result2 = mysqli_stmt_get_result($query2);
            $rowelab = $result2->fetch_array(MYSQLI_ASSOC);            
            $query2->close();   
                switch ($rowelab['status']) {
                    case 0:
                        $textstatus = "<span data-tag='rcvd'>Ricevuto</span>";
                        $textclass = "";
                        break;
                    case 1:
                        $textstatus = "<span data-tag='winner'>Vincitore</span>";
                        $textclass = "text-success";                        
                        break;
                    case 2:
                        $textstatus = "<span data-tag='discarded'>Eliminato</span>";
                        $textclass = "text-danger";                        
                    default:
                        break;
                }
        return array("code" => $rowuser['code'], "userid" => $rowuser['userid'], "surveyid" => $rowuser['surveyid'], "textstatus" => $textstatus, "textclass" => $textclass,
            "timestamp" => $rowelab['timestamp'], "vote" => $rowelab['vote'], "status" => $rowelab['status'], "hashorig" => $rowelab['hash'], "hashend" => $rowelab['endhash'], "notes" => $rowelab['notes'], "filepath" => $rowelab['filepath']);
    }
    public function getReceipt($cryptguid, $nonce)
    {
        $concat = sodium_crypto_secretbox_open(sodium_hex2bin($cryptguid), sodium_hex2bin($nonce), sodium_hex2bin(NACL));
        $array = explode(':', $concat);
        $uuid = $array[0];
        $passwd = $array[1];
        
        $details = logocon::getData($uuid);
        
        $code = $details['code'];
        $userid = $details['userid'];
        $timestamp = $details['timestamp'];
        $hashorig = $details['hashorig'];
        $hashend = $details['hashend'];
        
        $tpl = file_get_contents("../www/receipt.html");
        
        $res = str_replace("[BCODE]", logocon::barcode($code), $tpl);
        $res = str_replace("[TIMESTAMP]", date("d/m/Y H:i:s T", $timestamp), $res);        
        $res = str_replace("[CODE]", $code, $res);
        $res = str_replace("[USER]", $userid, $res);
        $res = str_replace("[PASSWD]", $passwd, $res);
        $res = str_replace("[OHASH]", $hashorig, $res);
        $res = str_replace("[EHASH]", $hashend, $res);
        
        file_put_contents("../tmp/".$code.".htm", $res);
        exec("/usr/local/bin/wkhtmltopdf --disable-smart-shrinking --footer-html ../res/footer.html ../tmp/".$code.".htm ../tmp/".$code.".pdf");
        fclose("../tmp/".$code.".htm");
        unlink("../tmp/".$code.".htm") or die("Couldn't delete file");  

        $receipt = base64_encode(file_get_contents("../tmp/".$code.".pdf"));
        unlink("../tmp/".$code.".pdf");

        return array("file" => $receipt, "code" => $code);       
    }
    
    public function getElab($cryptguid, $nonce)
    {
        $concat = sodium_crypto_secretbox_open(sodium_hex2bin($cryptguid), sodium_hex2bin($nonce), sodium_hex2bin(NACL));
        $array = explode(':', $concat);
        $uuid = $array[0];
        
        $details = logocon::getData($uuid);
        
        $code = $details['code'];
        $filepath = $details['filepath'];

        $elab = base64_encode(file_get_contents($filepath));

        return array("file" => $elab, "code" => $code);
    }

    public function userdlElab($session)
    {
        if(!logocon::isloggedin($session))
        {
            return false;
        }
        $uuid = logocon::getuuid($session['tokencr'], $session['userid']);
        $details = logocon::getData($uuid);
        
        $code = $details['code'];
        $filepath = $details['filepath'];

        $elab = base64_encode(file_get_contents($filepath));

        return array("file" => $elab, "code" => $code);        
    }
    
    public function dlElab($code)
    {
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `users` WHERE code = ?");
        $query->bind_param('s',$code);
        $query->execute();    
        $result = mysqli_stmt_get_result($query);
        $rowuser = $result->fetch_array(MYSQLI_ASSOC);
        $query->close();
        
        $uuid = $rowuser['elabuuid'];
        
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `elaborati` WHERE uuid = ?");
        $query->bind_param('s',$uuid);
        $query->execute();    
        $result = mysqli_stmt_get_result($query);
        $rowelab = $result->fetch_array(MYSQLI_ASSOC);
        $query->close();

        $filepath = $rowelab['filepath'];
        $elab = base64_encode(file_get_contents($filepath));

        return array("file" => $elab, "code" => $code);        
    }

    public function barcode($code)
    {
        $barcodeobj = new TCPDFBarcode($code, 'C128');
        $bc = $barcodeobj->getBarcodeSVGCode(1.5, 40, 'black');

        return '<div style="text-align: center;"><div>'.$bc.'</div><div style="font-family: OCR; text-align:center; padding:5px;">*'.$code.'*</div></div>';        
    }
    
    public function stamp($filepath, $details)
    {
        $bc = logocon::barcode($details['code']);
        
        $tpl = file_get_contents("../res/stamp.html");
        
        $res = str_replace("[BCODE]", $bc, $tpl);
        $res = str_replace("[DATETIME]", date("d/m/Y H:i:s T", $details['timestamp']), $res);     
        
        file_put_contents("../tmp/".$details['code'].".bcd.htm", $res);
        exec("/usr/local/bin/wkhtmltopdf --disable-smart-shrinking ../tmp/".$details['code'].".bcd.htm ../tmp/".$details['code'].".bcd.pdf");
        fclose("../tmp/".$details['code'].".bcd.htm");
        unlink("../tmp/".$details['code'].".bcd.htm") or die("Couldn't delete file");  
        exec("pdftk ".$filepath." stamp ../tmp/".$details['code'].".bcd.pdf output ".$filepath.".1");
        unlink("../tmp/".$details['code'].".bcd.pdf") or die("Couldn't delete file");  
        rename($filepath.".1", $filepath);

    }
    
    public function getElabsJSON()
    {
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `elaborati` WHERE 1");
        $query->execute();          
        $result = mysqli_stmt_get_result($query);
        $json['data'] = mysqli_fetch_all ($result, MYSQLI_ASSOC);
        $query->close();           
        
        return json_encode($json);
    }
    
    public function getDataTable()
    {
        $array = array();
            $mysqli = new mysqli(DBH, DBU, DBP, DBN);
            $query = $mysqli->prepare("SELECT * FROM `elaborati` WHERE 1");
            $query->execute();          
            $result = mysqli_stmt_get_result($query);
            $rows = mysqli_fetch_all ($result, MYSQLI_ASSOC);
            $query->close(); 
            foreach($rows as $row)
            {
                $uuid = $row['uuid'];
                $mysqli = new mysqli(DBH, DBU, DBP, DBN);
                $query = $mysqli->prepare("SELECT * FROM `users` WHERE elabuuid = ?");
                $query->bind_param('s',$uuid);
                $query->execute();    
                $result = mysqli_stmt_get_result($query);
                $rowuser = $result->fetch_array(MYSQLI_ASSOC);
                $query->close();   
                
                $mysqli2 = new mysqli(DBH, DBU, DBP, DBN);
                $query2 = $mysqli2->prepare("SELECT * FROM `elaborati` WHERE uuid = ?");
                $query2->bind_param('s',$uuid);
                $query2->execute();    
                $result2 = mysqli_stmt_get_result($query2);
                $rowelab = $result2->fetch_array(MYSQLI_ASSOC);
                $query->close();       
                switch ($rowelab['status']) {
                    case 0:
                        $textstatus = "<span data-tag='rcvd'>Ricevuto</span>";
                        $textclass = "text-danger";
                        break;
                    case 1:
                        $textstatus = "<span data-tag='winner'>Vincitore</span>";
                        $textclass = "text-success";                        
                        break;
                    case 2:
                        $textstatus = "<span data-tag='discarded'>Eliminato</span>";
                        $textclass = "";                        
                    default:
                        break;
                }
                $settings = logocon::getsettings();
                $contestend = $settings['contest_end_ts'];
                if(time() < $contestend)
                {
                    //contest non ancora terminato
                    $rowuser['surveyid'] = "Non disponibile";
                }
                $data = array("code" => $rowuser['code'], "surveyid" => $rowuser['surveyid'], "textstatus" => $textstatus, "textclass" => $textclass,
                    "timestamp" => $rowelab['timestamp'], "datetime" => date("d/m/Y H:i:s T", $rowelab['timestamp']), "vote" => $rowelab['vote'], "status" => $rowelab['status'], "hashorig" => $rowelab['hash'], "hashend" => $rowelab['endhash'], "notes" => $rowelab['notes'],
                    "buttons" => '  <div class="btn-group">
                                        <a href="ajax.php?action=updstatus&status=0&id='.$rowuser['code'].'" target="_blank" data-id="'.$rowuser['code'].'" title="Resetta stato" class="btn btn-warning"><i class="fas fa-undo"></i></a>
                                        <a href="ajax.php?action=updstatus&status=2&id='.$rowuser['code'].'" target="_blank" data-id="'.$rowuser['code'].'" title="Contrassegna come eliminato" class="deleteprop btn btn-danger"><i class="fas fa-trash"></i></a>
                                      <!--  <a href="#" class="btn btn-warning"><i class="fas fa-pencil-alt"></i></a> -->
                                        <a class="btn btn-success winprop" id="winprop" data-id="'.$rowuser['code'].'" href="ajax.php?action=updstatus&status=1&id='.$rowuser['code'].'" target="_blank" title="Contrassegna come vincitore"><i class="fas fa-award"></i></a>
                                        <a href="ajax.php?action=download&id='.$rowuser['code'].'" target="_blank" class="btn btn-primary" title="Scarica elaborato"><i class="fas fa-download"></i></a>
                                    </div>');                
                
                array_push($array, $data);
            }
            
            $json['data'] = $array;
        return json_encode($json);
    }   
    
    public function getsettings()
    {
        $mysqli = new mysqli(DBH, DBU, DBP, DBN);
        $query = $mysqli->prepare("SELECT * FROM `settings` WHERE id = 0");
        $query->execute();          
        $result = mysqli_stmt_get_result($query);
        $rows = mysqli_fetch_all ($result, MYSQLI_ASSOC);
        $query->close();    
        
        return $rows[0];
    }
}
