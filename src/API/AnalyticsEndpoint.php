<?php
/**
 * Analytics REST API Endpoint
 *
 * Provides REST API endpoints for external analytics integration
 *
 * @package WpQuizFlow\API
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\API;

use WpQuizFlow\Admin\Analytics;
use WpQuizFlow\Tracking\QuizSession;

/**
 * Analytics Endpoint Class
 *
 * Handles REST API endpoints for analytics
 *
 * @since 1.0.0
 */
class AnalyticsEndpoint
{
    /**
     * Analytics instance
     *
     * @var Analytics
     */
    private Analytics $analytics;
    
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
        $this->analytics = new Analytics();
        $this->quizSession = new QuizSession();
    }
    
    /**
     * Register REST API routes
     *
     * @since 1.0.0
     * @return void
     */
    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }
    
    /**
     * Register REST API routes
     *
     * @since 1.0.0
     * @return void
     */
    public function registerRoutes(): void
    {
        register_rest_route('wp-quiz-flow/v1', '/analytics', [
            'methods' => 'GET',
            'callback' => [$this, 'getAnalytics'],
            'permission_callback' => [$this, 'checkPermission'],
            'args' => [
                'quiz_id' => [
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Quiz ID to filter results'
                ],
                'start_date' => [
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Start date (YYYY-MM-DD)'
                ],
                'end_date' => [
                    'type' => 'string',
                    'required' => false,
                    'description' => 'End date (YYYY-MM-DD)'
                ]
            ]
        ]);
    }
    
    /**
     * Check permission for REST API
     *
     * @param \WP_REST_Request $request Request object
     * @return bool True if allowed
     */
    public function checkPermission(\WP_REST_Request $request): bool
    {
        // Allow authenticated users with manage_options capability
        return current_user_can('manage_options');
    }
    
    /**
     * Get analytics data
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function getAnalytics(\WP_REST_Request $request): \WP_REST_Response
    {
        $quizId = $request->get_param('quiz_id');
        $startDate = $request->get_param('start_date');
        $endDate = $request->get_param('end_date');
        
        $dateRange = [];
        if ($startDate) {
            $dateRange['start'] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $dateRange['end'] = $endDate . ' 23:59:59';
        }
        
        if ($quizId) {
            $stats = $this->quizSession->getStatistics($quizId, $dateRange);
        } else {
            $stats = $this->analytics->getDashboardStats($dateRange);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $stats
        ], 200);
    }
}

