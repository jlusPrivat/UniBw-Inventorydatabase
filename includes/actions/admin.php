<?php
if (!defined('INVDB'))
    die('No access');

$configpath = 'config.php';
$configrealpath = realpath($configpath);

$form = new Form();
$form->addField(new CSRFfield());
$form->addField(new Textareafield('config', 'Konfiguration', true,
    subtext: $configrealpath,
    groupCss: 'mb-3',
    validator: function (string $input): bool {
        return !empty($input);
    },
    invalidMsg: 'Darf nicht leer sein',
    defaultContent: file_get_contents($configpath),
    rows: 30
));


$sent = $form->wasSent();
$isWritable = is_writable($configpath);
if ($sent && $form->isValid() && $isWritable) {
    if (file_put_contents($configpath, $form->getFieldContent('config')) !== false)
        $SUCCMSG[] = 'Konfiguration erfolgreich bearbeitet. Einige Änderungen werden '
        . 'erst mit dem Neuladen der Seite wirksam';
    else
        $ERRMSG[] = 'Konfiguration konnte nicht bearbeitet werden';
}


if (!$isWritable)
    $ERRMSG[] = 'Die Konfigurationsdatei <samp>' . $configrealpath . '</samp> kann nicht '
    . 'schreibend geöffnet werden. Änderungen können daher nicht gespeichert werden.';

$HEADING = 'Administration';
$ECHO = '<h3 class="mt-0 mb-3 p-0">Globale Konfiguration</h3>
<p>Hier können die globalen Einstellungen vorgenommen werden. Es ist Vorsicht geboten, da Fehlkonfigurationen oder
andere nicht erlaubte Änderungen an der Datei das Inventarisierungssystem deaktivieren könnten. Es ist daher
empfohlen eine Kopie der Datei anzufertigen.</p>
<form action="index.php?action=admin" method="post">'
. $form->getFieldGroup('CSRF', $sent)
. $form->getFieldGroup('config', $sent)
. '<button class="btn btn-primary'
. ($isWritable ? '' : ' disabled')
. '" type="submit">Konfiguration Speichern</button>
</form>';
?>