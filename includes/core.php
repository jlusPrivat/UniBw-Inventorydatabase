<?php
if (!defined('INVDB'))
    die('No access');

// include the configuration file
require_once 'config.php';
require_once 'includes/funcs.php';
require_once 'includes/user.php';
require_once 'includes/group.php';

// use PHP sessions for short term cookying
session_start();
// initialize global vars
$ERRMSG = array();
$WARNMSG = array();
$SUCCMSG = array();

// kill session if exceeded timeout
$SESS = $_SESSION[$CONFIG['session']['id']] ?? array();
if (!empty($SESS)) {
    if (!isset($SESS['last_time']))
        $SESS['last_time'] = time();
    elseif ($SESS['last_time'] + (60 * $CONFIG['session']['timeout']) < time()) {
        $SESS = array();
        session_destroy();
    }
    else
        $_SESSION["pb"]["last_time"] = time();
}
// generate CSRF token
if (!isset($SESS['CSRF']))
    $SESS['CSRF'] = random_int(0, 99999);

// connect to the database
$DB = @new mysqli($CONFIG['mysql']['host'],
                 $CONFIG['mysql']['user'],
                 $CONFIG['mysql']['passwd'],
                 $CONFIG['mysql']['database']);
if ($DB->connect_errno)
    lg(2, 'Could not connect to database');

// load all groups
Group::reloadGroups();

// load the current user, if set
$USER = isset($SESS['UID']) ? new User($SESS['UID']) : null;

/* perform cleaning actions after all processing functions */
function coreCleanup (): void {
    global $CONFIG, $DB, $SESS;
    $_SESSION[$CONFIG['session']['id']] = $SESS;
    mysqli_close($DB);
}
?>