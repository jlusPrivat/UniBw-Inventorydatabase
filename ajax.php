<?php
define('INVDB', '1.0');
require_once 'includes/core.php';

// list all actions and whether it is legal to execute it
$actions = [
    'groups-listuser' => $USER?->isAdmin() ?? false,
];

if (!isset($_GET['action']) || !isset($actions[$_GET['action']]) || !$actions[$_GET['action']])
    die('Insufficient privileges');


switch ($_GET['action']) {

    // outputs all usernames and UIDs when given a GID
    case 'groups-listuser':
        if (!isset($_GET['GID']) || !is_numeric($_GET['GID']))
            exit;
        $res = $DB->query('SELECT A.UID, B.name FROM id_match_ug A
        INNER JOIN id_users B ON A.UID = B.UID WHERE A.GID = "' . $_GET['GID'] . '"');
        $users = [];
        while ($row = $res->fetch_assoc())
            $users[$row['UID']] = $row['name'];
        echo json_encode($users);
        break;
    
}

coreCleanup();
?>