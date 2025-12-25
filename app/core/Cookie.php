<?php
class Cookie {
    public static function set($name, $value, $expire = 86400 * 30) {
        setcookie($name, $value, time() + $expire, '/');
    }
    
    public static function get($name) {
        return $_COOKIE[$name] ?? null;
    }
    
    public static function delete($name) {
        setcookie($name, '', time() - 3600, '/');
    }
}
?>