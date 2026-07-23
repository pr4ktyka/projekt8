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
    public function getAllLessons() {
        try {
            $stmt = $this->db->query("
                SELECT l.*, ll.name as level_name
                FROM lessons l
                JOIN lesson_levels ll ON l.level_id = ll.id
                ORDER BY l.level_id, l.order_in_level
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Pobranie lekcji po poziomie
     */
    public function getLessonsByLevel($levelId) {
        try {
            $stmt = $this->db->prepare("
                SELECT l.*, ll.name as level_name
                FROM lessons l
                JOIN lesson_levels ll ON l.level_id = ll.id
                WHERE l.level_id = ?
                ORDER BY l.order_in_level
            ");
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
}
?>
