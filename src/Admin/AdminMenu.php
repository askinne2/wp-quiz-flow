<?php
/**
 * Admin Menu Manager for wpQuizFlow
 *
 * @package WpQuizFlow\Admin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Admin;

/**
 * Admin Menu Class
 *
 * Manages the WordPress admin interface for wpQuizFlow
 * Reuses wpFieldFlow's Debug system
 *
 * @since 1.0.0
 */
final class AdminMenu
{
    /**
     * Menu capability requirement
     *
     * @var string
     */
    private const REQUIRED_CAPABILITY = 'manage_options';
    
    /**
     * Settings manager instance
     *
     * @var SettingsManager
     */
    private SettingsManager $settingsManager;
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settingsManager = new SettingsManager();
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
        add_action('admin_menu', [$this, 'addMenuPages']);
        add_action('admin_init', [$this, 'initializeSettings']);
        add_action('admin_notices', [$this, 'displayAdminNotices']);
        add_filter('plugin_action_links_' . WP_QUIZ_FLOW_PLUGIN_BASENAME, [$this, 'addPluginActionLinks']);
        
        // AJAX handlers for quiz management
        add_action('wp_ajax_wp_quiz_flow_duplicate_quiz', [$this, 'handleDuplicateQuiz']);
        add_action('wp_ajax_wp_quiz_flow_export_quiz', [$this, 'handleExportQuiz']);
        add_action('wp_ajax_wp_quiz_flow_delete_quiz', [$this, 'handleDeleteQuiz']);
    }
    
    /**
     * Add admin menu pages
     *
     * @since 1.0.0
     * @return void
     */
    public function addMenuPages(): void
    {
        // Create our own top-level menu
        $mainPage = add_menu_page(
            __('QuizFlow Dashboard', 'wp-quiz-flow'),
            __('QuizFlow', 'wp-quiz-flow'),
            self::REQUIRED_CAPABILITY,
            'wp-quiz-flow',
            [$this, 'renderDashboardPage'],
            'dashicons-clipboard',
            26
        );
        
        // Dashboard submenu
        $dashboardPage = add_submenu_page(
            'wp-quiz-flow',
            __('QuizFlow Dashboard', 'wp-quiz-flow'),
            __('Dashboard', 'wp-quiz-flow'),
            self::REQUIRED_CAPABILITY,
            'wp-quiz-flow',
            [$this, 'renderDashboardPage']
        );
        
        // Quizzes submenu (future: quiz builder)
        $quizzesPage = add_submenu_page(
            'wp-quiz-flow',
            __('Quizzes', 'wp-quiz-flow'),
            __('Quizzes', 'wp-quiz-flow'),
            self::REQUIRED_CAPABILITY,
            'wp-quiz-flow-quizzes',
            [$this, 'renderQuizzesPage']
        );
        
        // Add New Quiz submenu (links to CPT)
        add_submenu_page(
            'wp-quiz-flow',
            __('Add New Quiz', 'wp-quiz-flow'),
            __('Add New Quiz', 'wp-quiz-flow'),
            self::REQUIRED_CAPABILITY,
            'post-new.php?post_type=wp_quiz_flow_quiz',
            null
        );
        
        // All Quizzes submenu (links to CPT list)
        add_submenu_page(
            'wp-quiz-flow',
            __('All Quizzes', 'wp-quiz-flow'),
            __('All Quizzes', 'wp-quiz-flow'),
            self::REQUIRED_CAPABILITY,
            'edit.php?post_type=wp_quiz_flow_quiz',
            null
        );
        
        // Settings submenu
        $settingsPage = add_submenu_page(
            'wp-quiz-flow',
            __('Quiz Settings', 'wp-quiz-flow'),
            __('Settings', 'wp-quiz-flow'),
            self::REQUIRED_CAPABILITY,
            'wp-quiz-flow-settings',
            [$this, 'renderSettingsPage']
        );
        
        // Usage submenu
        $usagePage = add_submenu_page(
            'wp-quiz-flow',
            __('Usage Guide', 'wp-quiz-flow'),
            __('Usage Guide', 'wp-quiz-flow'),
            self::REQUIRED_CAPABILITY,
            'wp-quiz-flow-usage',
            [$this, 'renderUsagePage']
        );
    }
    
    /**
     * Initialize admin settings
     *
     * @since 1.0.0
     * @return void
     */
    public function initializeSettings(): void
    {
        // Register settings group
        register_setting('wp_quiz_flow_settings', 'wp_quiz_flow_settings', [
            'sanitize_callback' => [$this->settingsManager, 'sanitizeSettings']
        ]);
    }
    
    /**
     * Render dashboard page
     *
     * @since 1.0.0
     * @return void
     */
    public function renderDashboardPage(): void
    {
        if (!current_user_can(self::REQUIRED_CAPABILITY)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-quiz-flow'));
        }
        
        // Check wpFieldFlow dependency
        $wpFieldFlowActive = class_exists('\WpFieldFlow\Core\Plugin');
        
        // Get quiz statistics
        $stats = $this->getDashboardStats();
        $settings = $this->settingsManager->getSettings();
        
        // Get analytics data
        $analytics = new \WpQuizFlow\Admin\Analytics();
        $analyticsData = $analytics->getDashboardStats();
        
        include WP_QUIZ_FLOW_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }
    
    /**
     * Render quizzes page (future: quiz builder)
     *
     * @since 1.0.0
     * @return void
     */
    public function renderQuizzesPage(): void
    {
        if (!current_user_can(self::REQUIRED_CAPABILITY)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-quiz-flow'));
        }
        
        // Get available quizzes from JSON files
        $quizData = new \WpQuizFlow\Quiz\QuizData();
        $availableQuizzes = $quizData->getAvailableQuizzes();
        
        include WP_QUIZ_FLOW_PLUGIN_DIR . 'templates/admin/quizzes.php';
    }
    
    /**
     * Render settings page
     *
     * @since 1.0.0
     * @return void
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can(self::REQUIRED_CAPABILITY)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-quiz-flow'));
        }
        
        // Handle form submission
        if (isset($_POST['wp_quiz_flow_save_settings']) && check_admin_referer('wp_quiz_flow_settings')) {
            $result = $this->settingsManager->handleSaveSettings($_POST);
            
            if ($result['success']) {
                add_action('admin_notices', function () use ($result) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
                });
            } else {
                add_action('admin_notices', function () use ($result) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
                });
            }
        }
        
        $settings = $this->settingsManager->getSettings();
        
        // Check if wpFieldFlow Debug is available
        $wpFieldFlowDebugAvailable = class_exists('\WpFieldFlow\Core\Debug') && method_exists('\WpFieldFlow\Core\Debug', 'isDebugEnabled');
        $wpFieldFlowDebugEnabled = false;
        if ($wpFieldFlowDebugAvailable) {
            try {
                $wpFieldFlowDebugEnabled = \WpFieldFlow\Core\Debug::isDebugEnabled();
            } catch (\Throwable $e) {
                // If calling the method fails, Debug is not available
                $wpFieldFlowDebugAvailable = false;
            }
        }
        
        include WP_QUIZ_FLOW_PLUGIN_DIR . 'templates/admin/settings.php';
    }
    
    /**
     * Render usage guide page
     *
     * @since 1.0.0
     * @return void
     */
    public function renderUsagePage(): void
    {
        if (!current_user_can(self::REQUIRED_CAPABILITY)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-quiz-flow'));
        }
        
        // Read usage guide markdown and display
        $usageGuidePath = WP_QUIZ_FLOW_PLUGIN_DIR . 'QUIZ_USAGE.md';
        $usageGuide = file_exists($usageGuidePath) ? file_get_contents($usageGuidePath) : '';
        
        // Make variable available to template
        include WP_QUIZ_FLOW_PLUGIN_DIR . 'templates/admin/usage.php';
    }
    
    /**
     * Display admin notices
     *
     * @since 1.0.0
     * @return void
     */
    public function displayAdminNotices(): void
    {
        // Check wpFieldFlow dependency
        if (!class_exists('\WpFieldFlow\Core\Plugin')) {
            $currentScreen = get_current_screen();
            if ($currentScreen && strpos($currentScreen->id, 'wp-quiz-flow') !== false) {
                printf(
                    '<div class="notice notice-error"><p><strong>%s:</strong> %s <a href="%s">%s</a></p></div>',
                    esc_html__('wpQuizFlow Error', 'wp-quiz-flow'),
                    esc_html__('wpFieldFlow plugin is required but not active.', 'wp-quiz-flow'),
                    esc_url(admin_url('plugins.php')),
                    esc_html__('Activate wpFieldFlow', 'wp-quiz-flow')
                );
            }
        }
    }
    
    /**
     * Add plugin action links
     *
     * @since 1.0.0
     * @param array $links Existing plugin action links
     * @return array Modified plugin action links
     */
    public function addPluginActionLinks(array $links): array
    {
        $pluginLinks = [
            sprintf(
                '<a href="%s">%s</a>',
                admin_url('admin.php?page=wp-quiz-flow'),
                __('Dashboard', 'wp-quiz-flow')
            ),
            sprintf(
                '<a href="%s">%s</a>',
                admin_url('admin.php?page=wp-quiz-flow-settings'),
                __('Settings', 'wp-quiz-flow')
            )
        ];
        
        return array_merge($pluginLinks, $links);
    }
    
    /**
     * Get dashboard statistics
     *
     * @since 1.0.0
     * @return array Dashboard stats
     */
    private function getDashboardStats(): array
    {
        $quizData = new \WpQuizFlow\Quiz\QuizData();
        $availableQuizzes = $quizData->getAvailableQuizzes();
        
        return [
            'total_quizzes' => count($availableQuizzes),
            'available_quizzes' => $availableQuizzes
        ];
    }
    
    /**
     * Get settings manager instance
     *
     * @since 1.0.0
     * @return SettingsManager
     */
    public function getSettingsManager(): SettingsManager
    {
        return $this->settingsManager;
    }
    
    /**
     * Handle duplicate quiz AJAX request
     *
     * @since 1.0.0
     * @return void
     */
    public function handleDuplicateQuiz(): void
    {
        check_ajax_referer('wp_quiz_flow_admin', 'nonce');
        
        if (!current_user_can(self::REQUIRED_CAPABILITY)) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-quiz-flow')]);
            return;
        }
        
        $quizId = sanitize_text_field($_POST['quiz_id'] ?? '');
        if (empty($quizId)) {
            wp_send_json_error(['message' => __('Quiz ID is required', 'wp-quiz-flow')]);
            return;
        }
        
        // Load quiz from database or JSON
        $quizData = new \WpQuizFlow\Quiz\QuizData();
        $quiz = $quizData->loadQuiz($quizId);
        
        if (!$quiz) {
            wp_send_json_error(['message' => __('Quiz not found', 'wp-quiz-flow')]);
            return;
        }
        
        // Create new post
        $newPostId = wp_insert_post([
            'post_title' => $quiz['title'] . ' (Copy)',
            'post_content' => $quiz['description'] ?? '',
            'post_status' => 'publish',
            'post_type' => 'wp_quiz_flow_quiz'
        ]);
        
        if (is_wp_error($newPostId)) {
            wp_send_json_error(['message' => __('Failed to duplicate quiz', 'wp-quiz-flow')]);
            return;
        }
        
        // Copy meta data
        $newQuizId = $quizId . '-copy-' . time();
        update_post_meta($newPostId, '_quiz_id', $newQuizId);
        update_post_meta($newPostId, '_quiz_structure', wp_json_encode($quiz, JSON_PRETTY_PRINT));
        update_post_meta($newPostId, '_quiz_version', $quiz['version'] ?? '1.0.0');
        update_post_meta($newPostId, '_target_sheet_id', $quiz['target_sheet_id'] ?? '');
        
        wp_send_json_success([
            'message' => __('Quiz duplicated successfully', 'wp-quiz-flow'),
            'post_id' => $newPostId,
            'edit_url' => admin_url('post.php?post=' . $newPostId . '&action=edit')
        ]);
    }
    
    /**
     * Handle export quiz AJAX request
     *
     * @since 1.0.0
     * @return void
     */
    public function handleExportQuiz(): void
    {
        check_ajax_referer('wp_quiz_flow_admin', 'nonce');
        
        if (!current_user_can(self::REQUIRED_CAPABILITY)) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-quiz-flow')]);
            return;
        }
        
        $quizId = sanitize_text_field($_POST['quiz_id'] ?? '');
        if (empty($quizId)) {
            wp_send_json_error(['message' => __('Quiz ID is required', 'wp-quiz-flow')]);
            return;
        }
        
        // Load quiz
        $quizData = new \WpQuizFlow\Quiz\QuizData();
        $quiz = $quizData->loadQuiz($quizId);
        
        if (!$quiz) {
            wp_send_json_error(['message' => __('Quiz not found', 'wp-quiz-flow')]);
            return;
        }
        
        // Return JSON
        wp_send_json_success([
            'quiz' => $quiz,
            'json' => wp_json_encode($quiz, JSON_PRETTY_PRINT)
        ]);
    }
    
    /**
     * Handle delete quiz AJAX request
     *
     * @since 1.0.0
     * @return void
     */
    public function handleDeleteQuiz(): void
    {
        check_ajax_referer('wp_quiz_flow_admin', 'nonce');
        
        if (!current_user_can(self::REQUIRED_CAPABILITY)) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-quiz-flow')]);
            return;
        }
        
        $postId = intval($_POST['post_id'] ?? 0);
        if (!$postId) {
            wp_send_json_error(['message' => __('Post ID is required', 'wp-quiz-flow')]);
            return;
        }
        
        // Check post type
        if (get_post_type($postId) !== 'wp_quiz_flow_quiz') {
            wp_send_json_error(['message' => __('Invalid post type', 'wp-quiz-flow')]);
            return;
        }
        
        // Delete post
        $result = wp_delete_post($postId, true);
        
        if (!$result) {
            wp_send_json_error(['message' => __('Failed to delete quiz', 'wp-quiz-flow')]);
            return;
        }
        
        wp_send_json_success(['message' => __('Quiz deleted successfully', 'wp-quiz-flow')]);
    }
}

