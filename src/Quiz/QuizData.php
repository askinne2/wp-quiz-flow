<?php
/**
 * Quiz Data Loader
 *
 * Loads quiz structure from JSON files or database
 *
 * @package WpQuizFlow\Quiz
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Quiz;

/**
 * Quiz Data Class
 *
 * Handles loading and parsing quiz structures
 *
 * @since 1.0.0
 */
class QuizData
{
    /**
     * Load quiz structure by ID
     *
     * @param string $quizId Quiz identifier
     * @return array<string, mixed>|null Quiz structure or null if not found
     */
    public function loadQuiz(string $quizId): ?array
    {
        // First, try to load from database (future: custom post type)
        $dbQuiz = $this->loadQuizFromDatabase($quizId);
        if ($dbQuiz !== null) {
            return $dbQuiz;
        }
        
        // Fall back to JSON file
        return $this->loadQuizFromJson($quizId);
    }
    
    /**
     * Load quiz from database
     *
     * @param string $quizId Quiz identifier
     * @return array<string, mixed>|null
     */
    private function loadQuizFromDatabase(string $quizId): ?array
    {
        // TODO: Implement database storage (custom post type)
        // For now, return null to use JSON fallback
        return null;
    }
    
    /**
     * Load quiz from JSON file
     *
     * @param string $quizId Quiz identifier
     * @return array<string, mixed>|null
     */
    private function loadQuizFromJson(string $quizId): ?array
    {
        $quizFile = WP_QUIZ_FLOW_PLUGIN_DIR . 'assets/json/' . sanitize_file_name($quizId) . '.json';
        
        if (!file_exists($quizFile)) {
            // Try default quiz
            if ($quizId !== 'noma-quiz' && $quizId !== 'default') {
                return $this->loadQuizFromJson('noma-quiz');
            }
            return null;
        }
        
        $json = file_get_contents($quizFile);
        if ($json === false) {
            return null;
        }
        
        $quiz = json_decode($json, true);
        if (!is_array($quiz)) {
            return null;
        }
        
        return $quiz;
    }
    
    /**
     * Get all available quizzes
     *
     * @return array<string, array<string, mixed>> List of available quizzes
     */
    public function getAvailableQuizzes(): array
    {
        $quizzes = [];
        $jsonDir = WP_QUIZ_FLOW_PLUGIN_DIR . 'assets/json/';
        
        if (!is_dir($jsonDir)) {
            return $quizzes;
        }
        
        $files = glob($jsonDir . '*.json');
        if ($files === false) {
            return $quizzes;
        }
        
        foreach ($files as $file) {
            $json = file_get_contents($file);
            if ($json === false) {
                continue;
            }
            
            $quiz = json_decode($json, true);
            if (!is_array($quiz) || !isset($quiz['quiz_id'])) {
                continue;
            }
            
            $quizzes[$quiz['quiz_id']] = [
                'id' => $quiz['quiz_id'],
                'title' => $quiz['title'] ?? 'Untitled Quiz',
                'version' => $quiz['version'] ?? '1.0.0',
                'description' => $quiz['description'] ?? ''
            ];
        }
        
        return $quizzes;
    }
}

