<?php
if (!defined('INVDB'))
    die('No access');


class Group {
	public static array $groups = array();
	public static function reloadGroups (): void {
		global $DB;
		$res = $DB->query('SELECT GID, name, admin FROM id_groups');
		while ($r = $res->fetch_assoc())
			$groups[$r['GID']] = new Group($r['GID'], $r['name'], $r['admin']);
	}
	
	public function __construct (private int $GID, private string $name, private bool $admin) {
	}
	
	public function getGid (): int {return $this->GID;}
	public function getName (): int {return $this->name;}
	public function isAdmin (): int {return $this->admin;}
}
?>