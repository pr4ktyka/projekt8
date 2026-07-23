<?php
/**
 * API: Przełączenie ulubionej lekcji
 * /api/toggle-favorite.php
 */

require_once __DIR__ . '/../../src/config/Config.php';
require_once __DIR__ . '/../../src/auth/SessionManager.php';
require_once __DIR__ . '/../../src/classes/User.php';

header('Content-Type: application/json');

SessionManager::init();

if (!SessionManager::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Brak autoryzacji']);
    exit;
}

$userId = SessionManager::getCurrentUserId();
$data = json_decode(file_get_contents('php://input'), true);
$lessonId = $data['lesson_id'] ?? null;

if (!$lessonId) {
    echo json_encode(['success' => false, 'message' => 'Brakujące dane']);
    exit;
}

$user = new User();
$result = $user->toggleFavorite($userId, $lessonId);

echo json_encode(['success' => $result]);
?>
