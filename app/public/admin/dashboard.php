<?php
/**
 * Panel administratora - admin/dashboard.php
 */

ob_start();

require_once __DIR__ . '/../../src/config/Config.php';
require_once __DIR__ . '/../../src/auth/AuthHandler.php';
require_once __DIR__ . '/../../src/auth/SessionManager.php';
require_once __DIR__ . '/../../src/config/Database.php';

AuthHandler::requireAdmin();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

SessionManager::init();

$db = Database::getInstance()->getConnection();

// Pobierz statystyki
$stmt = $db->query("
    SELECT 
        COUNT(DISTINCT u.id) as total_users,
        COUNT(DISTINCT CASE WHEN u.last_login > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN u.id END) as active_users,
        COUNT(DISTINCT CASE WHEN up.completed_at IS NOT NULL THEN up.user_id END) as users_with_progress,
        ROUND(AVG(COALESCE(up.quiz_score, 0)), 2) as avg_quiz_score
    FROM users u
    LEFT JOIN user_progress up ON u.id = up.user_id
");
$stats = $stmt->fetch();

// Pobierz użytkowników
$stmt = $db->query("
    SELECT 
        u.id,
        u.email,
        u.role,
        COUNT(DISTINCT up.lesson_id) as lessons_completed,
        ROUND(AVG(COALESCE(up.quiz_score, 0)), 2) as avg_quiz_score,
        COUNT(DISTINCT ub.badge_id) as badges_count
    FROM users u
    LEFT JOIN user_progress up ON u.id = up.user_id AND up.completed_at IS NOT NULL
    LEFT JOIN user_badges ub ON u.id = ub.user_id
    GROUP BY u.id, u.email, u.role
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administratora - orzeszekstudies</title>
    <?php require __DIR__ . '/../pwa-head.php'; ?>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>

<header>
    <nav>
        <a href="/" class="logo">orzeszekstudies - Panel Admina</a>
        <div class="nav-buttons">
            <a href="/" class="btn">Kursy</a>
            <a href="/profile.php" class="btn">Profil</a>
            <a href="/logout.php" class="btn">Wyloguj</a>
        </div>
    </nav>
</header>

<main>
    <h1>Panel Administratora</h1>

    <div class="admin-stats">
        <div class="stat-card">
            <div class="number"><?php echo $stats['total_users']; ?></div>
            <div class="label">Wszystkich użytkowników</div>
        </div>

        <div class="stat-card">
            <div class="number"><?php echo $stats['active_users']; ?></div>
            <div class="label">Aktywnych (ostatnie 7 dni)</div>
        </div>

        <div class="stat-card">
            <div class="number"><?php echo $stats['users_with_progress']; ?></div>
            <div class="label">Użytkowników z postępem</div>
        </div>

        <div class="stat-card">
            <div class="number"><?php echo $stats['avg_quiz_score']; ?>%</div>
            <div class="label">Średni wynik quizu</div>
        </div>
    </div>

    <div class="card" style="margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin-top: 0;">Lista użytkowników</h3>
            <a href="/admin/lessons.php" class="btn btn-primary" style="padding: 10px 20px;">
                📚 Zarządzaj lekcjami
            </a>
        </div>

        <?php if (empty($users)): ?>
            <p>Brak użytkowników</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Ukończone lekcje</th>
                            <th>Średni wynik</th>
                            <th>Odznaki</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <strong><?php echo ucfirst($user['role']); ?></strong>
                                </td>
                                <td><?php echo $user['lessons_completed']; ?>/6</td>
                                <td><?php echo $user['avg_quiz_score'] ?? 0; ?>%</td>
                                <td><?php echo $user['badges_count']; ?> 🏆</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 40px;">
        <a href="/" class="btn btn-primary">Wróć do strony głównej</a>
    </div>
</main>

<div class="progress-bar-container">
    <div class="progress-bar" style="width: 100%"></div>
</div>

<?php require __DIR__ . '/../pwa-register.php'; ?>

</body>
</html>
