<?php
/**
 * Main Plugin Class
 *
 * Orchestrates all plugin functionality
 *
 * @package WpQuizFlow\Core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Core;

use WpQuizFlow\Frontend\ShortcodeManager;
use WpQuizFlow\Quiz\QuizManager;

/**
 * Main Plugin Class
 *
 * Singleton pattern for plugin initialization
 *
 * @since 1.0.0
 */
final class Plugin
{
    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;
    
    /**
     * Shortcode Manager instance
     *
     * @var ShortcodeManager|null
     */
    private ?ShortcodeManager $shortcodeManager = null;
    
    /**
     * Quiz Manager instance
     *
     * @var QuizManager|null
     */
    private ?QuizManager $quizManager = null;
    
    /**
     * Private constructor to prevent direct instantiation
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        $this->initializeComponents();
        $this->initializeHooks();
    }
    
    /**
     * Get plugin instance (Singleton)
     *
     * @since 1.0.0
     * @return Plugin
     */
    public static function getInstance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Prevent cloning
     *
     * @since 1.0.0
     * @return void
     */
    private function __clone()
    {
        // Prevent cloning
    }
    
    /**
     * Prevent unserialization
     *
     * @since 1.0.0
     * @return void
     */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize singleton');
    }
    
    /**
     * Initialize plugin components
     *
     * @since 1.0.0
     * @return void
     */
    private function initializeComponents(): void
    {
        // Initialize Quiz Manager
        $this->quizManager = new QuizManager();
        
        // Initialize Shortcode Manager
        $this->shortcodeManager = new ShortcodeManager($this->quizManager);
    }
    
    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     * @return void
     */
    private function initializeHooks(): void
    {
        // Initialize shortcodes
        add_action('init', [$this, 'registerShortcodes']);
        
        // Register AJAX handlers
        add_action('wp_ajax_wp_quiz_flow_get_quiz_data', [$this, 'handleGetQuizData']);
        add_action('wp_ajax_nopriv_wp_quiz_flow_get_quiz_data', [$this, 'handleGetQuizData']);
    }
    
    /**
     * Register shortcodes
     *
     * @since 1.0.0
     * @return void
     */
    public function registerShortcodes(): void
    {
        if ($this->shortcodeManager !== null) {
            $this->shortcodeManager->registerShortcodes();
        }
    }
    
    /**
     * Handle AJAX request for quiz data
     *
     * @since 1.0.0
     * @return void
     */
    public function handleGetQuizData(): void
    {
        // Verify nonce
        $nonce = sanitize_text_field($_REQUEST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'wp_quiz_flow_frontend')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }
        
        $quizId = sanitize_text_field($_REQUEST['quiz_id'] ?? '');
        if (empty($quizId)) {
            wp_send_json_error(['message' => 'Quiz ID is required']);
            return;
        }
        
        if ($this->quizManager !== null) {
            $quizData = $this->quizManager->getQuizData($quizId);
            
            if ($quizData !== null) {
                wp_send_json_success(['quiz' => $quizData]);
            } else {
                wp_send_json_error(['message' => 'Quiz not found']);
            }
        } else {
            wp_send_json_error(['message' => 'Quiz manager not initialized']);
        }
    }
    
    /**
     * Get Shortcode Manager instance
     *
     * @since 1.0.0
     * @return ShortcodeManager|null
     */
    public function getShortcodeManager(): ?ShortcodeManager
    {
        return $this->shortcodeManager;
    }
    
    /**
     * Get Quiz Manager instance
     *
     * @since 1.0.0
     * @return QuizManager|null
     */
    public function getQuizManager(): ?QuizManager
    {
        return $this->quizManager;
    }
}

