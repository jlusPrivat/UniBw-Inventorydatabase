<?php
if (!defined('INVDB'))
    die('No access');

$form = new Form();
$form->addField(new CSRFfield());
$form->addField(new Textfield('id', 'Nutzerkennung', true,
    subtext: 'RZ-Kennung oder User-ID',
    groupCss: 'mb-3',
    validator: function (string $input): bool {
        return preg_match('/^U[0-9]{1,10}$/i', $input) ||
        preg_match('/^[a-z0-9]{6,12}$/i', $input);
    },
    invalidMsg: 'Muss eine gültige Nutzerkennung sein'
));
$form->addField(new PasswordField('pwd', 'Passwort', true,
    groupCss: 'mb-3'
));


$sent = $form->wasSent();
if ($form->wasSent() && $form->isValid()) {
    // try to login
    $id = $form->getFieldContent('id');
    $pwd = $form->getFieldContent('pwd');
    $usr = User::getAuthedUser($id, $pwd);
    if (is_null($usr))
        $WARNMSG[] = 'Benutzername oder Passwort falsch';
    else if (!$usr->isValid())
        $WARNMSG[] = 'Der Aktivierungszeitraum des Benutzers ist abgelaufen';
    else if (!is_null($usr->emailVerification))
        $WARNMSG[] = 'Die E-Mail Adresse des Benutzers wurde noch nicht verifiziert';
    else {
        // login successful
        $SESS['UID'] = $usr->getUid();
        $USER = $usr;
        $NEXTACTION = 'lendings';
        return;
    }
}


$HEADING = 'Login';
$ECHO = '<div class="container border rounded border-primary p-3" style="max-width: 400px;">
<h3 class="mt-0 mb-3 p-0 text-center">Login</h3>
<form action="index.php?action=login" method="post">'
. $form->getFieldGroup('CSRF', $sent)
. $form->getFieldGroup('id', $sent)
. $form->getFieldGroup('pwd', $sent)
. '<div class="d-flex justify-content-between align-items-center">
    <button class="btn btn-primary" type="submit">Login</button>
    <a href="#" onclick="$(\'#userIdInfo\').collapse(\'toggle\');">Über Nutzerkennungen</a>
</div>
</form>
</div>

<div class="card collapse mt-5" id="userIdInfo"><div class="card-body">
    <h5 class="card-title">Über Nutzerkennungen</h5>
    <p>Jeder Benutzer hat eine User-ID bestehend aus einem "U" gefolgt von 1-10 Ziffern.
    Ferner können alle Benutzer einmalig mit einer Rechenzentrums-Kennung
    (RZ-Kennung / RZID) verknüpft werden, die dann ebenfalls als Nutzerkennung
    verwendet werden kann.</p>
    <p>Ist ein Benutzer mit einer RZ-Kennung verknüpft, wird sein Passwort standardmäßig
    zunächst über den LDAP-Dienst des Rechenzentrums überprüft. Ist diese Funktion in
    der Konfiguration deaktiviert, der Server nicht verfügbar oder die RZ-Kennung wurde
    zwischenzeitlich gelöscht oder deaktiviert, so wird eine Hash-Kopie des Passworts
    zur Überprüfung verwendet und eine entsprechende Nachricht angezeigt.</p>
    <p>Die lokale Hash-Kopie des Passworts wird nach jedem erfoglreichen Login und
    der erstmaligen Verknüpfung mit einer RZID erzeugt.</p>
</div></div>';
?>