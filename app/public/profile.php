<?php
/**
 * Profil użytkownika - profile.php
 */

ob_start();

require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/auth/AuthHandler.php';
require_once __DIR__ . '/../src/auth/SessionManager.php';
require_once __DIR__ . '/../src/classes/User.php';

AuthHandler::requireLogin();

SessionManager::init();
$userId = SessionManager::getCurrentUserId();

$user = new User();
$profile = $user->getProfile($userId);
$badges = $user->getBadges($userId);
$favorites = $user->getFavorites($userId);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mój profil - orzeszekstudies</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>

<header>
    <nav>
        <a href="/" class="logo">orzeszekstudies</a>
        <div class="nav-buttons">
            <a href="/" class="btn">Kursy</a>
            <?php if (SessionManager::isAdmin()): ?>
                <a href="/admin/dashboard.php" class="btn">Panel Admina</a>
            <?php endif; ?>
            <a href="/logout.php" class="btn">Wyloguj</a>
        </div>
    </nav>
</header>

<main>
    <h1>Mój profil</h1>

    <div class="profile-container">
        <div class="profile-stats">
            <h3 style="margin-top: 0;">Twoje statystyki</h3>
            
            <div class="stat-item">
                <span class="stat-label">Email:</span>
                <span class="stat-value"><?php echo htmlspecialchars($profile['email']); ?></span>
            </div>

            <div class="stat-item">
                <span class="stat-label">Ukończone lekcje:</span>
                <span class="stat-value"><?php echo $profile['lessons_completed'] ?? 0; ?>/6</span>
            </div>

            <div class="stat-item">
                <span class="stat-label">Średni wynik quizu:</span>
                <span class="stat-value"><?php echo $profile['avg_quiz_score'] ?? 0; ?>%</span>
            </div>

            <div class="stat-item">
                <span class="stat-label">Zdobyte punkty:</span>
                <span class="stat-value"><?php echo ($profile['avg_quiz_score'] ?? 0) * ($profile['lessons_completed'] ?? 0); ?></span>
            </div>

            <div class="stat-item">
                <span class="stat-label">Postęp kursu:</span>
                <span class="stat-value"><?php echo round($profile['completion_percentage'] ?? 0); ?>%</span>
            </div>

            <div style="margin-top: 20px;">
                <div style="background-color: #f5f5f5; border-radius: 5px; overflow: hidden; height: 10px;">
                    <div style="background-color: var(--primary-color); height: 100%; width: <?php echo round($profile['completion_percentage'] ?? 0); ?>%;"></div>
                </div>
            </div>
        </div>

        <div class="profile-stats">
            <h3 style="margin-top: 0;">Zdobyte odznaki</h3>
            
            <?php if (empty($badges)): ?>
                <p style="color: #999;">Nie masz jeszcze żadnych odznak. Rozwiąż quizy, aby je zdobyć!</p>
            <?php else: ?>
                <div class="badges-grid">
                    <?php foreach ($badges as $badge): ?>
                        <div class="badge">
                            <div class="badge-icon">🏆</div>
                            <div class="badge-name"><?php echo htmlspecialchars($badge['name']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($favorites)): ?>
        <div class="card" style="margin-top: 30px;">
            <h3 style="margin-top: 0;">Ulubione lekcje</h3>
            <ul style="list-style: none;">
                <?php foreach ($favorites as $fav): ?>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <a href="/learn.php?lesson=<?php echo $fav['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">
                            ⭐ <?php echo htmlspecialchars($fav['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 40px;">
        <a href="/" class="btn btn-primary">Wróć do kursów</a>
    </div>
</main>

<div class="progress-bar-container">
    <div class="progress-bar" style="width: 100%"></div>
</div>

</body>
</html>
