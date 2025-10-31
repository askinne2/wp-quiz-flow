<?php
/**
 * Plugin Deactivator
 *
 * Handles plugin deactivation tasks
 *
 * @package WpQuizFlow\Core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Core;

/**
 * Deactivator Class
 *
 * @since 1.0.0
 */
class Deactivator
{
    /**
     * Deactivate plugin
     *
     * @since 1.0.0
     * @return void
     */
    public static function deactivate(): void
    {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log deactivation
        if (function_exists('\WpFieldFlow\Core\Debug::log')) {
            \WpFieldFlow\Core\Debug::log(
                'wpQuizFlow plugin deactivated',
                'info',
                'wpQuizFlow'
            );
        }
    }
}

