<?php
if (!defined('INVDB'))
    die('No access');


class User {
    private int $uid;
    public ?string $rzid = null;
    public string $name;
    public string $email;
    public ?string $emailVerification;
    public DateTime $validUntil;
    public array $groups = array();
    
    
    /* constructs the instance based on a user id */
    public function __construct (int $uid) {
        global $DB;
        $sanUid = $DB->real_escape_string($uid);
        
        // get the user data
        $res = $DB->query("SELECT RZID, email, valid_until, name, email_verification
            FROM id_users WHERE UID = '$sanUid' LIMIT 1")->fetch_assoc();
        if (!$res) throw new ErrorException('UID not found');
        
        $this->uid = $uid;
        $this->rzid = $res['RZID'];
        $this->name = $res['name'];
        $this->email = $res['email'];
        $this->emailVerification = $res['email_verification'];
        $this->validUntil = new DateTime($res['valid_until']);
        
        // get user group data
        $res = $DB->query("SELECT GID FROM id_match_ug WHERE UID = '$sanUid'");
        while ($r = $res->fetch_assoc())
            $this->groups[] = Group::$groups[$r['GID']];
    }



    public function getUid (): int {
        return $this->uid;
    }



    /* Returns true, if user is in any administrative group */
    public function isAdmin (): bool {
        foreach ($this->groups as $group) {
            if ($group->isAdmin())
                return true;
        }
        return false;
    }



    /* Checks, whether a permission for this user exists in any of the groups related to
    either a specific institute or, if set to NULL, any institute */
    public function hasPermission (string $descriptor, ?Inventory $inst): bool {
        foreach ($this->groups as $group) {
            if ($group->hasPermission($descriptor, $inst))
                return true;
        }
        return false;
    }



    /* Returns true, if the users validity is within the range */
    public function isValid (): bool {
        return $this->validUntil >= new DateTime();
    }



    /* Checks, if the user is still associated with any relevant items like lendings.
    Any user, which is still related to any of those items should not be removed.
    Returns true, if user is safely deleteable. Otherwise do not delete.
    */
    public function isDeleteable (): bool {
        return true;
    }



    /* Deletes a user from the database and associated data fields. No prior
    checking is performed in this function. */
    public function delete (): void {
        global $DB;
        $DB->query('DELETE FROM id_users WHERE UID = "' . $this->uid . '" LIMIT 1');
    }
    
    

    /* constructs the instance based on a formatted user id ("U[1-9]{1-10}") or RZ-ID
    and performs the authentication. If no matching user is found or the authentication
    failed, null will be returned.
    If LDAP auth is enabled and a RZID is associated with the user, will this
    take precedence. In case the LDAP server is unavailable or the RZID could not
    have been located, a respective message will be shown and the local
    fallback password database is then used. */
    public static function getAuthedUser (string $id, string $pwd): ?User {
        global $DB, $CONFIG;
        
        // identify if UID or RZID and get hashed non-ldap password
        $matches;
        if (preg_match('/^U([0-9]{1,10})$/i', $id, $matches)) {
            $usr = $DB->query('SELECT UID, RZID, valid_until, password FROM id_users
                              WHERE UID = "' . $matches[1] . '"')->fetch_assoc();
        }
        else {
            $sanId = $DB->real_escape_string($id);
            $usr = $DB->query("SELECT UID, RZID, valid_until, password FROM id_users
                              WHERE RZID = \"$sanId\"")->fetch_assoc();
        }
        
        // abort, if no user was found
        if (!$usr) return null;
        // abort, if validity exceeded
        if (strtotime($usr['valid_until']) < strtotime('now')) return null;
        
        // try the LDAP login
        if ($CONFIG['auth']['use-ldap-for-login'] && !is_null($usr['RZID'])) {
            $ldap = ldapAuthenticate($usr['RZID'], $pwd);
            // auth successfull
            if ($ldap === 0) {
                // update local password
                $DB->query('UPDATE id_users SET password = "'
                . password_hash($pwd, PASSWORD_DEFAULT) . '" WHERE UID = "'
                . $usr['UID'] . '"');
                // return the user
                return new User($usr['UID']);
            }
        }
        
        // usage of the local fallback password database is neccessary
        if (!isset($ldap) || ($ldap == 1 || $ldap == 2)) {
            // log the warning
            if (isset($ldap))
                lg(0, 'Using fallback local password database ('
                   . ($ldap == 1 ? 'LDAP server unavailable'
                      : 'User with RZID ' . $usr['RZID'] . ' not found in LDAP')
                   . ')');
            
            // perform the password check
            if (password_verify($pwd, $usr['password']))
                return new User($usr['UID']);
        }
        
        // authentication failed
        return null;
    }
}

?>