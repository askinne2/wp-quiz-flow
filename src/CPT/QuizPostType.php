<?php
/**
 * Quiz Custom Post Type
 *
 * Registers and manages the wp_quiz_flow_quiz custom post type
 *
 * @package WpQuizFlow\CPT
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\CPT;

/**
 * Quiz Post Type Class
 *
 * Handles registration and management of quiz custom post type
 *
 * @since 1.0.0
 */
class QuizPostType
{
    /**
     * Post type slug
     *
     * @var string
     */
    private const POST_TYPE = 'wp_quiz_flow_quiz';
    
    /**
     * Initialize post type
     *
     * @since 1.0.0
     * @return void
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post', [$this, 'saveQuizMeta']);
    }
    
    /**
     * Register custom post type
     *
     * @since 1.0.0
     * @return void
     */
    public function registerPostType(): void
    {
        $labels = [
            'name' => _x('Quizzes', 'Post type general name', 'wp-quiz-flow'),
            'singular_name' => _x('Quiz', 'Post type singular name', 'wp-quiz-flow'),
            'menu_name' => _x('Quizzes', 'Admin Menu text', 'wp-quiz-flow'),
            'name_admin_bar' => _x('Quiz', 'Add New on Toolbar', 'wp-quiz-flow'),
            'add_new' => __('Add New', 'wp-quiz-flow'),
            'add_new_item' => __('Add New Quiz', 'wp-quiz-flow'),
            'new_item' => __('New Quiz', 'wp-quiz-flow'),
            'edit_item' => __('Edit Quiz', 'wp-quiz-flow'),
            'view_item' => __('View Quiz', 'wp-quiz-flow'),
            'all_items' => __('All Quizzes', 'wp-quiz-flow'),
            'search_items' => __('Search Quizzes', 'wp-quiz-flow'),
            'parent_item_colon' => __('Parent Quizzes:', 'wp-quiz-flow'),
            'not_found' => __('No quizzes found.', 'wp-quiz-flow'),
            'not_found_in_trash' => __('No quizzes found in Trash.', 'wp-quiz-flow'),
            'featured_image' => _x('Quiz Cover Image', 'Overrides the "Featured Image" phrase', 'wp-quiz-flow'),
            'set_featured_image' => _x('Set cover image', 'Overrides the "Set featured image" phrase', 'wp-quiz-flow'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the "Remove featured image" phrase', 'wp-quiz-flow'),
            'use_featured_image' => _x('Use as cover image', 'Overrides the "Use as featured image" phrase', 'wp-quiz-flow'),
            'archives' => _x('Quiz Archives', 'The post type archive label used in nav menus', 'wp-quiz-flow'),
            'insert_into_item' => _x('Insert into quiz', 'Overrides the "Insert into post"/"Insert into page" phrase', 'wp-quiz-flow'),
            'uploaded_to_this_item' => _x('Uploaded to this quiz', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'wp-quiz-flow'),
            'filter_items_list' => _x('Filter quizzes list', 'Screen reader text for the filter links heading', 'wp-quiz-flow'),
            'items_list_navigation' => _x('Quizzes list navigation', 'Screen reader text for the pagination heading', 'wp-quiz-flow'),
            'items_list' => _x('Quizzes list', 'Screen reader text for the items list heading', 'wp-quiz-flow'),
        ];
        
        $args = [
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // We'll add it to our custom menu
            'query_var' => true,
            'rewrite' => ['slug' => 'quiz'],
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'menu_icon' => 'dashicons-clipboard',
            'supports' => ['title', 'editor', 'revisions'],
            'show_in_rest' => false, // We'll use custom REST endpoints if needed
        ];
        
        register_post_type(self::POST_TYPE, $args);
    }
    
    /**
     * Add meta boxes for quiz editing
     *
     * @since 1.0.0
     * @return void
     */
    public function addMetaBoxes(): void
    {
        add_meta_box(
            'wp_quiz_flow_structure',
            __('Quiz Structure', 'wp-quiz-flow'),
            [$this, 'renderStructureMetaBox'],
            self::POST_TYPE,
            'normal',
            'high'
        );
        
        add_meta_box(
            'wp_quiz_flow_settings',
            __('Quiz Settings', 'wp-quiz-flow'),
            [$this, 'renderSettingsMetaBox'],
            self::POST_TYPE,
            'side',
            'default'
        );
    }
    
    /**
     * Render quiz structure meta box
     *
     * @param \WP_Post $post The post object
     * @return void
     */
    public function renderStructureMetaBox(\WP_Post $post): void
    {
        wp_nonce_field('wp_quiz_flow_save_quiz', 'wp_quiz_flow_quiz_nonce');
        
        $quizStructure = get_post_meta($post->ID, '_quiz_structure', true);
        $quizId = get_post_meta($post->ID, '_quiz_id', true) ?: sanitize_title($post->post_title);
        
        // If no structure, try to load from JSON
        if (empty($quizStructure)) {
            $quizData = new \WpQuizFlow\Quiz\QuizData();
            $jsonQuiz = $quizData->loadQuiz($quizId);
            if ($jsonQuiz) {
                $quizStructure = wp_json_encode($jsonQuiz, JSON_PRETTY_PRINT);
            }
        }
        
        ?>
        <div class="wp-quiz-flow-meta-box">
            <p>
                <label for="wp_quiz_flow_id">
                    <strong><?php esc_html_e('Quiz ID:', 'wp-quiz-flow'); ?></strong>
                </label>
                <input type="text" 
                       id="wp_quiz_flow_id" 
                       name="wp_quiz_flow_id" 
                       value="<?php echo esc_attr($quizId); ?>" 
                       class="regular-text" 
                       placeholder="e.g., noma-quiz"
                />
                <br>
                <span class="description">
                    <?php esc_html_e('Unique identifier for this quiz. Used in shortcode: [wpQuizFlow quiz_id="your-id"]', 'wp-quiz-flow'); ?>
                </span>
            </p>
            
            <p>
                <label for="wp_quiz_flow_structure">
                    <strong><?php esc_html_e('Quiz Structure (JSON):', 'wp-quiz-flow'); ?></strong>
                </label>
                <textarea 
                    id="wp_quiz_flow_structure" 
                    name="wp_quiz_flow_structure" 
                    rows="20" 
                    class="large-text code" 
                    style="font-family: monospace;"
                ><?php echo esc_textarea($quizStructure); ?></textarea>
                <br>
                <span class="description">
                    <?php esc_html_e('Quiz structure in JSON format. See documentation for format details.', 'wp-quiz-flow'); ?>
                </span>
            </p>
            
            <p>
                <button type="button" class="button" id="wp-quiz-flow-validate">
                    <?php esc_html_e('Validate Structure', 'wp-quiz-flow'); ?>
                </button>
                <button type="button" class="button" id="wp-quiz-flow-preview">
                    <?php esc_html_e('Preview Quiz', 'wp-quiz-flow'); ?>
                </button>
            </p>
            
            <div id="wp-quiz-flow-validation-result" style="display: none;"></div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wp-quiz-flow-validate').on('click', function() {
                const structure = $('#wp_quiz_flow_structure').val();
                const resultDiv = $('#wp-quiz-flow-validation-result');
                
                try {
                    const parsed = JSON.parse(structure);
                    resultDiv.html('<div class="notice notice-success inline"><p>✓ Valid JSON structure</p></div>').show();
                } catch (e) {
                    resultDiv.html('<div class="notice notice-error inline"><p>✗ Invalid JSON: ' + e.message + '</p></div>').show();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render quiz settings meta box
     *
     * @param \WP_Post $post The post object
     * @return void
     */
    public function renderSettingsMetaBox(\WP_Post $post): void
    {
        $targetSheetId = get_post_meta($post->ID, '_target_sheet_id', true);
        $quizVersion = get_post_meta($post->ID, '_quiz_version', true) ?: '1.0.0';
        
        ?>
        <div class="wp-quiz-flow-settings-meta-box">
            <p>
                <label for="wp_quiz_flow_target_sheet_id">
                    <strong><?php esc_html_e('Target Sheet ID:', 'wp-quiz-flow'); ?></strong>
                </label>
                <input type="number" 
                       id="wp_quiz_flow_target_sheet_id" 
                       name="wp_quiz_flow_target_sheet_id" 
                       value="<?php echo esc_attr($targetSheetId); ?>" 
                       class="small-text" 
                       min="1"
                />
                <br>
                <span class="description">
                    <?php esc_html_e('wpFieldFlow sheet ID this quiz targets.', 'wp-quiz-flow'); ?>
                </span>
            </p>
            
            <p>
                <label for="wp_quiz_flow_version">
                    <strong><?php esc_html_e('Quiz Version:', 'wp-quiz-flow'); ?></strong>
                </label>
                <input type="text" 
                       id="wp_quiz_flow_version" 
                       name="wp_quiz_flow_version" 
                       value="<?php echo esc_attr($quizVersion); ?>" 
                       class="small-text"
                />
                <br>
                <span class="description">
                    <?php esc_html_e('Version number for this quiz (e.g., 1.0.0).', 'wp-quiz-flow'); ?>
                </span>
            </p>
            
            <p>
                <strong><?php esc_html_e('Shortcode:', 'wp-quiz-flow'); ?></strong><br>
                <code>[wpQuizFlow id="<?php echo esc_attr($targetSheetId ?: '2'); ?>" quiz_id="<?php echo esc_attr(get_post_meta($post->ID, '_quiz_id', true) ?: 'your-quiz-id'); ?>"]</code>
            </p>
        </div>
        <?php
    }
    
    /**
     * Save quiz meta data
     *
     * @param int $postId Post ID
     * @return void
     */
    public function saveQuizMeta(int $postId): void
    {
        // Verify nonce
        if (!isset($_POST['wp_quiz_flow_quiz_nonce']) || 
            !wp_verify_nonce($_POST['wp_quiz_flow_quiz_nonce'], 'wp_quiz_flow_save_quiz')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $postId)) {
            return;
        }
        
        // Check post type
        if (get_post_type($postId) !== self::POST_TYPE) {
            return;
        }
        
        // Save quiz ID
        if (isset($_POST['wp_quiz_flow_id'])) {
            $quizId = sanitize_text_field($_POST['wp_quiz_flow_id']);
            update_post_meta($postId, '_quiz_id', $quizId);
        }
        
        // Save quiz structure
        if (isset($_POST['wp_quiz_flow_structure'])) {
            $structure = wp_unslash($_POST['wp_quiz_flow_structure']);
            
            // Validate JSON
            $decoded = json_decode($structure, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                update_post_meta($postId, '_quiz_structure', $structure);
            } else {
                // Log error but don't save invalid JSON
                if (class_exists('\WpFieldFlow\Core\Debug') && method_exists('\WpFieldFlow\Core\Debug', 'log')) {
                    try {
                        \WpFieldFlow\Core\Debug::log(
                            'Invalid JSON structure for quiz ' . $postId . ': ' . json_last_error_msg(),
                            'error',
                            'wpQuizFlow'
                        );
                    } catch (\Throwable $e) {
                        // Ignore
                    }
                }
            }
        }
        
        // Save target sheet ID
        if (isset($_POST['wp_quiz_flow_target_sheet_id'])) {
            $targetSheetId = intval($_POST['wp_quiz_flow_target_sheet_id']);
            update_post_meta($postId, '_target_sheet_id', $targetSheetId);
        }
        
        // Save quiz version
        if (isset($_POST['wp_quiz_flow_version'])) {
            $version = sanitize_text_field($_POST['wp_quiz_flow_version']);
            update_post_meta($postId, '_quiz_version', $version);
        }
        
        // Clear quiz cache
        $quizId = get_post_meta($postId, '_quiz_id', true);
        if ($quizId) {
            delete_transient('wp_quiz_flow_quiz_' . md5($quizId));
            delete_transient('wp_quiz_flow_json_quiz_' . md5($quizId));
        }
    }
    
    /**
     * Get post type slug
     *
     * @return string Post type slug
     */
    public static function getPostType(): string
    {
        return self::POST_TYPE;
    }
}

