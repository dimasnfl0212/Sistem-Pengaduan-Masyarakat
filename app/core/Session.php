<?php
class Session {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function get($key) {
        return $_SESSION[$key] ?? null;
    }
    
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public function destroy() {
        session_destroy();
    }
    
    public function setFlash($message, $type = 'success') {
        $this->set('flash', ['message' => $message, 'type' => $type]);
    }
    
    public function getFlash() {
        $flash = $this->get('flash');
        $this->remove('flash');
        return $flash;
    }
    
    public function isLoggedIn() {
        return $this->get('user_id') !== null;
    }
    
    public function isAdmin() {
        return $this->get('user_role') === 'admin';
    }
}
?>