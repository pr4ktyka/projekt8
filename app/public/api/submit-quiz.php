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
$csrfToken = $_POST['csrf_token'] ?? '';
$quizId = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
$lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;

if (!SessionManager::validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowy token CSRF']);
    exit;
}

if ($quizId <= 0 || $lessonId <= 0) {
    http_response_code(422);
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
        $qId = intval(substr($key, 9));
        $answerId = intval($value);
        if ($qId > 0 && $answerId > 0) {
            $answers[$qId] = $answerId;
        }
    }
}

if (empty($answers)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Brak odpowiedzi quizowych']);
    exit;
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
