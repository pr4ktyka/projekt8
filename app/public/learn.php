<?php
/**
 * Panel nauki - learn.php
 * Wyświetlanie lekcji i navigacja
 */

ob_start();

require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/auth/AuthHandler.php';
require_once __DIR__ . '/../src/auth/SessionManager.php';
require_once __DIR__ . '/../src/classes/Lesson.php';
require_once __DIR__ . '/../src/classes/User.php';

AuthHandler::requireLogin();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

SessionManager::init();
$userId = SessionManager::getCurrentUserId();

$lesson = new Lesson();
$user = new User();

$searchQuery = trim($_GET['q'] ?? '');
$levelFilter = isset($_GET['level']) ? intval($_GET['level']) : 0;
$sortFilter = $_GET['sort'] ?? 'default';
$allowedSorts = ['default', 'title_asc', 'title_desc', 'level_desc'];

if (!in_array($sortFilter, $allowedSorts, true)) {
    $sortFilter = 'default';
}

$lessonId = isset($_GET['lesson']) ? intval($_GET['lesson']) : 1;
$currentLesson = $lesson->getLesson($lessonId);

if (!$currentLesson) {
    header('Location: /');
    exit;
}

$levels = $lesson->getAllLevels();
$allLessons = $lesson->searchLessons($searchQuery, $levelFilter, $sortFilter);

if (empty($allLessons)) {
    $allLessons = $lesson->getAllLessons();
}

$prevLesson = $lesson->getPreviousLesson($lessonId);
$nextLesson = $lesson->getNextLesson($lessonId);
$userProfile = $user->getProfile($userId);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(SessionManager::getCsrfToken()); ?>">
    <title><?php echo htmlspecialchars($currentLesson['title']); ?> - orzeszekstudies</title>
    <?php require __DIR__ . '/pwa-head.php'; ?>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>

<header>
    <nav>
        <a href="/" class="logo">orzeszekstudies</a>
        <div class="nav-buttons">
            <a href="/profile.php" class="btn">Profil</a>
            <a href="/logout.php" class="btn">Wyloguj</a>
        </div>
    </nav>
</header>

<main>
    <div class="learning-container">
        <div class="lesson-sidebar">
            <h3>Lekcje</h3>
            <form method="GET" action="/learn.php" class="lesson-filters">
                <input type="hidden" name="lesson" value="<?php echo intval($lessonId); ?>">

                <div class="form-group">
                    <input
                        type="text"
                        name="q"
                        placeholder="Szukaj lekcji..."
                        value="<?php echo htmlspecialchars($searchQuery); ?>"
                    >
                </div>

                <div class="form-group">
                    <select name="level">
                        <option value="0">Wszystkie poziomy</option>
                        <?php foreach ($levels as $level): ?>
                            <option value="<?php echo intval($level['id']); ?>" <?php echo ($levelFilter === intval($level['id'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($level['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <select name="sort">
                        <option value="default" <?php echo ($sortFilter === 'default') ? 'selected' : ''; ?>>Domyślne sortowanie</option>
                        <option value="title_asc" <?php echo ($sortFilter === 'title_asc') ? 'selected' : ''; ?>>Tytuł A-Z</option>
                        <option value="title_desc" <?php echo ($sortFilter === 'title_desc') ? 'selected' : ''; ?>>Tytuł Z-A</option>
                        <option value="level_desc" <?php echo ($sortFilter === 'level_desc') ? 'selected' : ''; ?>>Poziom: od zaawansowanego</option>
                    </select>
                </div>

                <div class="lesson-filters-actions">
                    <button type="submit" class="btn btn-small btn-primary">Filtruj</button>
                    <a href="/learn.php?lesson=<?php echo intval($lessonId); ?>" class="btn btn-small btn-secondary">Wyczyść</a>
                </div>
            </form>

            <div class="lesson-results-count">
                Znaleziono: <strong><?php echo count($allLessons); ?></strong>
            </div>

            <ul class="lesson-list">
                <?php foreach ($allLessons as $les): ?>
                    <li class="<?php echo ($les['id'] == $lessonId) ? 'active' : ''; ?>"
                        onclick="selectLesson(<?php echo $les['id']; ?>)">
                        <span><?php echo htmlspecialchars(substr($les['title'], 0, 20)); ?></span>
                        <span class="lesson-star" onclick="event.stopPropagation(); toggleFavorite(<?php echo $les['id']; ?>, event.target.parentElement)">★</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="lesson-content">
            <h2><?php echo htmlspecialchars($currentLesson['title']); ?></h2>
            <div style="color: #666; margin-bottom: 20px;">
                Poziom: <strong><?php echo htmlspecialchars($currentLesson['level_name']); ?></strong>
            </div>
            
            <?php echo $currentLesson['content']; ?>

            <div class="lesson-navigation">
                <?php if ($prevLesson): ?>
                    <button class="btn btn-secondary btn-small" onclick="selectLesson(<?php echo $prevLesson['id']; ?>)">
                        ← Poprzednia
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary btn-small" disabled>← Poprzednia</button>
                <?php endif; ?>

                <button class="btn btn-primary btn-small" onclick="window.location.href='/quiz.php?lesson=<?php echo $currentLesson['id']; ?>'">
                    Rozwiąż quiz →
                </button>

                <?php if ($nextLesson): ?>
                    <button class="btn btn-secondary btn-small" onclick="selectLesson(<?php echo $nextLesson['id']; ?>)">
                        Następna →
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary btn-small" disabled>Następna →</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<div class="progress-bar-container">
    <div class="progress-bar" style="width: <?php echo $userProfile['completion_percentage'] ?? 0; ?>%"></div>
</div>

<script src="/js/main.js"></script>
<?php require __DIR__ . '/pwa-register.php'; ?>

</body>
</html>
