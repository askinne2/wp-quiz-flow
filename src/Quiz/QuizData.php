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
            // Validate database quiz
            if ($this->validateQuiz($dbQuiz)) {
                return $dbQuiz;
            }
            // If validation fails, log error but still return (for backward compatibility)
            $this->logValidationErrors($quizId);
        }
        
        // Fall back to JSON file
        $jsonQuiz = $this->loadQuizFromJson($quizId);
        if ($jsonQuiz !== null) {
            // Validate JSON quiz
            if ($this->validateQuiz($jsonQuiz)) {
                return $jsonQuiz;
            }
            // If validation fails, log error but still return (for backward compatibility)
            $this->logValidationErrors($quizId);
        }
        
        return $jsonQuiz;
    }
    
    /**
     * Validate quiz structure
     *
     * @param array<string, mixed> $quiz Quiz structure to validate
     * @return bool True if valid
     */
    private function validateQuiz(array $quiz): bool
    {
        $validator = new QuizValidator();
        $isValid = $validator->validate($quiz);
        
        if (!$isValid) {
            // Log validation errors via Debug system if available
            if (class_exists('\WpFieldFlow\Core\Debug') && method_exists('\WpFieldFlow\Core\Debug', 'log')) {
                try {
                    \WpFieldFlow\Core\Debug::log(
                        'Quiz validation failed: ' . $validator->getErrorMessage(),
                        'warning',
                        'wpQuizFlow'
                    );
                } catch (\Throwable $e) {
                    // Ignore if Debug is not available
                }
            } else {
                // Fallback to error_log
                error_log('wpQuizFlow: Quiz validation failed: ' . $validator->getErrorMessage());
            }
        }
        
        return $isValid;
    }
    
    /**
     * Log validation errors
     *
     * @param string $quizId Quiz identifier
     * @return void
     */
    private function logValidationErrors(string $quizId): void
    {
        // Errors are already logged in validateQuiz
        // This method is for future extensibility
    }
    
    /**
     * Load quiz from database
     *
     * @param string $quizId Quiz identifier
     * @return array<string, mixed>|null
     */
    private function loadQuizFromDatabase(string $quizId): ?array
    {
        // Query for quiz by quiz_id meta field
        $args = [
            'post_type' => 'wp_quiz_flow_quiz',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_quiz_id',
                    'value' => $quizId,
                    'compare' => '='
                ]
            ]
        ];
        
        $query = new \WP_Query($args);
        
        if (!$query->have_posts()) {
            return null;
        }
        
        $post = $query->posts[0];
        
        // Get quiz structure from post meta
        $quizStructure = get_post_meta($post->ID, '_quiz_structure', true);
        
        if (empty($quizStructure)) {
            return null;
        }
        
        // Decode JSON structure
        $quiz = json_decode($quizStructure, true);
        
        if (!is_array($quiz) || json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        // Ensure quiz_id is set
        if (!isset($quiz['quiz_id'])) {
            $quiz['quiz_id'] = $quizId;
        }
        
        // Add metadata from post meta
        $quiz['version'] = get_post_meta($post->ID, '_quiz_version', true) ?: '1.0.0';
        $quiz['target_sheet_id'] = get_post_meta($post->ID, '_target_sheet_id', true);
        
        // Add title from post if not set
        if (!isset($quiz['title'])) {
            $quiz['title'] = $post->post_title;
        }
        
        // Add description from post content if not set
        if (!isset($quiz['description']) && !empty($post->post_content)) {
            $quiz['description'] = $post->post_content;
        }
        
        wp_reset_postdata();
        
        return $quiz;
    }
    
    /**
     * Load quiz from JSON file
     *
     * @param string $quizId Quiz identifier
     * @return array<string, mixed>|null
     */
    private function loadQuizFromJson(string $quizId): ?array
    {
        // Cache JSON quiz files
        $cacheKey = 'wp_quiz_flow_json_quiz_' . md5($quizId);
        $quiz = get_transient($cacheKey);
        
        if ($quiz !== false) {
            return $quiz;
        }
        
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
        
        // Cache for 1 hour (or until file changes)
        $fileModifiedTime = filemtime($quizFile);
        $cacheExpiration = $fileModifiedTime ? HOUR_IN_SECONDS : HOUR_IN_SECONDS;
        set_transient($cacheKey, $quiz, $cacheExpiration);
        
        return $quiz;
    }
    
    /**
     * Get all available quizzes
     * Includes both database quizzes and JSON quizzes
     *
     * @return array<string, array<string, mixed>> List of available quizzes
     */
    public function getAvailableQuizzes(): array
    {
        $quizzes = [];
        
        // Load quizzes from database first
        $args = [
            'post_type' => 'wp_quiz_flow_quiz',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ];
        
        $query = new \WP_Query($args);
        
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $quizId = get_post_meta($post->ID, '_quiz_id', true) ?: sanitize_title($post->post_title);
                
                $quizzes[$quizId] = [
                    'id' => $quizId,
                    'title' => $post->post_title,
                    'version' => get_post_meta($post->ID, '_quiz_version', true) ?: '1.0.0',
                    'description' => $post->post_content ?: '',
                    'source' => 'database',
                    'post_id' => $post->ID
                ];
            }
        }
        
        wp_reset_postdata();
        
        // Load quizzes from JSON files (don't override database quizzes)
        $jsonDir = WP_QUIZ_FLOW_PLUGIN_DIR . 'assets/json/';
        
        if (is_dir($jsonDir)) {
            $files = glob($jsonDir . '*.json');
            if ($files !== false) {
                foreach ($files as $file) {
                    $json = file_get_contents($file);
                    if ($json === false) {
                        continue;
                    }
                    
                    $quiz = json_decode($json, true);
                    if (!is_array($quiz) || !isset($quiz['quiz_id'])) {
                        continue;
                    }
                    
                    $quizId = $quiz['quiz_id'];
                    
                    // Only add if not already in database
                    if (!isset($quizzes[$quizId])) {
                        $quizzes[$quizId] = [
                            'id' => $quizId,
                            'title' => $quiz['title'] ?? 'Untitled Quiz',
                            'version' => $quiz['version'] ?? '1.0.0',
                            'description' => $quiz['description'] ?? '',
                            'source' => 'json'
                        ];
                    }
                }
            }
        }
        
        return $quizzes;
    }
}

