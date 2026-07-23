<?php
/**
 * Klasa Badge - zarządzanie odznakami
 */

require_once __DIR__ . '/../config/Database.php';

class Badge {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Sprawdzenie warunków przyznania odznak
     */
    public function checkAndAwardBadges($userId) {
        try {
            // 1. Pierwsza lekcja
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM user_progress 
                WHERE user_id = ? AND completed_at IS NOT NULL
            ");
            $stmt->execute([$userId]);
            $completed = $stmt->fetch();
            if ($completed['count'] >= 1) {
                $this->awardBadge($userId, 'first_lesson');
            }

            // 2. Poziom podstawowy (lekcje 1-2)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM user_progress up
                JOIN lessons l ON up.lesson_id = l.id
                WHERE up.user_id = ? AND up.completed_at IS NOT NULL 
                AND l.level_id = 1
            ");
            $stmt->execute([$userId]);
            $basic = $stmt->fetch();
            if ($basic['count'] >= 2) {
                $this->awardBadge($userId, 'basic_level_complete');
            }

            // 3. Poziom średniozaawansowany (lekcje 3-4)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM user_progress up
                JOIN lessons l ON up.lesson_id = l.id
                WHERE up.user_id = ? AND up.completed_at IS NOT NULL 
                AND l.level_id = 2
            ");
            $stmt->execute([$userId]);
            $intermediate = $stmt->fetch();
            if ($intermediate['count'] >= 2) {
                $this->awardBadge($userId, 'intermediate_level_complete');
            }

            // 4. Poziom zaawansowany (lekcje 5-6)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM user_progress up
                JOIN lessons l ON up.lesson_id = l.id
                WHERE up.user_id = ? AND up.completed_at IS NOT NULL 
                AND l.level_id = 3
            ");
            $stmt->execute([$userId]);
            $advanced = $stmt->fetch();
            if ($advanced['count'] >= 2) {
                $this->awardBadge($userId, 'advanced_level_complete');
            }

            // 5. Wszystkie kursy (6 lekcji)
            if ($completed['count'] >= 6) {
                $this->awardBadge($userId, 'all_courses_complete');
            }

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Przyznanie odznaki 100%
     */
    public function awardPerfectQuizBadge($userId, $quizScore) {
        if ($quizScore == 100) {
            return $this->awardBadge($userId, 'perfect_quiz');
        }
        return false;
    }

    /**
     * Przyznanie odznaki
     */
    private function awardBadge($userId, $badgeType) {
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

    /**
     * Pobranie wszystkich odznak systemu
     */
    public function getAllBadges() {
        try {
            $stmt = $this->db->query("SELECT * FROM badges ORDER BY id");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
