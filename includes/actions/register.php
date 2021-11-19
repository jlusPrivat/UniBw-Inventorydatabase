<?php
if (!defined('INVDB'))
    die('No access');

$HEADING = 'Registrierung';

// generate the form fields
$form = new Form();
$form->addField(new CSRFfield());
$form->addField(new Textfield('name', 'Anzeigename', true,
    groupCss: 'col',
    validator: function (string $input): bool {
        return preg_match('/^(\p{L}\p{M}*)[(\p{L}\p{M}*)\p{N} ]{3,28}[(\p{L}\p{M}*)\p{N}]$/u', $input);
    },
    invalidMsg: 'Muss zwischen 5 und 30 Zeichen lang sein'
));
$form->addField(new Textfield('rzid', 'RZ-Kennung', $CONFIG['auth']['force-registration-with-rzid'],
    groupCss: 'col',
    validator: function (string $input): bool {
        return preg_match('/^[a-z0-9]{6,12}$/i', $input);
    },
    invalidMsg: 'Muss eine gültige RZ-Kennung sein'
));
$form->addField(new Mailfield('mail', 'E-Mail Adresse', true,
    subtext: 'Wird für Benachrichtigungen verwendet und muss verifiziert werden. Muss keine 
    <samp>@unibw.de</samp> Adresse sein.',
    groupCss: 'mb-3',
    validator: function (string $input): bool {
        return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
    },
    invalidMsg: 'Muss eine gültige E-Mail-Adresse sein'
));
$form->addField(new PasswordField('pwd1', 'Passwort', true,
    groupCss: 'col',
    validator: function (string $input): bool {
        $len = strlen($input);
        return $len >= 5 && $len <= 200;
    },
    invalidMsg: 'Muss zwischen 5 und 200 Zeichen haben'
));
$form->addField(new PasswordField('pwd2', 'Passwort wiederholen', true,
    groupCss: 'col',
    validator: function (string $input): bool {
        return ($_POST['pwd1'] ?? '') == $input; 
    },
    invalidMsg: 'Die beiden Passwortfelder sind nicht gleich'
));


$sent = $form->wasSent();
if ($sent && $form->isValid()) {
    $name = $form->getFieldContent('name');
    $mail = $form->getFieldContent('mail');
    $rzid = $form->getFieldContent('rzid');
    $pwd = $form->getFieldContent('pwd1');
    $invalid = false;

    // check, if name is already registered
    $res = $DB->query('SELECT UID FROM id_users WHERE name = "'
    . $DB->real_escape_string($name) . '" LIMIT 1')->num_rows;
    if ($res > 0) {
        $invalid = true;
        $WARNMSG[] = 'Anzeigename bereits vergeben';
    }
    // try to authenticate the user, if the RZID was used
    if (!empty($rzid)) {
        // check, if RZID already used
        $res = $DB->query('SELECT UID FROM id_users WHERE RZID = "'
        . $DB->real_escape_string($rzid) . '" LIMIT 1')->num_rows;
        if ($res > 0) {
            $invalid = true;
            $WARNMSG[] = 'Anzeigename bereits vergeben';
        }
        else {
            // try to authenticate the user
            $res = ldapAuthenticate($rzid, $pwd);
            switch ($res) {
                case 1:
                    $invalid = true;
                    $ERRMSG[] = 'Der UniBw LDAP-Server ist z.Z. nicht erreichbar';
                    break;
                case 2:
                    $invalid = true;
                    $WARNMSG[] = 'Die RZ-Kennung konnte nicht gefunden werden';
                    break;
                case 3:
                    $invalid = true;
                    $WARNMSG[] = 'Das eingegebene Passwort passt nicht zu der RZ-Kennung';
                    break;
            }
        }
    }

    // all checks are performed, now comes the registration
    if (!$invalid) {
        $ECHO = '';
        $mailVerification = randomString(20);
        if ($CONFIG['auth']['guest-registration-default-validity'] === NULL)
            $newDate = 'NULL';
        else
            $newDate = (new DateTime())
            ->add($CONFIG['auth']['guest-registration-default-validity'])
            ->format('Y-m-d');
        // Inserts the user into the database
        $DB->query('INSERT INTO id_users (RZID, email, email_verification, valid_until, name, password) VALUES ('
        . (empty($rzid) ? 'NULL' : '"'.$DB->real_escape_string($rzid).'"') . ', "'
        . $DB->real_escape_string($mail) . '", "'
        . $mailVerification . '", "'
        . $newDate . '", "'
        . $DB->real_escape_string($name) . '", "'
        . password_hash($pwd, PASSWORD_DEFAULT) . '")');

        // sends the e-mail verification
        $uid = $DB->insert_id;
        $link = $CONFIG['basepath'] . 'index.php?action=verifyMail&id=' . $uid . '&code=' . $mailVerification;
        $res = sendMail($mail, 'E-Mail Verifikation', '<h1>UniBwM Inventarisierungsdatenbank</h1>'
        . '<p>Bitte klicken Sie auf diesen Link, um ihre E-Mail Adresse zu verifizieren:<br>'
        . '<a href="' . $link . '&accept=1">' . $link . '&accept=1</a>'
        . '</p><p>Wenn Sie sich nicht registriert haben, klicken Sie auf diesen Link, um den '
        . 'Benutzer zu löschen.<br>'
        . '<a href="' . $link . '&accept=0">' . $link . '&accept=0</a>'
        . '</p>');
        if ($res) {
            $SUCCMSG[] = 'Eine Bestätigungsmail wurde an <samp>' . $mail . '</samp> geschickt.';
            return;
        }
        else {
            $DB->query("UPDATE id_users SET email_verification = NULL WHERE UID = $uid LIMIT 1");
            $ERRMSG[] = 'Es konnte keine Bestätigungsmail verschickt werden. Der Benutzer ist nun freigeschaltet.';
            return;
        }
    }
}


$ECHO = '<div class="container border rounded border-primary p-3" style="max-width: 900px;">
<h3 class="mt-0 mb-3 p-0 text-center">Registrierung</h3>
<form action="index.php?action=register" method="post">'
. $form->getFieldGroup('CSRF', $sent)
. '<div class="row mb-3">'
. $form->getFieldGroup('name', $sent)
. $form->getFieldGroup('rzid', $sent)
. '</div>'
. $form->getFieldGroup('mail', $sent)
. '<div class="row mb-3">'
. $form->getFieldGroup('pwd1', $sent)
. $form->getFieldGroup('pwd2', $sent)
. '</div>
<div>
    <button class="btn btn-primary" type="submit">Registrieren</button>
</div>
</form>
</div>';
?>