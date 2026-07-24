<?php
/**
 * Strona rejestracji
 */

ob_start();

require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/auth/AuthHandler.php';
require_once __DIR__ . '/../src/auth/SessionManager.php';

SessionManager::init();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SessionManager::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $result = ['success' => false, 'message' => 'Sesja formularza wygasła. Odśwież stronę i spróbuj ponownie.'];
    } else {
        $auth = new AuthHandler();
        $result = $auth->register($_POST['email'] ?? '', $_POST['password'] ?? '', $_POST['password_confirm'] ?? '');
    }
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
        header('Location: /login.php?msg=Rejestracja+powiodła+się');
        exit;
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - orzeszekstudies</title>
    <?php require __DIR__ . '/pwa-head.php'; ?>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>

<header>
    <nav>
        <a href="/" class="logo">orzeszekstudies</a>
        <div class="nav-buttons">
            <a href="/login.php" class="btn">Logowanie</a>
        </div>
    </nav>
</header>

<main>
    <div style="max-width: 500px; margin: 60px auto;">
        <h1 style="text-align: center;">Rejestracja</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="card">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(SessionManager::getCsrfToken()); ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Hasło:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">Potwierdź hasło:</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 20px;">
                Zarejestruj się
            </button>

            <p style="text-align: center;">
                Masz już konto? <a href="/login.php">Zaloguj się tutaj</a>
            </p>
        </form>
    </div>
</main>

<div class="progress-bar-container">
    <div class="progress-bar" style="width: 0%"></div>
</div>

<?php require __DIR__ . '/pwa-register.php'; ?>

</body>
</html>
