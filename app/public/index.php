<?php
/**
 * Strona główna - index.php
 * Wyświetlanie poziomów kursów i lekcji
 */

ob_start();

require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/config/Database.php';
require_once __DIR__ . '/../src/classes/Lesson.php';
require_once __DIR__ . '/../src/auth/SessionManager.php';

SessionManager::init();

$lesson = new Lesson();
$isAdmin = SessionManager::isAdmin();
$allLessons = $lesson->getAllLessons($isAdmin);

// Pogrupuj lekcje po poziomach
$lessonsByLevel = [];
foreach ($allLessons as $les) {
    $lessonsByLevel[$les['level_id']][] = $les;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>orzeszekstudies - Platforma e-learningowa</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>

<header>
    <nav>
        <a href="/" class="logo">orzeszekstudies</a>
        <div class="nav-buttons">
            <?php if (SessionManager::isLoggedIn()): ?>
                <a href="/profile.php" class="btn">Profil</a>
                <?php if (SessionManager::isAdmin()): ?>
                    <a href="/admin/dashboard.php" class="btn">Panel Admina</a>
                <?php endif; ?>
                <a href="/logout.php" class="btn">Wyloguj</a>
            <?php else: ?>
                <a href="/login.php" class="btn">Logowanie</a>
                <a href="/register.php" class="btn">Rejestracja</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main>
    <div style="text-align: center; margin-bottom: 40px;">
        <h1>Witaj w orzeszekstudies</h1>
        <p style="font-size: 18px; color: #666;">Naucz się programowania krok po kroku</p>
    </div>

    <div style="background-color: white; padding: 30px; border-radius: 8px; margin-bottom: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0;">O platformie</h2>
        <p>orzeszekstudies to interaktywna platforma e-learningowa do nauki programowania. Uczymy się przez praktykę, krok po kroku, od podstaw HTML i CSS, przez JavaScript i PHP, aż do zaawansowanej pracy z bazami danych.</p>
        <p><strong>6 lekcji</strong> • <strong>6 quizów</strong> • <strong>System odznak</strong> • <strong>Śledzenie postępu</strong></p>
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 30px; justify-content: center;">
        <button class="btn btn-primary" onclick="filterLevel('all')">Wszystkie kursy</button>
        <button class="btn btn-secondary" onclick="filterLevel(1)">Podstawowy</button>
        <button class="btn btn-secondary" onclick="filterLevel(2)">Średniozaawansowany</button>
        <button class="btn btn-secondary" onclick="filterLevel(3)">Zaawansowany</button>
    </div>

    <div class="course-grid">
        <?php foreach ($allLessons as $les): ?>
            <div class="course-card" data-level="<?php echo $les['level_id']; ?>">
                <div class="title"><?php echo htmlspecialchars($les['title']); ?></div>
                <div class="meta">
                    <span>📚 <?php echo htmlspecialchars($les['level_name']); ?></span>
                    <span>⏱️ ~30 min</span>
                </div>
                <p><?php 
                    // Usuń h2 header i bierz tylko tekst z paragrafów
                    $cleanContent = preg_replace('/<h[2-3][^>]*>.*?<\/h[2-3]>/is', '', $les['content']);
                    echo substr(strip_tags($cleanContent), 0, 100); 
                    ?>...</p>
                <?php if (SessionManager::isLoggedIn()): ?>
                    <button class="btn btn-primary" onclick="window.location.href='/learn.php?lesson=<?php echo $les['id']; ?>'">
                        Rozpocznij naukę
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary" onclick="window.location.href='/login.php'">
                        Zaloguj się, aby uczyć się
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<div class="progress-bar-container">
    <div class="progress-bar" style="width: 0%"></div>
</div>

<script src="/js/main.js"></script>

</body>
</html>
