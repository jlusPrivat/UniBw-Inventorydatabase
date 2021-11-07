<?php
if (!defined('INVDB'))
    die('No access');

if (isset($_GET['id'], $_GET['code'], $_GET['accept'])
    && is_numeric($_GET['id'])) {
    // check, if user has been activated already
    $res = $DB->query('SELECT UID FROM id_users WHERE UID = "'
    . $DB->real_escape_string($_GET['id']) . '" AND email_verification = "'
    . $DB->real_escape_string($_GET['code'])
    . '"')->num_rows;
    if ($res != 1)
        $WARNMSG[] = 'Der Benutzer wurde bereits aktiviert oder existiert nicht.';
    else {
        // activate user, if accept is granted
        if (boolval($_GET['accept'])) {
            $DB->query('UPDATE id_users SET email_verification = NULL WHERE UID = "'
            . $DB->real_escape_string($_GET['id']) . '" LIMIT 1');
            $SUCCMSG[] = 'Der Benutzer wurde erfolgreich aktiviert.';
            if (is_null($USER)) {
                $NEXTACTION = 'login';
                return;
            }
        }
        else {
            // delete the user, if he doesnt want to be registered
            $us = new User($_GET['id']);
            if ($us->isDeleteable()) {
                $us->delete();
                $WARNMSG[] = 'Der Benutzer wurde erfolgreich gelöscht.';
            }
            else
                $WARNMSG[] = 'Der Benutzer durfte nicht gelöscht werden.
                Sind noch Gegenstände ausgeliehen?';
        }
    }
}
else
    $ERRMSG[] = 'Illegaler Zugriff';

$HEADING = 'Addressverifikation';
$ECHO = '';
?>