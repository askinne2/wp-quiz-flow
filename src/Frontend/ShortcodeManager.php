<?php
/**
 * Shortcode Manager for wpQuizFlow
 *
 * Handles quiz shortcode registration and rendering
 *
 * @package WpQuizFlow\Frontend
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Frontend;

use WpQuizFlow\Quiz\QuizManager;

/**
 * Shortcode Manager Class
 *
 * @since 1.0.0
 */
class ShortcodeManager
{
    /**
     * Quiz Manager instance
     *
     * @var QuizManager
     */
    private QuizManager $quizManager;
    
    /**
     * Constructor
     *
     * @param QuizManager $quizManager Quiz Manager instance
     * @since 1.0.0
     */
    public function __construct(QuizManager $quizManager)
    {
        $this->quizManager = $quizManager;
    }
    
    /**
     * Register shortcodes
     *
     * @since 1.0.0
     * @return void
     */
    public function registerShortcodes(): void
    {
        // Main quiz shortcode
        add_shortcode('wpQuizFlow', [$this, 'renderQuizShortcode']);
        add_shortcode('wp_quiz_flow', [$this, 'renderQuizShortcode']);
        
        // Legacy shortcodes for backward compatibility (with deprecation notice)
        add_shortcode('wpFieldFlow_quiz', [$this, 'renderQuizShortcodeLegacy']);
        add_shortcode('wp_field_flow_quiz', [$this, 'renderQuizShortcodeLegacy']);
    }
    
    /**
     * Render quiz shortcode
     *
     * @param array<string, string> $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered shortcode HTML
     */
    public function renderQuizShortcode(array $atts = [], ?string $content = null): string
    {
        $atts = shortcode_atts([
            'id' => '',
            'sheet_id' => '',
            'quiz_id' => 'noma-quiz', // Default quiz
            'show_progress' => 'true',
            'show_contact' => 'true',
            'title' => 'Find Resources for Your Situation',
            'contact_number' => '205-555-0100',
            'result_limit' => '12'
        ], $atts, 'wpQuizFlow');
        
        // Use sheet_id if provided, otherwise use id
        $sheetId = !empty($atts['sheet_id']) ? $atts['sheet_id'] : $atts['id'];
        
        // Validate required attributes
        if (empty($sheetId)) {
            return '<div class="wp-quiz-flow-error">Error: Sheet ID is required. Usage: [wpQuizFlow id="2"]</div>';
        }
        
        // Check if wpFieldFlow is available
        if (!class_exists('\WpFieldFlow\Admin\SheetsManager')) {
            return '<div class="wp-quiz-flow-error">Error: wpFieldFlow plugin is required. Please install and activate wpFieldFlow.</div>';
        }
        
        // Get sheet configuration from wpFieldFlow
        $sheetConfig = $this->getSheetConfig(intval($sheetId));
        if (!$sheetConfig) {
            return '<div class="wp-quiz-flow-error">Error: Sheet configuration not found for ID: ' . esc_html($sheetId) . '</div>';
        }
        
        // Generate unique container ID
        $containerId = 'wp-quiz-flow-' . uniqid();
        
        // Enqueue quiz assets
        $this->enqueueQuizAssets($sheetConfig, $atts);
        
        // Prepare data for the container
        $containerData = [
            'sheet_id' => $sheetId,
            'quiz_id' => $atts['quiz_id'],
            'atts' => $atts,
            'type' => 'quiz'
        ];
        
        // Safely encode the container data
        $encodedData = wp_json_encode($containerData);
        if ($encodedData === false) {
            $encodedData = '{}';
        }
        
        // Return quiz container
        $html = sprintf(
            '<div id="%s" class="wp-quiz-flow-container" data-sheet-id="%s" data-config="%s">
                <div class="wp-quiz-flow-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading quiz...</p>
                </div>
                <noscript>
                    <div class="wp-quiz-flow-error">
                        <p>This quiz requires JavaScript to be enabled. Please enable JavaScript in your browser.</p>
                    </div>
                </noscript>
            </div>',
            esc_attr($containerId),
            esc_attr($sheetId),
            esc_attr($encodedData)
        );
        
        return $html;
    }
    
    /**
     * Render legacy quiz shortcode (with deprecation notice)
     *
     * @param array<string, string> $atts Shortcode attributes
     * @param string|null $content Shortcode content
     * @return string Rendered shortcode HTML
     */
    public function renderQuizShortcodeLegacy(array $atts = [], ?string $content = null): string
    {
        // Log deprecation notice
        if (function_exists('\WpFieldFlow\Core\Debug::log')) {
            \WpFieldFlow\Core\Debug::log(
                'Deprecated shortcode used: wpFieldFlow_quiz. Use [wpQuizFlow] instead.',
                'warning',
                'wpQuizFlow'
            );
        }
        
        // Show admin notice for deprecation
        if (current_user_can('manage_options')) {
            $deprecationNotice = '<div class="notice notice-warning inline" style="margin: 10px 0;"><p>';
            $deprecationNotice .= '<strong>Deprecated Shortcode:</strong> ';
            $deprecationNotice .= 'The shortcode <code>[wpFieldFlow_quiz]</code> is deprecated. ';
            $deprecationNotice .= 'Please use <code>[wpQuizFlow]</code> instead.';
            $deprecationNotice .= '</p></div>';
            
            // Only show in admin or if user is admin
            if (is_admin() || current_user_can('manage_options')) {
                echo wp_kses_post($deprecationNotice);
            }
        }
        
        // Render using new shortcode handler
        return $this->renderQuizShortcode($atts, $content);
    }
    
    /**
     * Get sheet configuration from wpFieldFlow
     *
     * @param int $sheetId Sheet ID
     * @return array<string, mixed>|null Sheet configuration or null
     */
    private function getSheetConfig(int $sheetId): ?array
    {
        if (!class_exists('\WpFieldFlow\Admin\SheetsManager')) {
            return null;
        }
        
        try {
            $sheetsManager = new \WpFieldFlow\Admin\SheetsManager();
            $config = $sheetsManager->getSheetConfig($sheetId);
            
            // getSheetConfig returns an array, check if it's valid
            if (is_array($config) && !empty($config)) {
                return $config;
            }
        } catch (\Exception $e) {
            if (function_exists('\WpFieldFlow\Core\Debug::log')) {
                \WpFieldFlow\Core\Debug::log(
                    'Error getting sheet config: ' . $e->getMessage(),
                    'error',
                    'wpQuizFlow'
                );
            }
        }
        
        return null;
    }
    
    /**
     * Enqueue quiz assets
     *
     * @param array<string, mixed> $sheetConfig Sheet configuration
     * @param array<string, string> $atts Shortcode attributes
     * @return void
     */
    private function enqueueQuizAssets(array $sheetConfig, array $atts): void
    {
        // Enqueue quiz-specific CSS
        wp_enqueue_style(
            'wp-quiz-flow-frontend',
            WP_QUIZ_FLOW_PLUGIN_URL . 'assets/css/quiz.css',
            [],
            WP_QUIZ_FLOW_VERSION
        );
        
        // Also enqueue wpFieldFlow CSS for ResourceDirectory compatibility
        if (defined('WP_FIELD_FLOW_PLUGIN_URL')) {
            wp_enqueue_style(
                'wp-field-flow-frontend',
                WP_FIELD_FLOW_PLUGIN_URL . 'assets/css/frontend.css',
                [],
                defined('WP_FIELD_FLOW_VERSION') ? WP_FIELD_FLOW_VERSION : '1.0.0'
            );
        }
        
        // Enqueue React and ReactDOM from CDN (if not already loaded)
        if (!wp_script_is('react', 'enqueued')) {
            wp_enqueue_script(
                'react',
                'https://unpkg.com/react@18/umd/react.development.js',
                [],
                '18.0.0',
                true
            );
        }
        
        if (!wp_script_is('react-dom', 'enqueued')) {
            wp_enqueue_script(
                'react-dom',
                'https://unpkg.com/react-dom@18/umd/react-dom.development.js',
                ['react'],
                '18.0.0',
                true
            );
        }
        
        // Enqueue wpFieldFlow components (as dependencies)
        // Note: These should be loaded by wpFieldFlow, but we ensure they're available
        if (defined('WP_FIELD_FLOW_PLUGIN_URL')) {
            wp_enqueue_script(
                'wp-field-flow-loading-spinner',
                WP_FIELD_FLOW_PLUGIN_URL . 'assets/js/components/LoadingSpinner.jsx?v=' . time(),
                ['react', 'react-dom'],
                WP_FIELD_FLOW_VERSION ?? '1.0.0',
                true
            );
            
            wp_enqueue_script(
                'wp-field-flow-resource-card',
                WP_FIELD_FLOW_PLUGIN_URL . 'assets/js/components/ResourceCard.jsx?v=' . time(),
                ['react', 'react-dom'],
                WP_FIELD_FLOW_VERSION ?? '1.0.0',
                true
            );
            
            wp_enqueue_script(
                'wp-field-flow-search-filter',
                WP_FIELD_FLOW_PLUGIN_URL . 'assets/js/components/SearchFilter.jsx?v=' . time(),
                ['react', 'react-dom'],
                WP_FIELD_FLOW_VERSION ?? '1.0.0',
                true
            );
            
            wp_enqueue_script(
                'wp-field-flow-pagination',
                WP_FIELD_FLOW_PLUGIN_URL . 'assets/js/components/Pagination.jsx?v=' . time(),
                ['react', 'react-dom'],
                WP_FIELD_FLOW_VERSION ?? '1.0.0',
                true
            );
            
            wp_enqueue_script(
                'wp-field-flow-directory',
                WP_FIELD_FLOW_PLUGIN_URL . 'assets/js/components/ResourceDirectory.jsx?v=' . time(),
                ['wp-field-flow-loading-spinner', 'wp-field-flow-resource-card', 'wp-field-flow-search-filter', 'wp-field-flow-pagination'],
                WP_FIELD_FLOW_VERSION ?? '1.0.0',
                true
            );
        }
        
        // Enqueue QuizNavigator component (wpQuizFlow specific)
        wp_enqueue_script(
            'wp-quiz-flow-navigator',
            WP_QUIZ_FLOW_PLUGIN_URL . 'assets/js/components/QuizNavigator.jsx?v=' . time(),
            ['wp-field-flow-directory'],
            WP_QUIZ_FLOW_VERSION,
            true
        );
        
        // Main quiz app initialization script
        $cacheKey = time() . '_' . wp_rand();
        wp_enqueue_script(
            'wp-quiz-flow-app',
            WP_QUIZ_FLOW_PLUGIN_URL . 'assets/js/quiz-app.js?v=' . $cacheKey,
            ['wp-quiz-flow-navigator'],
            WP_QUIZ_FLOW_VERSION,
            true
        );
        
        // Get layout configuration from wpFieldFlow
        $layoutConfig = [];
        try {
            if (class_exists('\WpFieldFlow\Admin\SheetsManager') && class_exists('\WpFieldFlow\Admin\LayoutDesigner')) {
                $sheetsManager = new \WpFieldFlow\Admin\SheetsManager();
                $layoutDesigner = new \WpFieldFlow\Admin\LayoutDesigner($sheetsManager);
                $sheetId = intval($sheetConfig['id'] ?? 0);
                $layoutConfig = $layoutDesigner->getLayoutSchema($sheetId);
                
                if (!is_array($layoutConfig)) {
                    $layoutConfig = [];
                }
            }
        } catch (\Exception $e) {
            if (function_exists('\WpFieldFlow\Core\Debug::log')) {
                \WpFieldFlow\Core\Debug::log(
                    'Error loading layout config for quiz: ' . $e->getMessage(),
                    'error',
                    'wpQuizFlow'
                );
            }
        }
        
        // Create nonce
        $nonceAction = 'wp_quiz_flow_frontend';
        $freshNonce = wp_create_nonce($nonceAction);
        
        // Get quiz data
        $quizData = $this->quizManager->getQuizData($atts['quiz_id'] ?? 'noma-quiz');
        
        // Localize script data
        wp_localize_script('wp-quiz-flow-app', 'wpQuizFlowData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('wp/v2/'),
            'nonce' => $freshNonce,
            'nonceAction' => $nonceAction,
            'timestamp' => microtime(true),
            'cacheKey' => $cacheKey,
            'sheetConfig' => $sheetConfig,
            'layoutConfig' => $layoutConfig,
            'quizData' => $quizData,
            'shortcodeAtts' => $atts,
            'strings' => [
                'loading' => __('Loading...', 'wp-quiz-flow'),
                'preparing' => __('Preparing quiz...', 'wp-quiz-flow'),
                'no_results' => __('No resources match your needs', 'wp-quiz-flow'),
                'error_loading' => __('Error loading quiz', 'wp-quiz-flow'),
                'retry' => __('Retry', 'wp-quiz-flow')
            ]
        ]);
    }
}

