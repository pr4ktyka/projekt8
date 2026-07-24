<?php
/**
 * API: Pobranie pytania do edycji
 * GET /api/get-question.php?id=1
 */

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../../src/config/Config.php';
require_once __DIR__ . '/../../src/classes/Quiz.php';
require_once __DIR__ . '/../../src/auth/SessionManager.php';

SessionManager::init();

if (!SessionManager::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Brak autoryzacji']);
    exit;
}

$questionId = $_GET['id'] ?? 0;

if (!$questionId) {
    echo json_encode(['error' => 'Brakuje ID pytania']);
    exit;
}

try {
    $quiz = new Quiz();
    $db = Database::getInstance()->getConnection();
    
    // Pobranie pytania
    $stmt = $db->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$questionId]);
    $question = $stmt->fetch();
    
    if (!$question) {
        echo json_encode(['error' => 'Pytanie nie znalezione']);
        exit;
    }
    
    // Pobranie odpowiedzi
    $answers = $quiz->getAnswers($questionId);
    
    echo json_encode([
        'success' => true,
        'question' => $question,
        'answers' => $answers
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Błąd serwera']);
}
?>
