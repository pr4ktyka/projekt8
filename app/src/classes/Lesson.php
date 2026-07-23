<?php
/**
 * Klasa Lesson - zarządzanie lekcjami
 */

require_once __DIR__ . '/../config/Database.php';

class Lesson {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Pobranie wszystkich lekcji
     */
    public function getAllLessons($isAdmin = false) {
        try {
            $sql = "
                SELECT l.*, ll.name as level_name
                FROM lessons l
                JOIN lesson_levels ll ON l.level_id = ll.id
            ";
            
            if (!$isAdmin) {
                $sql .= " WHERE l.status = 'published'";
            }
            
            $sql .= " ORDER BY l.level_id, l.order_in_level";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Pobranie lekcji po poziomie
     */
    public function getLessonsByLevel($levelId, $isAdmin = false) {
        try {
            $sql = "
                SELECT l.*, ll.name as level_name
                FROM lessons l
                JOIN lesson_levels ll ON l.level_id = ll.id
                WHERE l.level_id = ?
            ";
            
            if (!$isAdmin) {
                $sql .= " AND l.status = 'published'";
            }
            
            $sql .= " ORDER BY l.order_in_level";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$levelId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Pobranie jednej lekcji
     */
    public function getLesson($lessonId) {
        try {
            $stmt = $this->db->prepare("
                SELECT l.*, ll.name as level_name
                FROM lessons l
                JOIN lesson_levels ll ON l.level_id = ll.id
                WHERE l.id = ?
            ");
            $stmt->execute([$lessonId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Pobranie poprzedniej lekcji
     */
    public function getPreviousLesson($currentLessonId) {
        try {
            $current = $this->getLesson($currentLessonId);
            if (!$current || $current['order_in_level'] == 1) return null;

            $stmt = $this->db->prepare("
                SELECT l.* 
                FROM lessons l
                WHERE l.level_id = ? AND l.order_in_level = ?
            ");
            $stmt->execute([$current['level_id'], $current['order_in_level'] - 1]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Pobranie następnej lekcji
     */
    public function getNextLesson($currentLessonId) {
        try {
            $current = $this->getLesson($currentLessonId);
            if (!$current) return null;

            $stmt = $this->db->prepare("
                SELECT l.* 
                FROM lessons l
                WHERE l.level_id = ? AND l.order_in_level = ?
            ");
            $stmt->execute([$current['level_id'], $current['order_in_level'] + 1]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Pobranie statystyk kursów
     */
    public function getCourseStats() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_lessons,
                    COUNT(DISTINCT level_id) as total_levels
                FROM lessons
            ");
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Tworzenie nowej lekcji
     */
    public function createLesson($title, $levelId, $content, $orderInLevel = null, $status = 'draft') {
        try {
            // Jeśli brak orderInLevel, ustaw na max+1 dla danego poziomu
            if ($orderInLevel === null) {
                $stmt = $this->db->prepare("SELECT MAX(order_in_level) as max_order FROM lessons WHERE level_id = ?");
                $stmt->execute([$levelId]);
                $result = $stmt->fetch();
                $orderInLevel = ($result['max_order'] ?? 0) + 1;
            }

            $stmt = $this->db->prepare("
                INSERT INTO lessons (title, level_id, content, order_in_level, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$title, $levelId, $content, $orderInLevel, $status])) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Aktualizacja statusu lekcji
     */
    public function updateStatus($lessonId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE lessons SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $lessonId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Edycja lekcji
     */
    public function updateLesson($lessonId, $title, $content, $levelId = null) {
        try {
            if ($levelId !== null) {
                $stmt = $this->db->prepare("UPDATE lessons SET title = ?, content = ?, level_id = ? WHERE id = ?");
                return $stmt->execute([$title, $content, $levelId, $lessonId]);
            } else {
                $stmt = $this->db->prepare("UPDATE lessons SET title = ?, content = ? WHERE id = ?");
                return $stmt->execute([$title, $content, $lessonId]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Usuwanie lekcji
     */
    public function deleteLesson($lessonId) {
        try {
            // Usuń quiz powiązany
            $this->db->prepare("DELETE FROM quizzes WHERE lesson_id = ?")->execute([$lessonId]);
            // Usuń lekcję
            $stmt = $this->db->prepare("DELETE FROM lessons WHERE id = ?");
            return $stmt->execute([$lessonId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
