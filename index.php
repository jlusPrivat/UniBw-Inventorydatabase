<?php
define('INVDB', '1.0');
require_once 'includes/core.php';
require_once 'includes/action.php';
require_once 'includes/form.php';


// list all available actions
$ac_isLoggedIn = function () use (&$USER) {return !is_null($USER);};
$ac_isNotLoggedIn = function () use (&$USER) {return is_null($USER);};
$ac_enableRegister = function () use (&$USER, &$CONFIG)
	{return is_null($USER) && $CONFIG['auth']['allow-guest-registration'];};
$ac_dropdownUserMenu = function () use (&$USER)
	{return isset($USER) && $USER->hasPermission('manage_users', NULL);};
$ac_simpleUserMenu = function () use (&$USER)
	{return isset($USER) && !$USER->hasPermission('manage_users', NULL);};
$ac_isAdmin = function () use (&$USER) {return $USER?->isAdmin() ?? false;};

$actions = [
	// actions when not logged in
	new Action('login', 'Login', 1, $ac_isNotLoggedIn),
	new Action('register', 'Registrieren', 1, $ac_enableRegister),

	// actions when logged in
	new Action('lendings', 'Leihungen', 1, $ac_isLoggedIn),
	new Action('user', 'Benutzer', 1, $ac_simpleUserMenu),
	// the user dropdown menu
	$ac_usr1 = new Action('user', 'Mein Benutzer', 0, $ac_dropdownUserMenu),
	$ac_usr2 = new Action('user&register=1', 'Neuer Benutzer', 0, $ac_dropdownUserMenu),
	$ac_usr3 = new Action('userlist', 'Benutzerliste', 0, $ac_dropdownUserMenu),
	new Action('', 'Benutzer', 1, $ac_dropdownUserMenu,
		children: [$ac_usr1, $ac_usr2, $ac_usr3]),
	// the admin dropdown menu
	$ac_adm1 = new Action('admin', 'Allgemein', 0, $ac_isAdmin),
	$ac_adm2 = new Action('groups', 'Gruppen', 0, $ac_isAdmin),
	$ac_adm3 = new Action('inventories', 'Inventare', 0, $ac_isAdmin),
	new Action('', 'Admin', 1, $ac_isAdmin, children: [$ac_adm1, $ac_adm2, $ac_adm3]),
	new Action('logout', 'Logout', 1, $ac_isLoggedIn, csrf: true),

	// always visible footer actions
	new Action('about', 'Über', 2),
	new Action('privacy', 'Datenschutzerklärung', 2),

	// invisible actions, only reachable via links
	new Action('verifyMail', '', 0)
];

// determine the current action
foreach ($actions as $act) {
	if ($act->short == ($_GET['action'] ?? '') && $act->isActive() && $act->short != '') {
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