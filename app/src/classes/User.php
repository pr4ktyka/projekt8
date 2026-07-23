<?php
/**
 * Klasa User - zarządzanie użytkownikami
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Config.php';

class User {
    private $db;
    private $userId;
    private $email;
    private $role;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Rejestracja nowego użytkownika
     */
    public function register($email, $password) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Nieprawidłowy email'];
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password_hash, role) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$email, $hashedPassword, ROLE_USER]);
            return ['success' => true, 'message' => 'Rejestracja powiodła się'];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Email już istnieje'];
            }
            return ['success' => false, 'message' => 'Błąd rejestracji'];
        }
    }

    /**
     * Logowanie użytkownika
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, password_hash, role 
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $this->userId = $user['id'];
                $this->email = $user['email'];
                $this->role = $user['role'];
                
                // Aktualizuj last_login
                $this->updateLastLogin();
                return ['success' => true, 'user' => $user];
            }
            return ['success' => false, 'message' => 'Nieprawidłowe dane logowania'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Błąd logowania'];
        }
    }

    /**
     * Pobranie profilu użytkownika
     */
    public function getProfile($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.email, u.role, u.created_at,
                       COUNT(DISTINCT up.lesson_id) as lessons_completed,
                       ROUND(AVG(COALESCE(up.quiz_score, 0)), 2) as avg_quiz_score,
                       COUNT(DISTINCT ub.badge_id) as badges_count,
                       ROUND(COUNT(DISTINCT up.lesson_id) / 6 * 100, 2) as completion_percentage
                FROM users u
                LEFT JOIN user_progress up ON u.id = up.user_id AND up.completed_at IS NOT NULL
                LEFT JOIN user_badges ub ON u.id = ub.user_id
                WHERE u.id = ?
                GROUP BY u.id
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Zapisanie postępu lekcji
     */
    public function updateLessonProgress($userId, $lessonId, $quizScore = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_progress (user_id, lesson_id, completed_at, quiz_score)
                VALUES (?, ?, NOW(), ?)
                ON DUPLICATE KEY UPDATE 
                    completed_at = NOW(),
                    quiz_score = ?
            ");
            $stmt->execute([$userId, $lessonId, $quizScore, $quizScore]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Pobranie ulubionych lekcji
     */
    public function getFavorites($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT l.* 
                FROM lessons l
                JOIN user_favorites uf ON l.id = uf.lesson_id
                WHERE uf.user_id = ?
                ORDER BY l.order_in_level
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Oznaczenie/odznaczenie ulubionej lekcji
     */
    public function toggleFavorite($userId, $lessonId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_favorites 
                WHERE user_id = ? AND lesson_id = ?
            ");
            $stmt->execute([$userId, $lessonId]);
            $exists = $stmt->fetch();

            if ($exists) {
                $stmt = $this->db->prepare("
                    DELETE FROM user_favorites 
                    WHERE user_id = ? AND lesson_id = ?
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO user_favorites (user_id, lesson_id) 
                    VALUES (?, ?)
                ");
            }
            $stmt->execute([$userId, $lessonId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Pobranie zdobytych odznak
     */
    public function getBadges($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT b.* 
                FROM badges b
                JOIN user_badges ub ON b.id = ub.badge_id
                WHERE ub.user_id = ?
                ORDER BY ub.earned_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Przyznanie odznaki
     */
    public function awardBadge($userId, $badgeType) {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM badges WHERE badge_type = ?
            ");
            $stmt->execute([$badgeType]);
            $badge = $stmt->fetch();

            if (!$badge) return false;

            $stmt = $this->db->prepare("
                INSERT IGNORE INTO user_badges (user_id, badge_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$userId, $badge['id']]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function updateLastLogin() {
        try {
            $stmt = $this->db->prepare("
                UPDATE users SET last_login = NOW() WHERE id = ?
            ");
            $stmt->execute([$this->userId]);
        } catch (PDOException $e) {
            // Silent fail
        }
    }
}
?>
