<?php
/**
 * Klasa Quiz - zarządzanie quizami
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Config.php';

class Quiz {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Pobranie quizu dla lekcji
     */
    public function getQuizByLessonId($lessonId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM quizzes WHERE lesson_id = ?
            ");
            $stmt->execute([$lessonId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Pobranie pytań do quizu
     */
    public function getQuestions($quizId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM questions 
                WHERE quiz_id = ? 
                ORDER BY question_order
            ");
            $stmt->execute([$quizId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Pobranie odpowiedzi do pytania
     */
    public function getAnswers($questionId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM answers 
                WHERE question_id = ? 
                ORDER BY letter
            ");
            $stmt->execute([$questionId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Sprawdzenie odpowiedzi i obliczenie wyniku
     */
    public function calculateScore($quizId, $answers) {
        try {
            $questions = $this->getQuestions($quizId);
            $correct = 0;
            $total = count($questions);

            foreach ($questions as $question) {
                if (!isset($answers[$question['id']])) continue;

                $stmt = $this->db->prepare("
                    SELECT is_correct FROM answers 
                    WHERE id = ? AND question_id = ?
                ");
                $stmt->execute([$answers[$question['id']], $question['id']]);
                $answer = $stmt->fetch();

                if ($answer && $answer['is_correct']) {
                    $correct++;
                }
            }

            $percentage = round(($correct / $total) * 100);
            $passed = $percentage >= PASS_THRESHOLD;

            return [
                'correct' => $correct,
                'total' => $total,
                'percentage' => $percentage,
                'passed' => $passed
            ];
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Pobranie wyniku quizu użytkownika
     */
    public function getUserQuizScore($userId, $lessonId) {
        try {
            $stmt = $this->db->prepare("
                SELECT quiz_score FROM user_progress 
                WHERE user_id = ? AND lesson_id = ?
            ");
            $stmt->execute([$userId, $lessonId]);
            $result = $stmt->fetch();
            return $result ? $result['quiz_score'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>
