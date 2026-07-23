<?php
/**
 * Wylogowanie
 */

require_once __DIR__ . '/../src/auth/AuthHandler.php';
require_once __DIR__ . '/../src/auth/SessionManager.php';

$auth = new AuthHandler();
$auth->logout();

header('Location: /login.php?msg=Wylogowanie+powiodło+się');
exit;
?>
