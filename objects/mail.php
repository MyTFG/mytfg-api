<?php
namespace Mytfg\Objects;

class Mail {    
    public static function send($from, $to, $subject, $text) {
        $headers   = array();
        $headers[] = "From: " . $from;
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Subject: " . $subject;
        $headers[] = "Content-type: text/plain; chareset=iso-8859-1";
        $headers[] = "Content-Transfer-Encoding: quoted-printable";
        
        mail($to, $subject, $text, implode("\r\n", $headers));
    }
    
    public static function presetSignup($code) {
        $text = utf8_decode(file_get_contents("data/mail_presets/de/signup"));
        return sprintf($text, APP_NAME, $code);
    }
    
    
    
    #
    # OBJECT METHODS
    #
    
    private $mail;
    private $owner;
    
    public function __construct($mail) {
    
    }
    
    public function removeSubaddress() {
        return preg_replace("/\+.*@/", "@", $mail);
    }
}
?>