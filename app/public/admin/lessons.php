<?php
/**
 * Panel administracji lekcjami
 */

ob_start();

require_once __DIR__ . '/../../src/config/Config.php';
require_once __DIR__ . '/../../src/auth/AuthHandler.php';
require_once __DIR__ . '/../../src/auth/SessionManager.php';
require_once __DIR__ . '/../../src/classes/Lesson.php';
require_once __DIR__ . '/../../src/classes/Quiz.php';
require_once __DIR__ . '/../../src/config/Database.php';

AuthHandler::requireAdmin();
SessionManager::init();

$lesson = new Lesson();
$quiz = new Quiz();
$db = Database::getInstance()->getConnection();

// Pobranie wszystkich poziomów dla selecta
$stmt = $db->query('SELECT id, name FROM lesson_levels ORDER BY id');
$levels = $stmt->fetchAll();

// Pobranie wszystkich lekcji (draft + published dla admina)
$stmt = $db->query('
    SELECT l.*, ll.name as level_name
    FROM lessons l
    JOIN lesson_levels ll ON l.level_id = ll.id
    ORDER BY l.level_id, l.order_in_level
');
$allLessons = $stmt->fetchAll();

$message = '';
$messageType = '';

// Sprawdzenie czy edytujemy lekcję
$editingLesson = null;
$editingQuiz = null;
$quizQuestions = [];

if (isset($_GET['edit'])) {
    $editLessonId = intval($_GET['edit']);
    foreach ($allLessons as $les) {
        if ($les['id'] == $editLessonId) {
            $editingLesson = $les;
            break;
        }
    }
    // Pobranie quizu dla lekcji
    if ($editingLesson) {
        $editingQuiz = $quiz->getQuizByLessonId($editingLesson['id']);
        if ($editingQuiz) {
            $quizQuestions = $quiz->getQuestions($editingQuiz['id']);
        }
    }
}

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
        } elseif ($_POST['action'] === 'edit') {
            $lessonId = $_POST['lesson_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $levelId = $_POST['level_id'] ?? 0;
            $content = $_POST['content'] ?? '';

            if ($lessonId && $title && $levelId && $content) {
                if ($lesson->updateLesson($lessonId, $title, $content, $levelId)) {
                    $message = 'Lekcja została zaktualizowana!';
                    $messageType = 'success';
                    header('refresh:2');
                } else {
                    $message = 'Błąd podczas edycji lekcji';
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
        } elseif ($_POST['action'] === 'add_question') {
            $quizId = $_POST['quiz_id'] ?? 0;
            $questionText = $_POST['question_text'] ?? '';
            $answers = $_POST['answers'] ?? [];
            $correctAnswer = $_POST['correct_answer'] ?? 0;

            if ($quizId && $questionText && count($answers) > 0 && $correctAnswer > 0) {
                try {
                    // Pobranie maksymalnego order
                    $stmt = $db->prepare('SELECT MAX(question_order) as max_order FROM questions WHERE quiz_id = ?');
                    $stmt->execute([$quizId]);
                    $result = $stmt->fetch();
                    $orderNum = ($result['max_order'] ?? 0) + 1;

                    // Wstaw pytanie
                    $stmt = $db->prepare('INSERT INTO questions (quiz_id, question_text, question_order) VALUES (?, ?, ?)');
                    $stmt->execute([$quizId, $questionText, $orderNum]);
                    $questionId = $db->lastInsertId();

                    // Wstaw odpowiedzi
                    $letters = ['A', 'B', 'C', 'D', 'E'];
                    foreach ($answers as $idx => $answer) {
                        if (trim($answer)) {
                            $isCorrect = ($idx + 1) == $correctAnswer ? 1 : 0;
                            $stmt = $db->prepare('INSERT INTO answers (question_id, answer_text, letter, is_correct) VALUES (?, ?, ?, ?)');
                            $stmt->execute([$questionId, $answer, $letters[$idx], $isCorrect]);
                        }
                    }

                    $message = 'Pytanie zostało dodane!';
                    $messageType = 'success';
                    header('refresh:2');
                } catch (Exception $e) {
                    $message = 'Błąd podczas dodawania pytania';
                    $messageType = 'error';
                }
            } else {
                $message = 'Wypełnij wszystkie pola pytania';
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'create_quiz') {
            $lessonId = $_POST['lesson_id'] ?? 0;
            if ($lessonId) {
                try {
                    $stmt = $db->prepare('INSERT INTO quizzes (lesson_id) VALUES (?)');
                    if ($stmt->execute([$lessonId])) {
                        $message = 'Quiz został utworzony! Możesz teraz dodawać pytania.';
                        $messageType = 'success';
                        header('refresh:2');
                    }
                } catch (Exception $e) {
                    $message = 'Błąd podczas tworzenia quizu';
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'edit_question') {
            $questionId = $_POST['question_id'] ?? 0;
            $questionText = $_POST['question_text'] ?? '';
            $answers = $_POST['answers'] ?? [];
            $correctAnswer = $_POST['correct_answer'] ?? 0;

            if ($questionId && $questionText && count($answers) > 0 && $correctAnswer > 0) {
                try {
                    // Update pytanie
                    $stmt = $db->prepare('UPDATE questions SET question_text = ? WHERE id = ?');
                    $stmt->execute([$questionText, $questionId]);

                    // Update odpowiedzi
                    $letters = ['A', 'B', 'C', 'D', 'E'];
                    foreach ($answers as $idx => $answer) {
                        if (trim($answer)) {
                            $isCorrect = ($idx + 1) == $correctAnswer ? 1 : 0;
                            $stmt = $db->prepare('UPDATE answers SET answer_text = ?, is_correct = ? WHERE question_id = ? AND letter = ?');
                            $stmt->execute([$answer, $isCorrect, $questionId, $letters[$idx]]);
                        }
                    }

                    $message = 'Pytanie zostało zaktualizowane!';
                    $messageType = 'success';
                    header('refresh:2');
                } catch (Exception $e) {
                    $message = 'Błąd podczas edycji pytania';
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'delete_question') {
            $questionId = $_POST['question_id'] ?? 0;
            if ($questionId) {
                try {
                    // Usuń odpowiedzi
                    $db->prepare('DELETE FROM answers WHERE question_id = ?')->execute([$questionId]);
                    // Usuń pytanie
                    $stmt = $db->prepare('DELETE FROM questions WHERE id = ?');
                    if ($stmt->execute([$questionId])) {
                        $message = 'Pytanie zostało usunięte!';
                        $messageType = 'success';
                        header('refresh:2');
                    }
                } catch (Exception $e) {
                    $message = 'Błąd podczas usuwania pytania';
                    $messageType = 'error';
                }
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
    <title>Zarządzanie LEKCJAMI - orzeszekstudies</title>
    <link rel="stylesheet" href="/css/styles.css">
    <style>
    .lessons-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-top: 30px;
    }

    @media (max-width: 1024px) {
        .lessons-grid {
            grid-template-columns: 1fr;
        }
    }

    .lessons-form,
    .lessons-list {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: inherit;
    }

    .form-group textarea {
        min-height: 200px;
        resize: vertical;
    }

    .lesson-item {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        border-left: 4px solid #2d5016;
    }

    .lesson-item.draft {
        border-left-color: #ff9800;
    }

    .lesson-item h4 {
        margin: 0 0 8px 0;
    }

    .lesson-meta {
        font-size: 12px;
        color: #666;
        margin: 8px 0;
    }

    .lesson-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .lesson-actions form {
        display: inline;
    }

    .btn-small {
        display: inline-block;
        padding: 6px 12px;
        font-size: 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        line-height: 1.5;
        box-sizing: border-box;
        vertical-align: middle;
    }

    .btn-publish {
        background: #2d5016;
        color: white;
    }

    .btn-unpublish {
        background: #ff9800;
        color: white;
    }

    .btn-delete {
        background: #f44336;
        color: white;
    }

    .btn-edit {
        background: #2196F3;
        color: white;
    }

    .btn-preview {
        background: #9C27B0;
        color: white;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: bold;
    }

    .status-published {
        background: #4caf50;
        color: white;
    }

    .status-draft {
        background: #ff9800;
        color: white;
    }

    .editing-notice {
        background: #e3f2fd;
        border-left: 4px solid #2196F3;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        overflow-y: auto;
    }

    .modal.active {
        display: block;
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 30px;
        border-radius: 8px;
        width: 90%;
        max-width: 900px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }

    .preview-content {
        padding: 20px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        line-height: 1.6;
    }
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

        <?php if ($editingLesson): ?>
        <div class="editing-notice">
            <strong>✏️ EDYTOWANIE LEKCJI:</strong> <?php echo htmlspecialchars($editingLesson['title']); ?>
            <a href="/admin/lessons.php" style="float: right; color: #2196F3;">Anuluj edycję</a>
        </div>
        <?php endif; ?>

        <div class="lessons-grid">
            <!-- Formularz dodawania/edycji lekcji -->
            <div class="lessons-form">
                <h3><?php echo $editingLesson ? '✏️ Edytuj lekcję' : '➕ Dodaj nową lekcję'; ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $editingLesson ? 'edit' : 'create'; ?>">
                    <?php if ($editingLesson): ?>
                    <input type="hidden" name="lesson_id" value="<?php echo $editingLesson['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title">Tytuł lekcji:</label>
                        <input type="text" id="title" name="title" required placeholder="np. Wprowadzenie do HTML" value="<?php echo htmlspecialchars($editingLesson['title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="level_id">Poziom:</label>
                        <select id="level_id" name="level_id" required>
                            <option value="">-- Wybierz poziom --</option>
                            <?php foreach ($levels as $lv): ?>
                            <option value="<?php echo $lv['id']; ?>" <?php echo ($editingLesson && $editingLesson['level_id'] == $lv['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lv['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="content">Zawartość (HTML):</label>
                        <textarea id="content" name="content" required placeholder="Możesz używać tagów HTML"><?php echo htmlspecialchars($editingLesson['content'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="draft">Wersja robocza (draft)</option>
                            <option value="published">Opublikowana</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <?php echo $editingLesson ? '💾 Aktualizuj lekcję' : '➕ Zapisz lekcję'; ?>
                    </button>

                    <?php if ($editingLesson): ?>
                    <button type="button" class="btn btn-secondary" style="width: 100%; margin-top: 10px;" onclick="openPreview()">
                        👁️ Podgląd jak dla użytkownika
                    </button>
                    <?php if ($editingQuiz): ?>
                    <button type="button" class="btn btn-secondary" style="width: 100%; margin-top: 10px;" onclick="toggleQuizForm()">
                        ❓ Zarządzaj quizem (<?php echo count($quizQuestions); ?> pytań)
                    </button>
                    <?php else: ?>
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="create_quiz">
                        <input type="hidden" name="lesson_id" value="<?php echo $editingLesson['id']; ?>">
                        <button type="submit" class="btn btn-secondary" style="width: 100%; background: #ff9800;">
                            ➕ Utwórz quiz dla tej lekcji
                        </button>
                    </form>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary" style="width: 100%; margin-top: 10px;" onclick="document.location.href='/admin/lessons.php'">
                        ← Wróć do listy
                    </button>
                    <?php endif; ?>
                </form>

                <!-- FORMULARZ QUIZU (tylko przy edycji) -->
                <?php if ($editingLesson && $editingQuiz): ?>
                <div id="quiz-form-container" style="margin-top: 20px; display: none; border-top: 1px solid #ddd; padding-top: 20px;">
                    <h4>❓ Dodaj pytanie do quizu</h4>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_question">
                        <input type="hidden" name="quiz_id" value="<?php echo $editingQuiz['id']; ?>">

                        <div class="form-group">
                            <label for="question_text">Tekst pytania:</label>
                            <textarea id="question_text" name="question_text" required style="min-height: 80px;"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Opcje odpowiedzi:</label>
                            <?php for ($i = 0; $i < 4; $i++): ?>
                            <div style="margin-bottom: 8px;">
                                <input type="text" name="answers[]" placeholder="Odpowiedź <?php echo chr(65 + $i); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            <?php endfor; ?>
                        </div>

                        <div class="form-group">
                            <label for="correct_answer">Prawidłowa odpowiedź:</label>
                            <select id="correct_answer" name="correct_answer" required>
                                <option value="">-- Wybierz --</option>
                                <option value="1">A</option>
                                <option value="2">B</option>
                                <option value="3">C</option>
                                <option value="4">D</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            ➕ Dodaj pytanie
                        </button>
                    </form>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                        <h4>📋 Pytania w tym quizie (<?php echo count($quizQuestions); ?>)</h4>
                        <?php if (empty($quizQuestions)): ?>
                        <p style="color: #666;">Brak pytań. Dodaj pierwsze!</p>
                        <?php else: ?>
                        <?php foreach ($quizQuestions as $q):
                            $answers = $quiz->getAnswers($q['id']);
                            ?>
                        <div style="background: #f9f9f9; padding: 10px; margin: 10px 0; border-radius: 4px;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <p style="margin: 0 0 8px 0;"><strong><?php echo htmlspecialchars($q['question_text']); ?></strong></p>
                                    <ul style="margin: 0; padding-left: 20px; font-size: 12px;">
                                        <?php foreach ($answers as $ans): ?>
                                        <li><?php echo $ans['letter']; ?>) <?php echo htmlspecialchars($ans['answer_text']); ?>
                                            <?php echo $ans['is_correct'] ? ' <span style="color: #4caf50; font-weight: bold;">✓ PRAWIDŁOWA</span>' : ''; ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div style="margin-left: 10px;">
                                    <button type="button" class="btn-small btn-edit" onclick="openEditQuestion(<?php echo $q['id']; ?>, '<?php echo htmlspecialchars(addslashes($q['question_text'])); ?>')" style="display: block; margin-bottom: 5px;">
                                        ✏️ Edytuj
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Usunąć to pytanie?');">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                        <button type="submit" class="btn-small btn-delete">
                                            🗑️ Usuń
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
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
                        <a href="/admin/lessons.php?edit=<?php echo $les['id']; ?>" class="btn-small btn-edit">✏️ Edytuj</a>

                        <button type="button" class="btn-small btn-preview" onclick="openPreviewLesson(<?php echo $les['id']; ?>)">
                            👁️ Podgląd
                        </button>
                        <?php if ($les['status'] === 'draft'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="publish">
                            <input type="hidden" name="lesson_id" value="<?php echo $les['id']; ?>">
                            <button type="submit" class="btn-small btn-publish">✓ Opublikuj</button>
                        </form>
                        <?php else: ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="unpublish">
                            <input type="hidden" name="lesson_id" value="<?php echo $les['id']; ?>">
                            <button type="submit" class="btn-small btn-unpublish">✗ Wycofaj</button>
                        </form>
                        <?php endif; ?>

                        <form method="POST" style="display: inline;" onsubmit="return confirm('Czy na pewno usunąć tę lekcję i jej quiz?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="lesson_id" value="<?php echo $les['id']; ?>">
                            <button type="submit" class="btn-small btn-delete">🗑️ Usuń</button>
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

    <!-- MODAL PODGLĄDU -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="previewTitle" style="margin-top: 0;"></h2>
            <div id="previewContent" class="preview-content"></div>
        </div>
    </div>

    <!-- MODAL EDYCJI PYTANIA -->
    <div id="editQuestionModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2 style="margin-top: 0;">✏️ Edytuj pytanie</h2>
            <form method="POST" id="editQuestionForm">
                <input type="hidden" name="action" value="edit_question">
                <input type="hidden" name="question_id" id="editQuestionId">

                <div class="form-group">
                    <label>Tekst pytania:</label>
                    <textarea id="editQuestionText" name="question_text" required style="min-height: 80px;"></textarea>
                </div>

                <div class="form-group">
                    <label>Opcje odpowiedzi:</label>
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <div style="margin-bottom: 8px;">
                        <input type="text" id="editAnswer<?php echo $i; ?>" name="answers[]" placeholder="Odpowiedź <?php echo chr(65 + $i); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="form-group">
                    <label>Prawidłowa odpowiedź:</label>
                    <select id="editCorrectAnswer" name="correct_answer" required>
                        <option value="">-- Wybierz --</option>
                        <option value="1">A</option>
                        <option value="2">B</option>
                        <option value="3">C</option>
                        <option value="4">D</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    💾 Zapisz zmiany
                </button>
                <button type="button" class="btn btn-secondary" style="width: 100%; margin-top: 10px;" onclick="closeEditModal()">
                    Anuluj
                </button>
            </form>
        </div>
    </div>

    <script>
    function openEditQuestion(questionId, questionText) {
        // Pobierz dane pytania z API
        fetch('/api/get-question.php?id=' + questionId)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.question) {
                    document.getElementById('editQuestionId').value = questionId;
                    document.getElementById('editQuestionText').value = data.question.question_text;

                    // Załaduj odpowiedzi
                    for (let i = 0; i < 4; i++) {
                        document.getElementById('editAnswer' + i).value = '';
                    }

                    if (data.answers && data.answers.length > 0) {
                        data.answers.forEach((ans, idx) => {
                            if (idx < 4) {
                                document.getElementById('editAnswer' + idx).value = ans.answer_text;
                                if (ans.is_correct) {
                                    document.getElementById('editCorrectAnswer').value = (idx + 1);
                                }
                            }
                        });
                    }

                    document.getElementById('editQuestionModal').classList.add('active');
                } else {
                    alert('Błąd wczytywania pytania');
                }
            })
            .catch(e => alert('Błąd: ' + e));
    }

    function closeEditModal() {
        document.getElementById('editQuestionModal').classList.remove('active');
    }

    function openPreview() {
        const title = document.getElementById('title').value || 'Podgląd lekcji';
        const content = document.getElementById('content').value;

        document.getElementById('previewTitle').textContent = title;
        document.getElementById('previewContent').innerHTML = content;
        document.getElementById('previewModal').classList.add('active');
    }

    function openPreviewLesson(lessonId) {
        // Załaduj zawartość lekcji z AJAX
        fetch('/api/get-lesson.php?id=' + lessonId)
            .then(r => r.json())
            .then(data => {
                document.getElementById('previewTitle').textContent = data.title || 'Podgląd lekcji';
                document.getElementById('previewContent').innerHTML = data.content || 'Brak zawartości';
                document.getElementById('previewModal').classList.add('active');
            })
            .catch(e => {
                alert('Błąd wczytywania podglądu: ' + e);
            });
    }

    function closeModal() {
        document.getElementById('previewModal').classList.remove('active');
    }

    function toggleQuizForm() {
        const elem = document.getElementById('quiz-form-container');
        if (elem) {
            elem.style.display = elem.style.display === 'none' ? 'block' : 'none';
        }
    }

    window.onclick = function(e) {
        const previewModal = document.getElementById('previewModal');
        const editModal = document.getElementById('editQuestionModal');

        if (e.target === previewModal) {
            closeModal();
        }
        if (e.target === editModal) {
            closeEditModal();
        }
    }
    </script>

</body>

</html>