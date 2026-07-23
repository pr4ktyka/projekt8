<?php
/**
 * Strona quizu - quiz.php
 */

ob_start();

require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/auth/AuthHandler.php';
require_once __DIR__ . '/../src/auth/SessionManager.php';
require_once __DIR__ . '/../src/classes/Lesson.php';
require_once __DIR__ . '/../src/classes/Quiz.php';

AuthHandler::requireLogin();

SessionManager::init();
$userId = SessionManager::getCurrentUserId();

$lessonId = $_GET['lesson'] ?? 1;

$lesson = new Lesson();
$quiz = new Quiz();

$currentLesson = $lesson->getLesson($lessonId);
if (!$currentLesson) {
    header('Location: /');
    exit;
}

$quizData = $quiz->getQuizByLessonId($lessonId);
if (!$quizData) {
    header('Location: /learn.php?lesson=' . $lessonId);
    exit;
}

$questions = $quiz->getQuestions($quizData['id']);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz: <?php echo htmlspecialchars($currentLesson['title']); ?> - orzeszekstudies</title>
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
    <div id="quiz-container" class="quiz-container">
        <h1 style="margin-top: 0;">Quiz: <?php echo htmlspecialchars($currentLesson['title']); ?></h1>
        <p style="color: #666; margin-bottom: 20px;">Odpowiedz na <?php echo count($questions); ?> pytań. Wymagane minimum 70% poprawnych odpowiedzi.</p>

        <form id="quiz-form" method="POST" onsubmit="event.preventDefault(); submitQuiz(); return false;">
            <input type="hidden" name="lesson_id" value="<?php echo $lessonId; ?>">
            <input type="hidden" name="quiz_id" value="<?php echo $quizData['id']; ?>">

            <?php foreach ($questions as $index => $question): ?>
                <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e0e0e0;">
                    <div class="question-counter">Pytanie <?php echo ($index + 1); ?> z <?php echo count($questions); ?></div>
                    <div class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></div>

                    <div class="quiz-options">
                        <?php 
                        $answers = $quiz->getAnswers($question['id']);
                        foreach ($answers as $answer):
                        ?>
                            <label class="quiz-option">
                                <input type="radio" name="question_<?php echo $question['id']; ?>" 
                                       value="<?php echo $answer['id']; ?>" required>
                                <span><?php echo $answer['letter']; ?>) <?php echo htmlspecialchars($answer['answer_text']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                Wyślij odpowiedzi
            </button>
        </form>
    </div>
</main>

<div class="progress-bar-container">
    <div class="progress-bar" style="width: 0%"></div>
</div>

<script src="/js/main.js"></script>

</body>
</html>
