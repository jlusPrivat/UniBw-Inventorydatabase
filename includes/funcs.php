<?php
if (!defined('INVDB'))
    die('No access');


/* logs and displays events to a file. Outputs are generally outputted as error messages
@param int $severity 0=WARN, 1=ERROR, 2=CRIT
@param string $msg the message to be stored */
function lg (int $severity, string $msg): void {
	global $CONFIG, $ERRMSG;
	
	// first store to the file
	if ($CONFIG['errors']['log-enabled']
		&& $CONFIG['errors']['log-min-severity'] <= $severity)
		@error_log("$severity: $msg", 3, $CONFIG['errors']['log-file']);
	
	// generate the output message
	if ($CONFIG['errors']['display-enabled']
		&& $CONFIG['errors']['display-min-severity'] <= $severity)
		$ERRMSG[] = $msg;
	
	// kill the application, if severity is critical
	if ($severity >= 2)
		die('Critical failure: ' . end($ERRMSG));
}


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
/* Sends an email via the configured SMTP server
@return bool true=successfully sent false=could not be sent
*/
function sendMail (string $recipient, string $subject, string $htmlContent): bool {
    global $CONFIG, $ERRMSG;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $CONFIG['smtp']['host'];
        $mail->SMTPAuth = $CONFIG['smtp']['auth'];
        $mail->Username = $CONFIG['smtp']['username'];
        $mail->Password = $CONFIG['smtp']['password'];
        $mail->SMTPSecure = $CONFIG['smtp']['security'];
        $mail->Port = $CONFIG['smtp']['port'];
        $mail->setFrom($CONFIG['smtp']['senderAddress'], $CONFIG['smtp']['senderName']);
        $mail->addAddress($recipient);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->Subject = $subject;
        $mail->Body = $htmlContent;
        $mail->send();
        return true;
    }
    catch (Exception $e) {
        $ERRMSG[] = 'E-Mail konnte nicht verschickt werden: ' . $mail->ErrorInfo;
        return false;
    }
}



/* performs an LDAP authentication on the UniBwM net
@param string $rzid the id the authenticate
@param string $pwd the password to authenticate
@return int 0=successfull, 1=server unavailable, 2=user not found, 3=password incorrect
*/
function ldapAuthenticate (string $rzid, string $pwd): int {
    $ldap = ldap_connect('ldaps://ldap.unibw.de', 636);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    
    // try the anonymous bind first
    if (!@ldap_bind($ldap)) {
        @ldap_close($ldap);
        return 1;
    }
    
    // gather the entries with the correct rzid
    $res = ldap_get_entries($ldap, ldap_search($ldap,
        'dc=unibw-muenchen,dc=De', '(uid=' . $rzid . ')'));
    // user not found or not unique
    if ($res['count'] != 1) {
        @ldap_close($ldap);
        return 2;
    }
    
    // perform the authenticated bind with the dn gathered before
    if (@ldap_bind($ldap, $res[0]['dn'], $pwd)) {
        @ldap_close($ldap);
        return 0; // successfully logged in
    }
    @ldap_close($ldap);
    return 3; // password incorrect
}



/* Generates a random string
@param string $length the number of characters of the output string
*/
function randomString (int $length): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

?>