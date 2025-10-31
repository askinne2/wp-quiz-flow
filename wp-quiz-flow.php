<?php
/**
 * Plugin Name: wpQuizFlow
 * Plugin URI: https://21adsmedia.com
 * Description: Decision tree quiz system for filtering wpFieldFlow resources. Guides users to relevant resources through empathetic, Typeform-style questionnaires.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: 21 ads media
 * Author URI: https://21adsmedia.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-quiz-flow
 * Domain Path: /languages
 * Network: true
 *
 * @package WpQuizFlow
 * @author 21 ads media
 * @since 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_QUIZ_FLOW_VERSION', '1.0.0');
define('WP_QUIZ_FLOW_PLUGIN_FILE', __FILE__);
define('WP_QUIZ_FLOW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_QUIZ_FLOW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_QUIZ_FLOW_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once WP_QUIZ_FLOW_PLUGIN_DIR . 'src/autoloader.php';

/**
 * Check if wpFieldFlow is active
 *
 * @since 1.0.0
 * @return bool True if wpFieldFlow is active, false otherwise
 */
function wp_quiz_flow_check_dependency(): bool
{
    // Include WordPress plugin functions if not already loaded
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    // Check if wpFieldFlow plugin is active by basename
    $wp_field_flow_basename = 'wp-sync-sheet/wp-field-flow.php';
    if (is_plugin_active($wp_field_flow_basename)) {
        return true;
    }
    
    // Fallback: Check if wpFieldFlow constant is defined (more reliable during activation)
    if (defined('WP_FIELD_FLOW_VERSION')) {
        return true;
    }
    
    // Fallback: Check if wpFieldFlow function exists (after plugins_loaded)
    if (function_exists('wp_field_flow_init')) {
        return true;
    }
    
    // Fallback: Check if wpFieldFlow class exists
    if (class_exists('\WpFieldFlow\Core\Plugin')) {
        return true;
    }
    
    return false;
}

/**
 * Display admin notice if wpFieldFlow is not active
 *
 * @since 1.0.0
 * @return void
 */
function wp_quiz_flow_dependency_notice(): void
{
    if (!wp_quiz_flow_check_dependency()) {
        printf(
            '<div class="notice notice-error"><p><strong>%s</strong>: %s</p></div>',
            esc_html__('wpQuizFlow Error', 'wp-quiz-flow'),
            esc_html__(
                'This plugin requires wpFieldFlow to be installed and activated. Please install and activate wpFieldFlow first.',
                'wp-quiz-flow'
            )
        );
    }
}
add_action('admin_notices', 'wp_quiz_flow_dependency_notice');

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return void
 */
function wp_quiz_flow_init(): void
{
    // Check dependency before initializing
    if (!wp_quiz_flow_check_dependency()) {
        return;
    }
    
    try {
        global $wp_quiz_flow_plugin;
        $wp_quiz_flow_plugin = WpQuizFlow\Core\Plugin::getInstance();
    } catch (Exception $e) {
        // Log error and show admin notice
        error_log('wpQuizFlow Plugin Error: ' . $e->getMessage());
        
        if (function_exists('\WpFieldFlow\Core\Debug::log')) {
            \WpFieldFlow\Core\Debug::log(
                'wpQuizFlow initialization failed: ' . $e->getMessage(),
                'error',
                'wpQuizFlow'
            );
        }
        
        add_action('admin_notices', function () use ($e) {
            printf(
                '<div class="notice notice-error"><p><strong>%s:</strong> %s</p></div>',
                esc_html__('wpQuizFlow Error', 'wp-quiz-flow'),
                esc_html($e->getMessage())
            );
        });
    }
}

// Initialize plugin after WordPress and wpFieldFlow are loaded
// wpFieldFlow loads at priority 10, so we load at 15
add_action('plugins_loaded', 'wp_quiz_flow_init', 15);

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 * @return void
 */
function wp_quiz_flow_activate(): void
{
    // Include WordPress plugin functions for activation check
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    // Check if wpFieldFlow plugin file exists
    $wp_field_flow_file = WP_PLUGIN_DIR . '/wp-sync-sheet/wp-field-flow.php';
    $wp_field_flow_basename = 'wp-sync-sheet/wp-field-flow.php';
    
    if (!file_exists($wp_field_flow_file)) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            sprintf(
                '<h1>%s</h1><p>%s</p><p><a href="%s">%s</a></p>',
                esc_html__('wpQuizFlow Activation Error', 'wp-quiz-flow'),
                esc_html__(
                    'wpQuizFlow requires wpFieldFlow to be installed. The wpFieldFlow plugin was not found. Please install wpFieldFlow first, then try activating wpQuizFlow again.',
                    'wp-quiz-flow'
                ),
                admin_url('plugins.php'),
                esc_html__('Return to Plugins page', 'wp-quiz-flow')
            ),
            esc_html__('wpQuizFlow Activation Error', 'wp-quiz-flow'),
            ['back_link' => false]
        );
        return;
    }
    
    // Check if wpFieldFlow is active
    if (!is_plugin_active($wp_field_flow_basename) && !defined('WP_FIELD_FLOW_VERSION')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            sprintf(
                '<h1>%s</h1><p>%s</p><p><strong>%s:</strong> %s</p><p><a href="%s">%s</a></p>',
                esc_html__('wpQuizFlow Activation Error', 'wp-quiz-flow'),
                esc_html__(
                    'wpQuizFlow requires wpFieldFlow to be activated first. Please activate wpFieldFlow, then try activating wpQuizFlow again.',
                    'wp-quiz-flow'
                ),
                esc_html__('Current Status', 'wp-quiz-flow'),
                esc_html__('wpFieldFlow is installed but not activated.', 'wp-quiz-flow'),
                admin_url('plugins.php'),
                esc_html__('Return to Plugins page', 'wp-quiz-flow')
            ),
            esc_html__('wpQuizFlow Activation Error', 'wp-quiz-flow'),
            ['back_link' => false]
        );
        return;
    }
    
    // Dependency check passed, proceed with activation
    try {
        WpQuizFlow\Core\Activator::activate();
    } catch (Exception $e) {
        wp_die(
            esc_html('Plugin activation failed: ' . $e->getMessage()),
            esc_html__('wpQuizFlow Activation Error', 'wp-quiz-flow'),
            ['back_link' => true]
        );
    }
}
register_activation_hook(__FILE__, 'wp_quiz_flow_activate');

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @return void
 */
function wp_quiz_flow_deactivate(): void
{
    WpQuizFlow\Core\Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'wp_quiz_flow_deactivate');

