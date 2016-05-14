<?php
namespace Mytfg\Objects;
class Imap {
    public static function imap_decode($inbox, $email_number) {
		$res = array();
		$overview  = imap_fetch_overview($inbox, $email_number, 0);
		$structure = imap_fetchstructure($inbox,  $email_number);
		$header    = imap_header($inbox, $email_number);

        
		$message = false;

		$encoding = $structure->encoding;
        $parameters = $structure->parameters;
        $params = array();
        foreach ($parameters as $param) {
            $params[$param->attribute] = $param->value;
        }
        
        
        if ($structure->type == TYPEMULTIPART) {      
            foreach ($structure->parts as $id => $part) { 
                if ($part->type == TYPETEXT) {                 
                    $partnum = $id + 1;
                    $encoding = $part->encoding;
                    $params = array();
                    foreach ($part->parameters as $param) {
                        $params[$param["attribute"]] = $param["value"];
                    }
                    
                    if ($part->subtype == "PLAIN") {
                        $charset = $params['charset'];
                        $message = Mail::decodeText($encoding, $charset, imap_fetchbody($inbox, $email_number, $partnum));
                    } else if ($part->subtype == "HTML") { 
                        $charset = $params['charset'];                   
                        $message = convert_html_to_text(Mail::decodeText($encoding, $charset, imap_fetchbody($inbox, $email_number, $partnum)));                    
                    }
                }
            }
        } else {
            $charset = $params['charset'];
			$message = Mail::decodeText($encoding, $charset, imap_body($inbox, $email_number));  
        }       

		if(!empty($header->reply_toaddress)) {
			$from = $header->reply_to[0]->mailbox."@".$header->reply_to[0]->host;
		} else {
			$from = $header->from[0]->mailbox."@".$header->from[0]->host;
		}
        
        $message = str_replace("\n", "\n\n", str_replace("\r\n", "\n", trim($message)));

		$res["from"] = $from;
		$res["date"] = utf8_decode(imap_utf8($overview[0]->date));
		$res["subject"] = utf8_decode(imap_utf8($overview[0]->subject));
		$res["message"] = $message;
		$res["to"] = $header->to[0]->mailbox;

		return $res;
	}
    
    private static function decodeText($encoding, $charset, $text) {
        switch ($encoding) {
            default:
                break;
            case 1:
                $text = imap_8bit($text);
                $text = quoted_printable_decode($text);
                break;
            case 2:
                $text = imap_binary($text);
                $text = quoted_printable_decode($text);
                break;
            case 3:
                $text = imap_base64($text);
                break;
            case 4:
                $text = quoted_printable_decode($text);
                break;                
        }
        
        return $text;
    }
}
?>