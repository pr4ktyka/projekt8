<?php
/**
 * Panel administracji lekcjami
 */

ob_start();

require_once __DIR__ . '/../../src/config/Config.php';
require_once __DIR__ . '/../../src/auth/AuthHandler.php';
require_once __DIR__ . '/../../src/auth/SessionManager.php';
require_once __DIR__ . '/../../src/classes/Lesson.php';
require_once __DIR__ . '/../../src/config/Database.php';

AuthHandler::requireAdmin();
SessionManager::init();

$lesson = new Lesson();
$db = Database::getInstance()->getConnection();

// Pobranie wszystkich poziomów dla selecta
$stmt = $db->query("SELECT id, name FROM lesson_levels ORDER BY id");
$levels = $stmt->fetchAll();

// Pobranie wszystkich lekcji (draft + published dla admina)
$stmt = $db->query("
    SELECT l.*, ll.name as level_name
    FROM lessons l
    JOIN lesson_levels ll ON l.level_id = ll.id
    ORDER BY l.level_id, l.order_in_level
");
$allLessons = $stmt->fetchAll();

$message = '';
$messageType = '';

// Obsługa akcji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $title = $_POST['title'] ?? '';
            $levelId = $_POST['level_id'] ?? 0;
            $content = $_POST['content'] ?? '';
            $status = $_POST['status'] ?? 'draft';
            
            if ($title && $levelId && $content) {
                if ($lesson->createLesson($title, $levelId, $content, null, $status)) {
                    $message = 'Lekcja została ' . ($status === 'draft' ? 'zapisana w wersji roboczej' : 'opublikowana') . '!';
                    $messageType = 'success';
                    header('refresh:2');
                } else {
                    $message = 'Błąd podczas tworzenia lekcji';
                    $messageType = 'error';
                }
            } else {
                $message = 'Wypełnij wszystkie pola';
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'publish') {
            $lessonId = $_POST['lesson_id'] ?? 0;
            if ($lesson->updateStatus($lessonId, 'published')) {
                $message = 'Lekcja została opublikowana!';
                $messageType = 'success';
                header('refresh:2');
            }
        } elseif ($_POST['action'] === 'unpublish') {
            $lessonId = $_POST['lesson_id'] ?? 0;
            if ($lesson->updateStatus($lessonId, 'draft')) {
                $message = 'Lekcja została przeniesiona do wersji roboczej';
                $messageType = 'success';
                header('refresh:2');
            }
        } elseif ($_POST['action'] === 'delete') {
            $lessonId = $_POST['lesson_id'] ?? 0;
            if ($lesson->deleteLesson($lessonId)) {
                $message = 'Lekcja została usunięta!';
                $messageType = 'success';
                header('refresh:2');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie lekcjami - orzeszekstudies</title>
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        .lessons-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px; }
        @media (max-width: 1024px) { .lessons-grid { grid-template-columns: 1fr; } }
        .lessons-form, .lessons-list { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
        .form-group textarea { min-height: 200px; resize: vertical; }
        .lesson-item { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #2d5016; }
        .lesson-item.draft { border-left-color: #ff9800; }
        .lesson-item h4 { margin: 0 0 8px 0; }
        .lesson-meta { font-size: 12px; color: #666; margin: 8px 0; }
        .lesson-actions { display: flex; gap: 10px; margin-top: 10px; }
        .lesson-actions form { display: inline; }
        .btn-small { padding: 6px 12px; font-size: 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-publish { background: #2d5016; color: white; }
        .btn-unpublish { background: #ff9800; color: white; }
        .btn-delete { background: #f44336; color: white; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .status-published { background: #4caf50; color: white; }
        .status-draft { background: #ff9800; color: white; }
    </style>
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

<main style="padding: 30px; max-width: 1400px; margin: 0 auto;">
    <h1>Zarządzanie Lekcjami</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 20px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="lessons-grid">
        <!-- Formularz dodawania lekcji -->
        <div class="lessons-form">
            <h3>Dodaj nową lekcję</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="title">Tytuł lekcji:</label>
                    <input type="text" id="title" name="title" required placeholder="np. Wprowadzenie do HTML">
                </div>

                <div class="form-group">
                    <label for="level_id">Poziom:</label>
                    <select id="level_id" name="level_id" required>
                        <option value="">-- Wybierz poziom --</option>
                        <?php foreach ($levels as $lv): ?>
                            <option value="<?php echo $lv['id']; ?>"><?php echo htmlspecialchars($lv['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Zawartość (HTML):</label>
                    <textarea id="content" name="content" required placeholder="Możesz używać tagów HTML"></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status">
                        <option value="draft">Wersja robocza (draft)</option>
                        <option value="published">Opublikowana</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Zapisz lekcję
                </button>
            </form>
        </div>

        <!-- Lista lekcji -->
        <div class="lessons-list">
            <h3>Wszystkie lekcje</h3>
            
            <?php if (empty($allLessons)): ?>
                <p>Brak lekcji. Dodaj pierwszą!</p>
            <?php else: ?>
                <?php foreach ($allLessons as $les): ?>
                    <div class="lesson-item <?php echo $les['status']; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <h4><?php echo htmlspecialchars($les['title']); ?></h4>
                                <div class="lesson-meta">
                                    <strong><?php echo htmlspecialchars($les['level_name']); ?></strong> • 
                                    <span class="status-badge status-<?php echo $les['status']; ?>">
                                        <?php echo $les['status'] === 'published' ? 'Opublikowana' : 'Draft'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="lesson-actions">
                            <?php if ($les['status'] === 'draft'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="publish">
                                    <input type="hidden" name="lesson_id" value="<?php echo $les['id']; ?>">
                                    <button type="submit" class="btn-small btn-publish">Opublikuj</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="unpublish">
                                    <input type="hidden" name="lesson_id" value="<?php echo $les['id']; ?>">
                                    <button type="submit" class="btn-small btn-unpublish">Cofnij publikację</button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" style="display: inline;" onsubmit="return confirm('Czy na pewno usunąć tę lekcję?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="lesson_id" value="<?php echo $les['id']; ?>">
                                <button type="submit" class="btn-small btn-delete">Usuń</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <p style="margin-top: 30px; text-align: center;">
        <a href="/admin/dashboard.php" class="btn">Wróć do panelu głównego</a>
    </p>
</main>

<div class="progress-bar-container">
    <div class="progress-bar" style="width: 0%"></div>
</div>

</body>
</html>
