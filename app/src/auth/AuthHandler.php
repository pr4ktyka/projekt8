<?php
/**
 * Obsługa autentykacji
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/../classes/User.php';

class AuthHandler {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    /**
     * Rejestracja użytkownika
     */
    public function register($email, $password, $passwordConfirm) {
        $email = trim((string) $email);
        $password = (string) $password;
        $passwordConfirm = (string) $passwordConfirm;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Nieprawidłowy email'];
        }

        if ($password !== $passwordConfirm) {
            return ['success' => false, 'message' => 'Hasła nie są identyczne'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Hasło musi mieć co najmniej 6 znaków'];
        }

        $result = $this->user->register($email, $password);
        return $result;
    }

    /**
     * Logowanie użytkownika
     */
    public function login($email, $password) {
        $email = trim((string) $email);
        $password = (string) $password;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Nieprawidłowy email'];
        }

        $result = $this->user->login($email, $password);
        
        if ($result['success']) {
            SessionManager::create(
                $result['user']['id'],
                $result['user']['email'],
                $result['user']['role']
            );
            SessionManager::regenerateId();
            return ['success' => true, 'message' => 'Logowanie powiodło się'];
        }
        
        return $result;
    }

    /**
     * Wylogowanie użytkownika
     */
    public function logout() {
        SessionManager::destroy();
        return ['success' => true, 'message' => 'Wylogowanie powiodło się'];
    }

    /**
     * Sprawdzenie autoryzacji
     */
    public static function requireLogin() {
        SessionManager::init();
        if (!SessionManager::isLoggedIn() || !SessionManager::isSessionValid()) {
            header('Location: /login.php');
            exit;
        }
    }

    /**
     * Sprawdzenie roli administratora
     */
    public static function requireAdmin() {
        self::requireLogin();
        if (!SessionManager::isAdmin()) {
            header('Location: /');
            exit;
        }
    }
}
?>
