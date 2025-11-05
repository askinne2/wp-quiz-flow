<?php
/**
 * A/B Testing Manager
 *
 * Handles A/B testing for quiz variants
 *
 * @package WpQuizFlow\Testing
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Testing;

/**
 * A/B Test Manager Class
 *
 * Manages quiz variant testing
 *
 * @since 1.0.0
 */
class ABTestManager
{
    /**
     * Assign user to quiz variant
     *
     * @param string $quizId Base quiz ID
     * @param array<string, string> $variants Available variants
     * @return string Selected variant ID
     */
    public function assignVariant(string $quizId, array $variants): string
    {
        if (empty($variants)) {
            return $quizId;
        }
        
        // Use session-based assignment for consistency
        $sessionKey = 'wp_quiz_flow_ab_' . md5($quizId);
        
        if (!isset($_SESSION[$sessionKey])) {
            // Randomly assign to variant
            $variantIds = array_keys($variants);
            $selectedVariant = $variantIds[array_rand($variantIds)];
            $_SESSION[$sessionKey] = $selectedVariant;
        }
        
        return $_SESSION[$sessionKey] ?? $quizId;
    }
    
    /**
     * Get variant performance
     *
     * @param string $quizId Base quiz ID
     * @param array<string, string> $variants Available variants
     * @return array<string, array<string, mixed>> Performance metrics per variant
     */
    public function getVariantPerformance(string $quizId, array $variants): array
    {
        global $wpdb;
        
        $tableName = $wpdb->prefix . 'wp_quiz_flow_sessions';
        $performance = [];
        
        foreach ($variants as $variantId => $variantName) {
            $stats = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT 
                        COUNT(*) as total_sessions,
                        SUM(completed) as completed_sessions,
                        AVG(result_count) as avg_results
                     FROM {$tableName}
                     WHERE quiz_id = %s",
                    $variantId
                ),
                ARRAY_A
            );
            
            $total = (int) ($stats['total_sessions'] ?? 0);
            $completed = (int) ($stats['completed_sessions'] ?? 0);
            
            $performance[$variantId] = [
                'name' => $variantName,
                'total_sessions' => $total,
                'completed_sessions' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                'avg_results' => round((float) ($stats['avg_results'] ?? 0), 2)
            ];
        }
        
        return $performance;
    }
}

