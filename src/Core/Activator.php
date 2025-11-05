<?php
/**
 * Plugin Activator
 *
 * Handles plugin activation tasks
 *
 * @package WpQuizFlow\Core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Core;

use WpQuizFlow\Tracking\QuizSession;

/**
 * Activator Class
 *
 * @since 1.0.0
 */
class Activator
{
    /**
     * Activate plugin
     *
     * @since 1.0.0
     * @return void
     */
    public static function activate(): void
    {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        self::setDefaultOptions();
        
        // Create tracking table
        $quizSession = new QuizSession();
        $quizSession->createTable();
        
        // Log activation
        if (function_exists('\WpFieldFlow\Core\Debug::log')) {
            \WpFieldFlow\Core\Debug::log(
                'wpQuizFlow plugin activated',
                'info',
                'wpQuizFlow'
            );
        }
    }
    
    /**
     * Set default plugin options
     *
     * @since 1.0.0
     * @return void
     */
    private static function setDefaultOptions(): void
    {
        $defaults = [
            'wp_quiz_flow_version' => WP_QUIZ_FLOW_VERSION,
            'wp_quiz_flow_activated' => current_time('mysql'),
        ];
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
}

