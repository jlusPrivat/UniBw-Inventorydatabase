<?php
if (!defined('INVDB'))
    die('No access');


class Inventory {
    public static array $inventories = array();

	/* Should be called once at startup or when an inventory changed */
	public static function reloadInventories (): void {
		global $DB;
		$res = $DB->query('SELECT IID, name, description FROM id_inventories');
		while ($r = $res->fetch_assoc())
			self::$inventories[$r['IID']] = new Inventory($r['IID'], $r['name'], $r['description']);
	}


    private function __construct (private int $IID, private string $name, private string $description) {}
}
?>