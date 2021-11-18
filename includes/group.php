<?php
if (!defined('INVDB'))
    die('No access');


class Group {
	public static array $groups = array();

	/* Should be called when group permissions changed or once at startup.
	This function loads all groups with their permissions into $groups */
	public static function reloadGroups (): void {
		global $DB;
		$res = $DB->query('SELECT GID, name, admin FROM id_groups');
		while ($r = $res->fetch_assoc()) {
			// create and save new Group object
			$group = (self::$groups[$r['GID']] = new Group($r['GID'], $r['name'], $r['admin']));
			// load permissions
			$perms = $DB->query('SELECT * FROM id_match_gi WHERE GID = "' . $r['GID'] . '"');
			while ($perm = $perms->fetch_assoc()) {
				$iid = $perm['IID'];
				// remove non-permission related cells
				unset($perm['IID'], $perm['GID']);
				$group->permissions[$iid] = $perm;
			}
		}
	}


	private array $permissions = array();
	
	public function __construct (private int $GID, private string $name, private bool $admin) {
	}
	
	public function getGid (): int {return $this->GID;}
	public function getName (): int {return $this->name;}
	public function isAdmin (): int {return $this->admin;}



	/* Checks, whether a permission for this user exists in any of the groups related to
    either a specific institute or, if set to NULL, any institute */
	public function hasPermission (string $descriptor, ?Inventory $inst): bool {
		if (!$inst) {
			foreach ($this->permissions as $perm) {
				// permission found and granted for any inventory
				if (!($perm[$descriptor] ?? 0))
					return true;
			}
		}
		// inventory and permission found and allowed
		else if (isset($this->permissions[$inst]) && !($this->permissions[$inst][$descriptor] ?? 0))
			return true;
		return false;
	}
}
?>