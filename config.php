<?php
if (!defined('INVDB'))
    die('No access');

$CONFIG = [
	'basepath' => 'http://192.168.1.3/invDB/',
	'mysql' => [
		'host' => 'localhost',
		'user' => 'testuser',
		'passwd' => 'testaa',
		'database' => 'test',
		'prefix' => 'id'
	],
	'smtp' => [
		// check PHPMailer configuration settings
		'host' => 'apfel.rocks',
		'auth' => true,
		'username' => 'testuser@apfel.rocks',
		'password' => '!!!',
		'senderAddress' => 'testuser@apfel.rocks',
		'senderName' => 'UniBwM Inventar',
		'security' => 'tls', // alternatively ssl
		'port' => 587
	],
	'session' => [
		'id' => 'id',
		'timeout' => 60 // in minutes
	],
	'terminals' => [
		'127.0.0.1'
	],
    'auth' => [
        'allow-guest-registration' => true,
		// for syntax check https://www.php.net/manual/de/dateinterval.construct.php
		// set to NULL to not limit validity
		'guest-registration-default-validity' => new DateInterval('P1Y'),
        'use-ldap-for-login' => true,
		'force-registration-with-rzid' => false
    ],
	// this does not include the PHP errors.
	// Those must be configured in PHP.ini
	'errors' => [
		'display-enabled' => true,
		'display-min-severity' => 0,
		'log-enabled' => true,
		'log-min-severity' => 0,
		'log-file' => 'log.txt'
	]
];
?>