<?php
if (!defined('INVDB'))
    die('No access');

$formEdit = new Form();
$formEdit->addField(new CSRFfield());
$formEdit->addField(new Hiddenfield('editGID', '', true,
    validator: fn(string $input) => is_numeric($input) || $input == 'new'
));
$formEdit->addField(new Textfield('name', 'Gruppenname', true,
    subtext: 'Muss zwischen 4 und 30 Zeichen lang sein',
    groupCss: 'mb-3',
    validator: fn (string $input) => preg_match('/^[(\p{L}\p{M}*)\p{N} ]{4,30}$/', $input)
));
$formEdit->addField((new Boxfield('special', 'Spezielle Berechtigungen', false))
    ->addBox('checkbox', 'admin', 'Administratorgruppe'));

$formRemove = new Form();
$formRemove->addField(new CSRFfield());
$formRemove->addField(new Hiddenfield('removeGID', '', true,
    validator: fn(string $input) => is_numeric($input)
));


if ($formEdit->wasSent()) {
    // edit or new group form sent
    if (!$formEdit->isValid())
        $ERRMSG[] = 'Der Gruppenname ist nicht gültig';
    else {
        // check, if name is already in use
        $gid = $formEdit->getFieldContent('editGID');
        $name = $formEdit->getFieldContent('name');
        $admin = in_array('admin', $formEdit->getFieldContent('special'));
        $res = $DB->query('SELECT GID FROM id_groups WHERE name = "'
        . $DB->real_escape_string($name) . '"' . ($gid != 'new' ? ' AND GID <> "'
        . $DB->real_escape_string($gid) . '"' : '') . ' LIMIT 1');
        if ($res->num_rows > 0)
            $ERRMSG[] = 'Der Gruppenname ist bereits vergeben';
        
        else {
            // write to database
            if ($gid == 'new') {
                $DB->query('INSERT INTO id_groups (name, admin) VALUES ("'
                . $DB->real_escape_string($name) . '", ' . ($admin ? '1' : '0') . ')');
                $SUCCMSG[] = 'Die Gruppe <samp>' . $name . '</samp> wurde erfolgreich angelegt';
            }
            else {
                $DB->query('UPDATE id_groups SET name = "' . $DB->real_escape_string($name)
                . '", admin = ' . ($admin ? '1' : '0') . ' WHERE GID = "'
                . $DB->real_escape_string($gid) . '" LIMIT 1');
                $SUCCMSG[] = 'Die Gruppe <samp>' . $name . '</samp> wurde erfolgreich bearbeitet';
            }
        }
    }
}

else if ($formRemove->wasSent() && $formRemove->isValid()) {
    $gid = $formRemove->getFieldContent('removeGID');
    $DB->query('DELETE FROM id_match_ug WHERE GID = "' . $DB->real_escape_string($gid) . '"');
    $DB->query('DELETE FROM id_match_gi WHERE GID = "' . $DB->real_escape_string($gid) . '"');
    $DB->query('DELETE FROM id_groups WHERE GID = "' . $DB->real_escape_string($gid) . '" LIMIT 1');
    $SUCCMSG[] = 'Gruppe wurde erfolgreich gelöscht.';
}


// fetch all groups and display
$groups = $DB->query('SELECT A.GID, name, admin, COUNT(B.UID) AS usrcnt
FROM id_groups A LEFT JOIN id_match_ug B ON A.GID = B.GID GROUP BY A.GID');

$HEADING = 'Gruppen';
$ECHO = '<h3 class="mt-0 mb-3 p-0">Gruppenverwaltung</h3>
<table class="table table-striped table-hover mb-3"><thead class="thead-light"><tr>
<th>Group-ID</th><th>Name</th><th># Benutzer</th><th>Aktionen</th>
</tr></thead><tbody>';

while ($group = $groups->fetch_assoc()) {
    $ECHO .= '<tr' . ($group['admin'] ? ' class="table-info"' : '')
    . ' id="row-' . $group['GID'] . '"><td class="align-middle">' . $group['GID']
    . '</td><td class="align-middle">' . $group['name']
    . '</td><td class="align-middle">' . $group['usrcnt'] . '</td><td>'
    // button to show all users
    . '<button type="button" onclick="showUserList(' . $group['GID'] . ', \''
    . $group['name'] . '\')" class="btn btn-secondary bi-person-lines-fill p-1 me-2"
    data-toggle="tooltip" title="Alle Benutzer anzeigen"></button>'
    // button to edit the group
    . '<button type="button" class="btn btn-secondary bi-pencil p-1 me-2" '
    . 'data-toggle="tooltip" title="Bearbeiten" onclick="editGrp(\'' . $group['GID']
    . '\', \'' . $group['name'] . '\', ' . ($group['admin'] ? 'true' : 'false') . ')"></button>'
    // button to delete the group
    . '<button type="button" class="btn btn-danger bi-trash p-1" data-toggle="tooltip" '
    . 'title="Gruppe löschen" onclick="removeGrp(\'' . $group['GID']
    . '\', \'' . $group['name'] . '\')"></button>
    </td></tr>';
}

$ECHO .= '</tbody></table><div class="text-end">
<button type="button" class="btn btn-success" onclick="editGrp(\'new\', '
. '\'Neue Gruppe\', false)">Neue Gruppe erstellen</button>
</div>';

$SCRIPT = '<script src="html/groups.js"></script>

<div class="modal fade" id="userlist" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content"><form action="index.php?action=groups" method="post">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">'
            . $formEdit->getFieldGroup('CSRF', false)
            . $formEdit->getFieldGroup('editGID', false)
            . $formEdit->getFieldGroup('name', false)
            . $formEdit->getFieldGroup('special', false)
            . '</div><div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </div>
        </form></div>
    </div>
</div>

<div class="modal fade" id="remove" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content"><form action="index.php?action=groups" method="post">
            <div class="modal-header">
                <h5 class="modal-title" class="text-danger"></h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>'
            . $formRemove->getFieldGroup('CSRF', false)
            . $formRemove->getFieldGroup('removeGID', false)
            . '<div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
                <button type="submit" class="btn btn-danger">Löschen</button>
            </div>
        </form></div>
    </div>
</div>';
?>