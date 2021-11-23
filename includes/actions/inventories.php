<?php
if (!defined('INVDB'))
    die('No access');

    
$HEADING = 'Inventare';
$ECHO = '<h3 class="mt-0 mb-3 p-0">Inventarverwaltung</h3>';
$SCRIPT = '<script src="html/inventories.js"></script>';


if (isset($_GET['IID'])) {
    if ($_GET['IID'] == 'new') {

        // create new inventory
        // find a new name
        $i = 0;
        do {
            $i++;
            $res = $DB->query('SELECT IID FROM id_inventories WHERE name = "Neues Inventar'
            . ($i > 1 ? ' ' . $i : '') . '" LIMIT 1');
        } while ($res->num_rows > 0);
        // save to database
        $DB->query('INSERT INTO id_inventories (name, description) VALUES ("Neues Inventar'
        . ($i > 1 ? ' ' . $i : '') . '", "")');
        $SUCCMSG[] = 'Neues Inventar erstellt';
        $IID = $DB->insert_id;

    }
    else if (!is_numeric($_GET['IID'])) {
        $ERRMSG[] = 'Ungültige Inventory ID';
        return;
    }
    else
        $IID = $_GET['IID'];

    // load inventory
    $res = $DB->query('SELECT IID, name, description FROM id_inventories WHERE IID = "'
    . $DB->real_escape_string($IID) . '" LIMIT 1');
    if (!($inv = $res->fetch_assoc())) {
        $ERRMSG[] = 'Ungeültige Inventory ID';
        return;
    }


    $form = new Form();
    $form->addField(new CSRFfield());
    $form->addField(new Textfield('IID', 'Inventar-ID', false,
        groupCss: 'col',
        defaultContent: $IID,
        fieldmodifier: ' readonly'
    ));
    $form->addField(new Textfield('name', 'Inventarname', true,
        subtext: 'Muss zwischen 4 und 30 Zeichen lang sein',
        groupCss: 'col-9',
        validator: fn (string $input) => preg_match('/^[(\p{L}\p{M}*)\p{N} ]{4,30}$/', $input),
        defaultContent: $inv['name']
    ));
    $form->addField(new Textareafield('description', 'Beschreibung', false,
        groupCss: 'mb-3',
        defaultContent: $inv['description'],
        rows: 3
    ));

    // edit inventory
    if ($form->wasSent() && $form->isValid()) {
        // check if name already taken
        $res = $DB->query('SELECT IID FROM id_inventories WHERE name = "'
        . $DB->real_escape_string($form->getFieldContent('name')) . '" AND IID <> "'
        . $DB->real_escape_string($IID) . '"');
        if ($res->num_rows > 0) {
            $ERRMSG[] = 'Der Name ist bereits vergeben';
        }
        else {

            // update the basic fields
            $DB->query('UPDATE id_inventories SET name = "'
            . $DB->real_escape_string($form->getFieldContent('name')) . '", description = "'
            . $DB->real_escape_string($form->getFieldContent('description')) . '" WHERE IID = "'
            . $DB->real_escape_string($IID) . '" LIMIT 1');
            // remove all previous group permission bindings
            $DB->query('DELETE FROM id_match_gi WHERE IID = "' . $DB->real_escape_string($IID) . '"');

            // insert new permissions
            $permissionOrder = array('view_items', 'lend_from', 'return_to', 'manage_lendings'
            , 'manage_users', 'perform_stocktaking', 'edit_items');
            $perms = array(); // when group 3 may lend_from: [3 => [1]]
            foreach ($permissionOrder as $key => $val) {
                foreach ($_POST[$val] ?? [] as $id) {
                    // $key: the index of the permission name
                    // $id: group id for which the permission is 
                    if (is_numeric($id))
                        $perms[$id][] = $key;
                }
            }
            
            $query = array();
            foreach ($perms as $gid => $perm) {
                $appendum = array();
                for ($i = 0; $i < count($permissionOrder); $i++) {
                    $appendum[] = (in_array($i, $perm) ? '1' : '0');
                }
                $query[] = '"' . $gid . '", "' . $IID . '", ' . implode(', ', $appendum);
            }
            if (!empty($query)) {
                $DB->query('INSERT INTO id_match_gi (GID, IID, ' . implode(', ', $permissionOrder)
                . ') VALUES (' . implode('), (', $query) . ')');
            }

            if ($DB->errno == 0)
                $SUCCMSG[] = 'Änderungen erfolgreich gespeichert';
            else
                lg(2, $DB->error);

        }
    }

    // load all associated groups
    $resInvGrp = $DB->query('SELECT A.*, B.name FROM id_match_gi A '
    . 'INNER JOIN id_groups B ON A.GID=B.GID WHERE IID = "'
    . $DB->real_escape_string($IID) . '"');
    // load all groups, regardless of association
    $resGrp = $DB->query('SELECT GID, name FROM id_groups');
    // the list of associated grp ids
    $assGrps = array();


    $ECHO .= '<form action="index.php?action=inventories&IID='
    . $IID . '" method="post">'
    . $form->getFieldGroup('CSRF')
    . '<div class="row mb-3">'
    . $form->getFieldGroup('IID')
    . $form->getFieldGroup('name', true)
    . '</div>'
    . $form->getFieldGroup('description', true)
    . '<h5>Gruppenberechtigungen</h5>
    <table class="table table-striped table-hover table-bordered mb-3 align-middle">
    <thead class="thead-light align-middle"><tr>
    <th style="width: 100%;">Gruppe</th>
    <th data-toggle="tooltip" title="Kann das gesamte Inventar sehen. 
    Selbst ausgeliehene Items sind immer sichtbar.">Sehen</th>
    <th data-toggle="tooltip" title="Kann alle Items im Inventar
    bearbeiten.">Bearbeiten</th>
    <th data-toggle="tooltip" title="Kann Inventuren durchführen.">Inventur</th>
    <th data-toggle="tooltip" title="Kann für sich selbst
    Items ausleihen.">Ausleihen</th>
    <th data-toggle="tooltip" title="Kann für sich selbst
    ausgeliehene Items zurückgeben.">Zurückgeben</th>
    <th data-toggle="tooltip" title="Kann Leihungen verwalten, wie zum Beispiel den
    Leihenden und das Rückgabedatum ändern oder alle Items zurückgeben.">Leihungen
    verwalten</th>
    <th data-toggle="tooltip" title="Kann neue Benutzer registrieren und mit diesem Inventar verknüpfte
    Benutzer bearbeiten">Benutzer verwalten</th>
    <th>&nbsp;</th>
    </tr></thead><tbody id="grptbody" class="text-center">'
    . '<tr id="grprow-" style="display: none;"><td class="text-start">#<span class="grpid"></span>
    <span class="fw-bold grpname">Admin</span></td>
    <td><input type="checkbox" class="form-check-input" name="view_items[]" value=""></td>
    <td><input type="checkbox" class="form-check-input" name="edit_items[]" value=""></td>
    <td><input type="checkbox" class="form-check-input" name="perform_stocktaking[]" value=""></td>
    <td><input type="checkbox" class="form-check-input" name="lend_from[]" value=""></td>
    <td><input type="checkbox" class="form-check-input" name="return_to[]" value=""></td>
    <td><input type="checkbox" class="form-check-input" name="manage_lendings[]" value=""></td>
    <td><input type="checkbox" class="form-check-input" name="manage_users[]" value=""></td>
    <td><button type="button" class="btn btn-danger bi-trash p-1" data-toggle="tooltip"
    title="Gruppe löschen (Doppelklick)"></button></td></tr>';
    while ($grp = $resInvGrp->fetch_assoc()) {
        $assGrps[] = $grp['GID'];
        $ECHO .= '<tr id="grprow-' . $grp['GID'] . '"><td class="text-start">#<span class="grpid">'
        . $grp['GID'] . '</span> <span class="fw-bold grpname">'
        . $grp['name'] . '</span></td>
        <td><input type="checkbox" class="form-check-input" name="view_items[]" value="'
        . $grp['GID'] . '"' . ($grp['view_items'] == 1 ? ' checked' : '') . '></td>
        <td><input type="checkbox" class="form-check-input" name="edit_items[]" value="'
        . $grp['GID'] . '"' . ($grp['edit_items'] == 1 ? ' checked' : '') . '></td>
        <td><input type="checkbox" class="form-check-input" name="perform_stocktaking[]" value="'
        . $grp['GID'] . '"' . ($grp['perform_stocktaking'] == 1 ? ' checked' : '') . '></td>
        <td><input type="checkbox" class="form-check-input" name="lend_from[]" value="'
        . $grp['GID'] . '"' . ($grp['lend_from'] == 1 ? ' checked' : '') . '></td>
        <td><input type="checkbox" class="form-check-input" name="return_to[]" value="'
        . $grp['GID'] . '"' . ($grp['return_to'] == 1 ? ' checked' : '') . '></td>
        <td><input type="checkbox" class="form-check-input" name="manage_lendings[]" value="'
        . $grp['GID'] . '"' . ($grp['manage_lendings'] == 1 ? ' checked' : '') . '></td>
        <td><input type="checkbox" class="form-check-input" name="manage_users[]" value="'
        . $grp['GID'] . '"' . ($grp['manage_users'] == 1 ? ' checked' : '') . '></td>
        <td><button type="button" class="btn btn-danger bi-trash p-1" data-toggle="tooltip"
        title="Gruppe löschen (Doppelklick)" ondblclick="remGrp(' . $grp['GID']
        . ')"></button></td></tr>';
    }
    $ECHO .= '</tbody></table><div class="row justify-content-between"><div class="col">
    <a href="index.php?action=inventories" class="btn btn-secondary me-3" role="button">Zurück</a>
    <input type="submit" value="Speichern" class="btn btn-primary">
    </div><div class="col text-end">
    <button type="button" class="btn btn-success float-end"
    onclick="addGrp()">Gruppe hinzufügen</button>
    <select id="grpselect" class="form-select me-3 float-end" style="width: auto;"
    data-toggle="tooltip" data-trigger="manual" title="Bitte Gruppe auswählen">
    <option value="none" selected>Gruppe auswählen</option>';
    while ($grp = $resGrp->fetch_assoc()) {
        $ECHO .= '<option value="' . $grp['GID'] . '"'
        . (in_array($grp['GID'], $assGrps) ? ' disabled' : '')
        . '>' . $grp['name'] . '</option>';
    }
    $ECHO .= '</select>
    </div></div></form>';

}


else {

    $deleteForm = new Form();
    $deleteForm->addField(new CSRFfield());
    $deleteForm->addField(new Hiddenfield('IID', '', true,
        validator: fn(string $input) => is_numeric($input)
    ));

    if ($deleteForm->wasSent() && $deleteForm->isValid()) {
        $IID = $deleteForm->getFieldContent('IID');
        // remove user bindings
        $DB->query('UPDATE id_users SET IID_primary = NULL WHERE IID_primary = "'
        . $DB->real_escape_string($IID) . '"');
        // remove permissions
        $DB->query('DELETE FROM id_match_gi WHERE IID = "'
        . $DB->real_escape_string($IID) . '"');
        // remove all items and their lendings
        $DB->query('DELETE A, B FROM id_items A
        LEFT OUTER JOIN id_lendings B ON A.ITID = B.ITID
        WHERE A.IID = "' . $DB->real_escape_string($IID) . '"');
        // remove the inventory itself
        $DB->query('DELETE FROM id_inventories
        WHERE IID = "' . $DB->real_escape_string($IID) . '"');
        $WARNMSG[] = 'Inventar erfolgreich gelöscht.';
    }

    // fetch all groups and display
    $invs = $DB->query('SELECT IID, name, description FROM id_inventories');

    $ECHO .= '<table class="table table-striped table-hover mb-3"><thead class="thead-light"><tr>
    <th>Inventar-ID</th><th>Name</th><th>Beschreibung</th><th>&nbsp;</th>
    </tr></thead><tbody>';

    while ($inv = $invs->fetch_assoc()) {
        $ECHO .= '<tr><td class="align-middle">' . $inv['IID']
        . '</td><td class="align-middle fw-bold">' . $inv['name']
        . '</td><td class="align-middle">' . htmlspecialchars($inv['description'])
        . '</td><td class="text-end"><a href="index.php?action=inventories&IID=' . $inv['IID']
        . '" class="bi-pencil btn btn-secondary p-1 me-3" data-toggle="tooltip" title="Bearbeiten"></a>
        <button type="button" onclick="remInv(' . $inv['IID'] . ', \'' . $inv['name']
        . '\')" class="bi-trash btn btn-danger p-1" data-toggle="tooltip" title="Löschen"></a>
        </td></tr>';
    }

    $ECHO .= '</tbody></table><div class="text-end">
    <a href="index.php?action=inventories&IID=new" role="button" 
    class="btn btn-success">Neues Inventar erstellen</a></div>';

    $SCRIPT .= '<div id="remove-modal" class="modal fade" tabindex="-1">
    <form action="index.php?action=inventories" method="post">'
    . $deleteForm->getFieldGroup('CSRF')
    . $deleteForm->getFieldGroup('IID')
    . '<div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">
    Wirklich Inventar <span id="remove-name"></span> löschen?</h5>
    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
    </button></div>
    <div class="modal-body">Es werden alle Items, auch die ausgeliehenen gelöscht. Außerdem werden alle
    Benutzerverknüpfungen mit diesem Inventar aufgehoben. Die Aktion ist nicht umkehrbar.</div>
    <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
    <input type="submit" class="btn btn-danger" value="Wirklich löschen"></div>
    </div></div></form></div>';

}
?>