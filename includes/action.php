<?php
if (!defined('INVDB'))
    die('No access');

class Action {
    public function __construct (
        public string $short,
        public string $long,
        public int $menus, // used as bitfield
        public ?closure $active = NULL, // if NULL, return true
        public ?closure $inactiveVisible = NULL, // if NULL, return false
        // a single child results in a non-clickable action / only supported by top menu
        // this also applies when children are not visible
        public array $children = [],
        public bool $csrf = false
    ) {}

    public function isActive (): bool {
        if (is_null($this->active))
            return true;
        return ($this->active)();
    }
    public function isInactiveVisible (): bool {
        if (is_null($this->inactiveVisible))
            return false;
        return ($this->inactiveVisible)();
    }
}
?>