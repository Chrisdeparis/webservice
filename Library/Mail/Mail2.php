<?php
 
namespace Library\Mail;

require_once(LIB_ROOT."Mail/PHPMailer/_lib/class.smtp.php");
require_once(LIB_ROOT."Mail/PHPMailer/_lib/class.phpmailer.php");


abstract class Mail {

    public function __construct(){

    }


    /**
     * [envoyerMail qui envoit des mails : ne doit pas etre appellé par le client]
     * @param  [type] $mailExped [description]
     * @param  [type] $mailDest  [description]
     * @param  [type] $body      [description]
     * @param  [type] $subject   [description]
     * @param  [type] $template  [description]
     * @return [type]            [description]
     */
    public function envoyerMail($mailExped, $mailDest, $body, $subject, $template){
        
        

        $mail = new \PhpMailer\PhpMailer();
        $mail->IsSMTP();
        $mail->IsHTML(true);
        //$mail->SMTPDebug = 2; 



        //yahoo
        /*$mail->SMTPAuth = true;
        $mail->Host = "ssl://188.125.69.59:465"; // SMTP server
        $mail->Username = "fourneaux@yahoo.fr";
        $mail->Password = "acnologia"; 
        $mail->From=$mailExped;
        $mail->Subject = $subject;*/


        //hostinger
        /*$mail->SMTPAuth = true;
        //$mail->Host = "mx1.hostinger.fr"; 
        $mail->Host = "ssl://mx1.hostinger.fr:2525"; 
        $mail->Username = "mailer@chefdesfourneaux-api.16mb.com";
        $mail->Password = "Fourneaux1234"; 
        $mail->Port=2525;
        $mail->From=$mailExped;
        $mail->Subject = $subject;*/
        


        //hostinger
        $mail->SMTPAuth = true;
        //$mail->Host = "mx1.hostinger.fr"; 
        $mail->Host = "ssl://smtp.google.com:465"; 
        $mail->Username = "chefdesfourneaux@gmail.com";
        $mail->Password = "Fourneaux1234"; 
        $mail->Port=465;
        $mail->From=$mailExped;
        $mail->Subject = $subject;




        
        $tpl=APP_ROOT."/Models/ViewMail/".$template.".phtml";
        //echo $tpl;

        if(file_exists($tpl)){
            //echo "ici  sjkqhj";
            ob_start(); //lance au cas ou ca ne serait pas lancé
            $save=ob_get_clean();
            ob_start();
            $content_mail = $body;
            include($tpl);
            //$tmp=ob_get_contents();
            $mail->Body = ob_get_clean();  //contient le contenu du mail a l'interieur du template
            echo $save;     //remet le contenu du buffer qui n'a pas eté arreté
        }else{
            $mail->Body = $body;
        }
        

        $mail->AddAddress($mailDest);
        //$mail->AddReplyTo($mailDest);
        
        if( (!$mail->Send() ) ){
            echo "msg 2erreur :".$mail->ErrorInfo;
            $mail->SmtpClose();

            return false;
        }else{
            $mail->SmtpClose();
            return true;
        }


    }



    







}