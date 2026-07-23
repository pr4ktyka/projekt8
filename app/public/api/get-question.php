<?php
/**
 * API: Pobranie pytania do edycji
 * GET /api/get-question.php?id=1
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../src/config/Config.php';
require_once __DIR__ . '/../../src/classes/Quiz.php';

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
