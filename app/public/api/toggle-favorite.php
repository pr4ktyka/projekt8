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
$csrfToken = is_array($data) ? ($data['csrf_token'] ?? '') : '';
$lessonId = is_array($data) ? intval($data['lesson_id'] ?? 0) : 0;

if (!SessionManager::validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowy token CSRF']);
    exit;
}

if ($lessonId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Brakujące dane']);
    exit;
}

$user = new User();
$result = $user->toggleFavorite($userId, $lessonId);

echo json_encode(['success' => $result]);
?>
