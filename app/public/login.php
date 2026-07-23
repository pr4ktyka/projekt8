<?php
/**
 * Strona logowania
 */

ob_start();

require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/auth/AuthHandler.php';
require_once __DIR__ . '/../src/auth/SessionManager.php';

SessionManager::init();

if (SessionManager::isLoggedIn()) {
    header('Location: /');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthHandler();
    $result = $auth->login($_POST['email'], $_POST['password']);
    
    if ($result['success']) {
        header('Location: /');
        exit;
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - orzeszekstudies</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>

<header>
    <nav>
        <a href="/" class="logo">orzeszekstudies</a>
        <div class="nav-buttons">
            <a href="/register.php" class="btn">Rejestracja</a>
        </div>
    </nav>
</header>

<main>
    <div style="max-width: 500px; margin: 60px auto;">
        <h1 style="text-align: center;">Logowanie</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="card">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Hasło:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 20px;">
                Zaloguj się
            </button>

            <p style="text-align: center;">
                Nie masz konta? <a href="/register.php">Zarejestruj się tutaj</a>
            </p>
        </form>
    </div>
</main>

<div class="progress-bar-container">
    <div class="progress-bar" style="width: 0%"></div>
</div>

</body>
</html>
