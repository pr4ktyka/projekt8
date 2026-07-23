<?php
/**
 * API: Przesłanie quizu
 * /api/submit-quiz.php
 */

require_once __DIR__ . '/../../src/config/Config.php';
require_once __DIR__ . '/../../src/auth/SessionManager.php';
require_once __DIR__ . '/../../src/classes/User.php';
require_once __DIR__ . '/../../src/classes/Quiz.php';
require_once __DIR__ . '/../../src/classes/Badge.php';

header('Content-Type: application/json');

SessionManager::init();

if (!SessionManager::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Brak autoryzacji']);
    exit;
}

$userId = SessionManager::getCurrentUserId();
$quizId = $_POST['quiz_id'] ?? null;
$lessonId = $_POST['lesson_id'] ?? null;

if (!$quizId || !$lessonId) {
    echo json_encode(['success' => false, 'message' => 'Brakujące dane']);
    exit;
}

$quiz = new Quiz();
$user = new User();
$badge = new Badge();

// Zbierz odpowiedzi
$answers = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'question_') === 0) {
        $qId = substr($key, 9);
        $answers[$qId] = $value;
    }
}

// Oblicz wynik
$result = $quiz->calculateScore($quizId, $answers);

if ($result) {
    // Zapisz postęp
    $user->updateLessonProgress($userId, $lessonId, $result['percentage']);
    
    // Sprawdź odznaki
    $badge->checkAndAwardBadges($userId);
    
    // Sprawdź perfect quiz
    if ($result['percentage'] == 100) {
        $badge->awardPerfectQuizBadge($userId, $result['percentage']);
    }
    
    echo json_encode([
        'success' => true,
        'results' => $result
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Błąd obliczenia wyniku']);
}
?>
