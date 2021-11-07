<?php
if (!defined('INVDB'))
    die('No access');

class Action {
    public function __construct (
        public string $short,
        public string $long,
    public int $menus = 1, // used as bitfield
        public string $active = 'true',
        public string $inactiveVisible = 'true',
        public array $children = [],
        public bool $csrf = false
    ) {}

    public function isActive (): bool {
        if ($this->active[0] == '!')
            return !call_user_func([__CLASS__, 'cond_'.ltrim($this->active, '!')]);
        return call_user_func([__CLASS__, 'cond_'.$this->active]);
    }
    public function isInactiveVisible (): bool {
        if ($this->inactiveVisible[0] == '!')
            return !call_user_func([__CLASS__, 'cond_'.ltrim($this->inactiveVisible, '!')]);
        return call_user_func([__CLASS__, 'cond_'.$this->inactiveVisible]);
    }
    
    private static function cond_true (): bool {
        return true;
    }
    private static function cond_false (): bool {
        return false;
    }
    private static function cond_isLoggedIn (): bool {
        global $USER;
        return !is_null($USER);
    }
    private static function cond_guestRegistrationAllowed (): bool {
        global $USER, $CONFIG;
        return is_null($USER) && $CONFIG['auth']['allow-guest-registration'];
    }
}
?>