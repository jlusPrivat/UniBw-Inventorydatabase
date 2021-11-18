<?php
if (!defined('INVDB'))
    die('No access');

if (($_GET['CSRF'] ?? '') == $SESS['CSRF']) {
    unset($SESS['UID']);
    $USER = NULL;
    $SUCCMSG[] = 'Sie wurden erfolgreich abgemeldet.';
    $NEXTACTION = 'login';
    return;
}
else
    $ERRMSG[] = 'Illegaler Zugriff';

$HEADING = 'Logout';
$ECHO = '';
?>