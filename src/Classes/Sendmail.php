<?PHP
/**
 * Class Sendmail for mail handling
 */
class Sendmail {
    
    /**
     * Send a mail
     * 
     * @param   $to     - recipient user email
     * @param   $sub    - Subject of message
     * @param   $msg    - message to be deliverred
     * 
     * @return  bool    - true when send with success
     */
    public function send($to, $sub, $msg) {
        // In case lines are longer than 70 characters
        $message = wordwrap($msg, 70, "\r\n");
        
        // Headers
        $head = 'FromL noreply@matcha.com';
        
        return mail($to, $sub, $msg, $head);
    }
    
    /**
     * Send a password reset link
     * 
     * @param   $to     - recipient
     * 
     * @return  string  - token
     */
    public function sendPassReset($to) {
        // unique code for verification
        $code = md5(microtime(true));
        
        // Generated link.
        $link = $_SERVER['SERVER_NAME'] . "/reset_password?code=" . base64_encode($code);
        
        // messqge Body
        $msg = "A password reset request was sent you.\r\n
        Click <span><a href='$link'>here</a></span> toi reset it";
        
        $this->send($to, "Password Reset", $msg);
        
        return $code;
    }
}
?>