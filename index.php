<?php
define('INVDB', '1.0');
require_once 'includes/core.php';
require_once 'includes/action.php';
require_once 'includes/form.php';


// list all available actions
$actions = [
	// actions when not logged in
	new Action('login', 'Login', 1, '!isLoggedIn', 'false'),
	new Action('register', 'Registrieren', 1, 'guestRegistrationAllowed', 'false'),
	new Action('lendings', 'Leihungen', 1, 'isLoggedIn', 'false'),
	// actions when logged in
	new Action('logout', 'Logout', 1, 'isLoggedIn', 'false', csrf: true),
	// always visible footer actions
	new Action('about', 'Über', 2, 'true'),
	new Action('privacy', 'Datenschutzerklärung', 2, 'true'),
	// invisible actions, only reachable via links
	new Action('verifyMail', '', 0, 'true')
];

// determine the current action
foreach ($actions as $act) {
	if ($act->short == ($_GET['action'] ?? '') && $act->isActive()) {
		$ACTION = $act->short;
		break;
	}
}
$ACTION = $ACTION ?? (is_null($USER) ? 'login' : 'lendings');

// call the action
do {
	if (isset($NEXTACTION)) {
		$ACTION = $NEXTACTION;
		unset($NEXTACTION);
	}
	$file = 'includes/actions/' . $ACTION . '.php';
	if (is_file($file))
		include $file;
	else
		lg(2, 'Could not locate file ' . $file);
} while (isset($NEXTACTION));

include 'html/main.php';

coreCleanup();
?>