<?php
/**
 * API: Pobranie zawartości lekcji (dla podglądu)
 * GET /api/get-lesson.php?id=1
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../src/config/Config.php';
require_once __DIR__ . '/../../src/classes/Lesson.php';

$lessonId = $_GET['id'] ?? 0;

if (!$lessonId) {
    echo json_encode(['error' => 'Brakuje ID lekcji']);
    exit;
}

try {
    $lesson = new Lesson();
    $lessonData = $lesson->getLesson($lessonId);
    
    if ($lessonData) {
        echo json_encode([
            'success' => true,
            'id' => $lessonData['id'],
            'title' => $lessonData['title'],
            'content' => $lessonData['content'],
            'level_name' => $lessonData['level_name']
        ]);
    } else {
        echo json_encode(['error' => 'Lekcja nie znaleziona']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Błąd serwera']);
}
?>
