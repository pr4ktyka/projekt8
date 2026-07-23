<?php
/**
 * Konfiguracja aplikacji
 * Stałe i zmienne środowiskowe
 */

define('APP_NAME', 'orzeszekstudies');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('DB_HOST', getenv('MYSQL_HOST') ?: 'mysql');
define('DB_NAME', getenv('MYSQL_DB') ?: 'orzeszekstudies');
define('DB_USER', getenv('MYSQL_USER') ?: 'user');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: 'password');

// Role użytkownika
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');

// Kolory aplikacji
define('PRIMARY_COLOR', '#2d5016'); // Ciemno zielony
define('SECONDARY_COLOR', '#4a7c2a'); // Jasniejszy zielony

// Ustawienia sesji
define('SESSION_TIMEOUT', 3600); // 1 godzina
define('PASS_THRESHOLD', 70); // 70% aby zalicyć quiz

// Ścieżki
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('SRC_PATH', BASE_PATH . '/src');
?>
