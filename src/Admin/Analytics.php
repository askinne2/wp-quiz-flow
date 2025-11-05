<?php
/**
 * Analytics Manager for wpQuizFlow
 *
 * Handles quiz analytics and statistics
 *
 * @package WpQuizFlow\Admin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Admin;

use WpQuizFlow\Tracking\QuizSession;

/**
 * Analytics Class
 *
 * Provides analytics data for quizzes
 *
 * @since 1.0.0
 */
class Analytics
{
    /**
     * Quiz Session instance
     *
     * @var QuizSession
     */
    private QuizSession $quizSession;
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->quizSession = new QuizSession();
    }
    
    /**
     * Get dashboard statistics
     *
     * @param array<string> $dateRange Optional date range
     * @return array<string, mixed> Dashboard stats
     */
    public function getDashboardStats(array $dateRange = []): array
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . 'wp_quiz_flow_sessions';
        
        $where = [];
        $whereValues = [];
        
        if (!empty($dateRange['start'])) {
            $where[] = 'created_at >= %s';
            $whereValues[] = $dateRange['start'];
        }
        
        if (!empty($dateRange['end'])) {
            $where[] = 'created_at <= %s';
            $whereValues[] = $dateRange['end'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Overall statistics
        $overallStats = $this->quizSession->getStatistics(null, $dateRange);
        
        // Per-quiz statistics
        $quizStats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT quiz_id, 
                        COUNT(*) as total_sessions,
                        SUM(completed) as completed_sessions,
                        AVG(result_count) as avg_results
                 FROM {$tableName}
                 {$whereClause}
                 GROUP BY quiz_id
                 ORDER BY total_sessions DESC",
                ...$whereValues
            ),
            ARRAY_A
        );
        
        // Drop-off points (questions where users abandon)
        $dropOffPoints = $this->getDropOffPoints($dateRange);
        
        // Popular paths
        $popularPaths = $this->getPopularPaths($dateRange);
        
        return [
            'overall' => $overallStats,
            'by_quiz' => $quizStats ?? [],
            'drop_off_points' => $dropOffPoints,
            'popular_paths' => $popularPaths
        ];
    }
    
    /**
     * Get drop-off points
     *
     * @param array<string> $dateRange Optional date range
     * @return array<string, mixed> Drop-off analysis
     */
    private function getDropOffPoints(array $dateRange = []): array
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . 'wp_quiz_flow_sessions';
        
        $where = [];
        $whereValues = [];
        
        if (!empty($dateRange['start'])) {
            $where[] = 'created_at >= %s';
            $whereValues[] = $dateRange['start'];
        }
        
        if (!empty($dateRange['end'])) {
            $where[] = 'created_at <= %s';
            $whereValues[] = $dateRange['end'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get incomplete sessions
        $incompleteSessions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_path FROM {$tableName}
                 {$whereClause}
                 AND completed = 0
                 AND user_path IS NOT NULL",
                ...$whereValues
            ),
            ARRAY_A
        );
        
        $dropOffMap = [];
        
        foreach ($incompleteSessions as $session) {
            $path = json_decode($session['user_path'] ?? '[]', true);
            if (is_array($path) && !empty($path)) {
                $lastNode = end($path);
                $nodeId = $lastNode['node_id'] ?? '';
                if (!empty($nodeId)) {
                    $dropOffMap[$nodeId] = ($dropOffMap[$nodeId] ?? 0) + 1;
                }
            }
        }
        
        arsort($dropOffMap);
        
        return $dropOffMap;
    }
    
    /**
     * Get popular paths
     *
     * @param array<string> $dateRange Optional date range
     * @return array<string, mixed> Popular paths
     */
    private function getPopularPaths(array $dateRange = []): array
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . 'wp_quiz_flow_sessions';
        
        $where = [];
        $whereValues = [];
        
        if (!empty($dateRange['start'])) {
            $where[] = 'created_at >= %s';
            $whereValues[] = $dateRange['start'];
        }
        
        if (!empty($dateRange['end'])) {
            $where[] = 'created_at <= %s';
            $whereValues[] = $dateRange['end'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get completed sessions
        $completedSessions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_path FROM {$tableName}
                 {$whereClause}
                 AND completed = 1
                 AND user_path IS NOT NULL",
                ...$whereValues
            ),
            ARRAY_A
        );
        
        $pathMap = [];
        
        foreach ($completedSessions as $session) {
            $path = json_decode($session['user_path'] ?? '[]', true);
            if (is_array($path) && !empty($path)) {
                $pathKey = implode(' → ', array_map(function($item) {
                    return $item['option_text'] ?? '';
                }, $path));
                
                if (!empty($pathKey)) {
                    $pathMap[$pathKey] = ($pathMap[$pathKey] ?? 0) + 1;
                }
            }
        }
        
        arsort($pathMap);
        
        return array_slice($pathMap, 0, 10); // Top 10 paths
    }
    
    /**
     * Get quiz performance metrics
     *
     * @param string $quizId Quiz identifier
     * @param array<string> $dateRange Optional date range
     * @return array<string, mixed> Performance metrics
     */
    public function getQuizMetrics(string $quizId, array $dateRange = []): array
    {
        return $this->quizSession->getStatistics($quizId, $dateRange);
    }
}

