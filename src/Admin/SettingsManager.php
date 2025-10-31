<?php
/**
 * Settings Manager for wpQuizFlow
 *
 * Handles quiz settings, defaults, and configuration
 * Reuses wpFieldFlow's Debug system for logging
 *
 * @package WpQuizFlow\Admin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Admin;

/**
 * Settings Manager Class
 *
 * @since 1.0.0
 */
final class SettingsManager
{
    /**
     * Default settings
     *
     * @var array<string, mixed>
     */
    private const DEFAULT_SETTINGS = [
        'default_contact_number' => '205-555-0100',
        'default_result_limit' => 12,
        'show_progress_default' => true,
        'show_contact_default' => true,
        'default_quiz_id' => 'noma-quiz',
        'enable_debug_logging' => false, // Uses wpFieldFlow's debug mode
    ];
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->initializeHooks();
    }
    
    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     * @return void
     */
    private function initializeHooks(): void
    {
        // AJAX handlers
        add_action('wp_ajax_wp_quiz_flow_save_settings', [$this, 'handleSaveSettingsAjax']);
        add_action('wp_ajax_wp_quiz_flow_clear_quiz_cache', [$this, 'handleClearQuizCache']);
    }
    
    /**
     * Get all settings
     *
     * @since 1.0.0
     * @return array<string, mixed> Settings array
     */
    public function getSettings(): array
    {
        $settings = get_option('wp_quiz_flow_settings', []);
        return wp_parse_args($settings, self::DEFAULT_SETTINGS);
    }
    
    /**
     * Get a specific setting
     *
     * @since 1.0.0
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? ($default ?? self::DEFAULT_SETTINGS[$key] ?? null);
    }
    
    /**
     * Save settings
     *
     * @since 1.0.0
     * @param array<string, mixed> $postData POST data
     * @return array<string, mixed> Result array
     */
    public function handleSaveSettings(array $postData): array
    {
        // Log save attempt using wpFieldFlow's Debug
        $this->log(
            'wpQuizFlow settings save attempt',
            'info',
            'wpQuizFlow',
            ['post_keys' => array_keys($postData)]
        );
        
        $sanitized = $this->sanitizeSettings($postData);
        $updated = update_option('wp_quiz_flow_settings', $sanitized);
        
        if ($updated !== false) {
            $this->log(
                'wpQuizFlow settings saved successfully',
                'info',
                'wpQuizFlow',
                ['settings_keys' => array_keys($sanitized)]
            );
            
            return [
                'success' => true,
                'message' => __('Settings saved successfully.', 'wp-quiz-flow')
            ];
        }
        
        return [
            'success' => false,
            'message' => __('Failed to save settings.', 'wp-quiz-flow')
        ];
    }
    
    /**
     * Handle AJAX save settings
     *
     * @since 1.0.0
     * @return void
     */
    public function handleSaveSettingsAjax(): void
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'wp_quiz_flow_settings')) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'wp-quiz-flow')]);
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-quiz-flow')]);
            return;
        }
        
        $result = $this->handleSaveSettings($_POST);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Handle clear quiz cache (future: cache quiz JSON files)
     *
     * @since 1.0.0
     * @return void
     */
    public function handleClearQuizCache(): void
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'wp_quiz_flow_settings')) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'wp-quiz-flow')]);
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-quiz-flow')]);
            return;
        }
        
        // Clear any cached quiz data (future implementation)
        // For now, just log the action
        $this->log(
            'Quiz cache cleared',
            'info',
            'wpQuizFlow'
        );
        
        wp_send_json_success(['message' => __('Quiz cache cleared.', 'wp-quiz-flow')]);
    }
    
    /**
     * Sanitize settings
     *
     * @since 1.0.0
     * @param array<string, mixed> $settings Raw settings
     * @return array<string, mixed> Sanitized settings
     */
    public function sanitizeSettings(array $settings): array
    {
        $sanitized = [];
        
        // Default contact number
        if (isset($settings['default_contact_number'])) {
            $sanitized['default_contact_number'] = sanitize_text_field($settings['default_contact_number']);
        }
        
        // Default result limit
        if (isset($settings['default_result_limit'])) {
            $sanitized['default_result_limit'] = absint($settings['default_result_limit']);
            // Ensure it's between 1 and 100
            $sanitized['default_result_limit'] = max(1, min(100, $sanitized['default_result_limit']));
        }
        
        // Boolean settings
        $sanitized['show_progress_default'] = isset($settings['show_progress_default']) && $settings['show_progress_default'] === '1';
        $sanitized['show_contact_default'] = isset($settings['show_contact_default']) && $settings['show_contact_default'] === '1';
        $sanitized['enable_debug_logging'] = isset($settings['enable_debug_logging']) && $settings['enable_debug_logging'] === '1';
        
        // Default quiz ID
        if (isset($settings['default_quiz_id'])) {
            $sanitized['default_quiz_id'] = sanitize_file_name($settings['default_quiz_id']);
        }
        
        // Merge with defaults to ensure all keys exist
        return wp_parse_args($sanitized, $this->getSettings());
    }
    
    /**
     * Check if wpFieldFlow Debug is enabled
     *
     * @since 1.0.0
     * @return bool True if debug is enabled
     */
    public function isDebugEnabled(): bool
    {
        if (class_exists('\WpFieldFlow\Core\Debug') && method_exists('\WpFieldFlow\Core\Debug', 'isDebugEnabled')) {
            try {
                return \WpFieldFlow\Core\Debug::isDebugEnabled();
            } catch (\Throwable $e) {
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Log using wpFieldFlow's Debug system
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param string $level Log level
     * @param string $component Component name
     * @param array<string, mixed> $context Additional context
     * @return void
     */
    public function log(string $message, string $level = 'info', string $component = 'wpQuizFlow', array $context = []): void
    {
        if (class_exists('\WpFieldFlow\Core\Debug') && method_exists('\WpFieldFlow\Core\Debug', 'log')) {
            try {
                \WpFieldFlow\Core\Debug::log($message, $level, $component, $context);
                return;
            } catch (\Throwable $e) {
                // Fall through to WordPress error_log
            }
        }
        
        // Fallback to WordPress error_log if wpFieldFlow Debug is not available
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("wpQuizFlow [{$level}] {$message}");
        }
    }
}

