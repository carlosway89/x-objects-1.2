<?php
/**
 * User: "David Owen Greenberg" <owen.david.us@gmail.com>
 * Date: 20/11/12
 * Time: 06:07 PM
 */
class xo_email_message extends magic_object {
    public static $last_class_error = null;
    public function __construct($from,$to,$subject,$body){
        global $container;
        $tag = new xo_codetag( xo_basename(__FILE__),__LINE__,get_class(),__FUNCTION__);
        if ( $container->debug) echo "$tag->event_format: from=$from,to=$to,subject=$subject,body=$body<br>\r\n";
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
    }
    public function send($method = "mail"){
        $result = false;
        $headers = "From: $this->from\r\n".
            "Reply-To: $this->from\r\n".
            "MIME-Version: 1.0\r\n".
            "Content-Type: text/html; charset=ISO-8859-1\r\n";
        switch ( $method){
            case 'phpmailer':
                $to = $this->to;
                $from = $this->from;
                $mail = new PHPMailer();
                $mail->SetFrom($from);
                $mail->Subject = $this->subject;
                $msgHtml = $this->body;
                $mail->MsgHTML($msgHtml);
//                $mail->AddReplyTo($form);

                try{

                    $mail->AddAddress($to);
                    if($mail->Send()){
                        //echo "succes";
                        $result = true;
                    } else {
                        $result = false;
                    }
                    $mail->ClearAddresses();

                } catch(phpmailerException $e)
                {
                    echo $e->errorMessage();
                }
                catch(Exception $e)
                {
                    echo $e->getMessage();
                }
            break;
            case 'mail':
                $result = @mail($this->to,$this->subject,$this->body,
                    $headers
                    ,"-oi -f $this->from");
            break;
            case 'pear':
                require_once "Mail.php";
                $smtp = Mail::factory('smtp',
                    array ('host' => $host,
                        'auth' => true,
                        'username' => $username,
                        'password' => $password));

                $mail = $smtp->send($this->to, $headers, $this->body);

                if (PEAR::isError($mail)) {
                    echo("<p>" . $mail->getMessage() . "</p>");
                } else {
                    $result = true;

                }
            break;
        }

        return $result;
    }
    public static function create($from,$to,$subject,$body){
        $c = __CLASS__;
        return new $c($from,$to,$subject,$body);
    }
}
