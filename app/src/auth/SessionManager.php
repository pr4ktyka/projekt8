<?php
/**
 * Zarządzanie sesjami
 */

require_once __DIR__ . '/../config/Config.php';

class SessionManager {
    
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            header('Content-Type: text/html; charset=utf-8');
            session_start();
        }
    }

    public static function create($userId, $email, $role) {
        self::init();
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['login_time'] = time();
    }

    public static function isLoggedIn() {
        self::init();
        return isset($_SESSION['user_id']) && isset($_SESSION['email']);
    }

    public static function getCurrentUserId() {
        self::init();
        return $_SESSION['user_id'] ?? null;
    }

    public static function getCurrentEmail() {
        self::init();
        return $_SESSION['email'] ?? null;
    }

    public static function getCurrentRole() {
        self::init();
        return $_SESSION['role'] ?? ROLE_USER;
    }

    public static function isAdmin() {
        self::init();
        return (self::getCurrentRole() === ROLE_ADMIN);
    }

    public static function isSessionValid() {
        self::init();
        if (!isset($_SESSION['login_time'])) {
            return false;
        }
        
        if ((time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
            self::destroy();
            return false;
        }
        
        return true;
    }

    public static function destroy() {
        self::init();
        session_unset();
        session_destroy();
    }

    public static function regenerateId() {
        self::init();
        session_regenerate_id(true);
    }
}
?>
