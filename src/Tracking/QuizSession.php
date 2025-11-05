<?php
/**
 * Quiz Session Tracking
 *
 * Tracks quiz usage, completion, and user paths
 *
 * @package WpQuizFlow\Tracking
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Tracking;

/**
 * Quiz Session Class
 *
 * Handles quiz session tracking and analytics
 *
 * @since 1.0.0
 */
class QuizSession
{
    /**
     * Database table name
     *
     * @var string
     */
    private const TABLE_NAME = 'wp_quiz_flow_sessions';
    
    /**
     * Initialize tracking
     *
     * @since 1.0.0
     * @return void
     */
    public function register(): void
    {
        // Create database table on activation
        register_activation_hook(WP_QUIZ_FLOW_PLUGIN_FILE, [$this, 'createTable']);
        
        // Register AJAX handlers
        add_action('wp_ajax_wp_quiz_flow_track_session', [$this, 'handleTrackSession']);
        add_action('wp_ajax_nopriv_wp_quiz_flow_track_session', [$this, 'handleTrackSession']);
        
        // Cleanup old sessions
        add_action('wp_quiz_flow_cleanup_sessions', [$this, 'cleanupOldSessions']);
        
        // Schedule cleanup if not already scheduled
        if (!wp_next_scheduled('wp_quiz_flow_cleanup_sessions')) {
            wp_schedule_event(time(), 'daily', 'wp_quiz_flow_cleanup_sessions');
        }
    }
    
    /**
     * Create database table
     *
     * @since 1.0.0
     * @return void
     */
    public function createTable(): void
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . self::TABLE_NAME;
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            quiz_id VARCHAR(100) NOT NULL,
            session_id VARCHAR(64) NOT NULL,
            user_path TEXT,
            collected_tags TEXT,
            taxonomy_filters TEXT,
            completed TINYINT(1) DEFAULT 0,
            result_count INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_quiz_id (quiz_id),
            INDEX idx_session_id (session_id),
            INDEX idx_completed (completed),
            INDEX idx_created_at (created_at)
        ) {$charsetCollate};";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Start a new quiz session
     *
     * @param string $quizId Quiz identifier
     * @return string Session ID
     */
    public function startSession(string $quizId): string
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . self::TABLE_NAME;
        
        // Generate unique session ID
        $sessionId = wp_generate_password(32, false);
        
        // Insert session
        $wpdb->insert(
            $tableName,
            [
                'quiz_id' => $quizId,
                'session_id' => $sessionId,
                'completed' => 0,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%d', '%s']
        );
        
        return $sessionId;
    }
    
    /**
     * Track quiz answer
     *
     * @param string $sessionId Session ID
     * @param array<string, mixed> $answerData Answer data
     * @return bool Success
     */
    public function trackAnswer(string $sessionId, array $answerData): bool
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . self::TABLE_NAME;
        
        // Get current session
        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$tableName} WHERE session_id = %s",
                $sessionId
            ),
            ARRAY_A
        );
        
        if (!$session) {
            return false;
        }
        
        // Parse existing path
        $userPath = json_decode($session['user_path'] ?? '[]', true);
        if (!is_array($userPath)) {
            $userPath = [];
        }
        
        // Add new answer to path
        $userPath[] = [
            'node_id' => $answerData['node_id'] ?? '',
            'option_id' => $answerData['option_id'] ?? '',
            'option_text' => $answerData['option_text'] ?? '',
            'timestamp' => current_time('mysql')
        ];
        
        // Parse existing tags
        $collectedTags = json_decode($session['collected_tags'] ?? '[]', true);
        if (!is_array($collectedTags)) {
            $collectedTags = [];
        }
        
        // Add new tags
        if (isset($answerData['tags']) && is_array($answerData['tags'])) {
            $collectedTags = array_merge($collectedTags, $answerData['tags']);
            $collectedTags = array_unique($collectedTags);
        }
        
        // Update session
        $result = $wpdb->update(
            $tableName,
            [
                'user_path' => wp_json_encode($userPath),
                'collected_tags' => wp_json_encode($collectedTags)
            ],
            ['session_id' => $sessionId],
            ['%s', '%s'],
            ['%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Complete quiz session
     *
     * @param string $sessionId Session ID
     * @param int $resultCount Number of results shown
     * @param array<string, array<string>> $taxonomyFilters Taxonomy filters used
     * @return bool Success
     */
    public function completeSession(string $sessionId, int $resultCount = 0, array $taxonomyFilters = []): bool
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . self::TABLE_NAME;
        
        $result = $wpdb->update(
            $tableName,
            [
                'completed' => 1,
                'result_count' => $resultCount,
                'taxonomy_filters' => wp_json_encode($taxonomyFilters)
            ],
            ['session_id' => $sessionId],
            ['%d', '%d', '%s'],
            ['%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Handle track session AJAX request
     *
     * @since 1.0.0
     * @return void
     */
    public function handleTrackSession(): void
    {
        // Verify nonce
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'wp_quiz_flow_frontend')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }
        
        $action = sanitize_text_field($_POST['track_action'] ?? '');
        
        switch ($action) {
            case 'start':
                $quizId = sanitize_text_field($_POST['quiz_id'] ?? '');
                if (empty($quizId)) {
                    wp_send_json_error(['message' => 'Quiz ID is required']);
                    return;
                }
                
                $sessionId = $this->startSession($quizId);
                wp_send_json_success(['session_id' => $sessionId]);
                break;
                
            case 'answer':
                $sessionId = sanitize_text_field($_POST['session_id'] ?? '');
                if (empty($sessionId)) {
                    wp_send_json_error(['message' => 'Session ID is required']);
                    return;
                }
                
                $answerData = [
                    'node_id' => sanitize_text_field($_POST['node_id'] ?? ''),
                    'option_id' => sanitize_text_field($_POST['option_id'] ?? ''),
                    'option_text' => sanitize_text_field($_POST['option_text'] ?? ''),
                    'tags' => isset($_POST['tags']) && is_array($_POST['tags']) 
                        ? array_map('sanitize_text_field', $_POST['tags']) 
                        : []
                ];
                
                $success = $this->trackAnswer($sessionId, $answerData);
                if ($success) {
                    wp_send_json_success(['message' => 'Answer tracked']);
                } else {
                    wp_send_json_error(['message' => 'Failed to track answer']);
                }
                break;
                
            case 'complete':
                $sessionId = sanitize_text_field($_POST['session_id'] ?? '');
                if (empty($sessionId)) {
                    wp_send_json_error(['message' => 'Session ID is required']);
                    return;
                }
                
                $resultCount = intval($_POST['result_count'] ?? 0);
                $taxonomyFilters = isset($_POST['taxonomy_filters']) && is_array($_POST['taxonomy_filters'])
                    ? $_POST['taxonomy_filters']
                    : [];
                
                // Sanitize taxonomy filters
                $sanitizedFilters = [];
                foreach ($taxonomyFilters as $taxonomy => $terms) {
                    if (is_array($terms)) {
                        $sanitizedFilters[sanitize_key($taxonomy)] = array_map('sanitize_text_field', $terms);
                    }
                }
                
                $success = $this->completeSession($sessionId, $resultCount, $sanitizedFilters);
                if ($success) {
                    wp_send_json_success(['message' => 'Session completed']);
                } else {
                    wp_send_json_error(['message' => 'Failed to complete session']);
                }
                break;
                
            default:
                wp_send_json_error(['message' => 'Invalid action']);
        }
    }
    
    /**
     * Cleanup old sessions
     *
     * @since 1.0.0
     * @return void
     */
    public function cleanupOldSessions(): void
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . self::TABLE_NAME;
        
        // Delete sessions older than 90 days
        $cutoffDate = date('Y-m-d H:i:s', strtotime('-90 days'));
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$tableName} WHERE created_at < %s",
                $cutoffDate
            )
        );
    }
    
    /**
     * Get session statistics
     *
     * @param string|null $quizId Optional quiz ID to filter
     * @param array<string> $dateRange Optional date range
     * @return array<string, mixed> Statistics
     */
    public function getStatistics(?string $quizId = null, array $dateRange = []): array
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . self::TABLE_NAME;
        
        $where = [];
        $whereValues = [];
        
        if ($quizId) {
            $where[] = 'quiz_id = %s';
            $whereValues[] = $quizId;
        }
        
        if (!empty($dateRange['start'])) {
            $where[] = 'created_at >= %s';
            $whereValues[] = $dateRange['start'];
        }
        
        if (!empty($dateRange['end'])) {
            $where[] = 'created_at <= %s';
            $whereValues[] = $dateRange['end'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Total sessions
        $totalSessions = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$tableName} {$whereClause}",
                ...$whereValues
            )
        );
        
        // Completed sessions
        $completedSessions = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$tableName} {$whereClause} AND completed = 1",
                ...$whereValues
            )
        );
        
        // Average result count
        $avgResults = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT AVG(result_count) FROM {$tableName} {$whereClause} AND completed = 1",
                ...$whereValues
            )
        );
        
        return [
            'total_sessions' => (int) $totalSessions,
            'completed_sessions' => (int) $completedSessions,
            'completion_rate' => $totalSessions > 0 
                ? round(($completedSessions / $totalSessions) * 100, 2) 
                : 0,
            'avg_result_count' => round((float) $avgResults, 2)
        ];
    }
}

