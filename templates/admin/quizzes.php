<?php
/**
 * Quizzes Management Template
 *
 * @package WpQuizFlow\Admin
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wp-quiz-flow-admin">
    <div class="wp-quiz-flow-header">
        <h1><?php esc_html_e('Quizzes', 'wp-quiz-flow'); ?></h1>
        <p class="description">
            <?php esc_html_e('Manage your quiz definitions. Quizzes can be stored in the database or as JSON files.', 'wp-quiz-flow'); ?>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=wp_quiz_flow_quiz')); ?>" class="button button-primary" style="margin-left: 10px;">
                <?php esc_html_e('Add New Quiz', 'wp-quiz-flow'); ?>
            </a>
        </p>
    </div>

    <?php if (empty($availableQuizzes)): ?>
        <div class="notice notice-info">
            <p><?php esc_html_e('No quizzes found. Create quiz JSON files in the assets/json/ directory.', 'wp-quiz-flow'); ?></p>
        </div>
    <?php else: ?>
        <div class="wp-quiz-flow-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Quiz ID', 'wp-quiz-flow'); ?></th>
                        <th><?php esc_html_e('Title', 'wp-quiz-flow'); ?></th>
                        <th><?php esc_html_e('Version', 'wp-quiz-flow'); ?></th>
                        <th><?php esc_html_e('Source', 'wp-quiz-flow'); ?></th>
                        <th><?php esc_html_e('Shortcode', 'wp-quiz-flow'); ?></th>
                        <th><?php esc_html_e('Actions', 'wp-quiz-flow'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availableQuizzes as $quiz): ?>
                        <tr>
                            <td><code><?php echo esc_html($quiz['id']); ?></code></td>
                            <td><strong><?php echo esc_html($quiz['title']); ?></strong></td>
                            <td><?php echo esc_html($quiz['version'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if (isset($quiz['source'])): ?>
                                    <span class="dashicons dashicons-<?php echo $quiz['source'] === 'database' ? 'database' : 'media-code'; ?>" title="<?php echo esc_attr($quiz['source']); ?>"></span>
                                    <?php echo esc_html(ucfirst($quiz['source'])); ?>
                                <?php else: ?>
                                    <?php esc_html_e('Unknown', 'wp-quiz-flow'); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code>[wpQuizFlow id="2" quiz_id="<?php echo esc_attr($quiz['id']); ?>"]</code>
                                <button type="button" 
                                        class="button button-small wp-quiz-flow-copy-shortcode" 
                                        data-shortcode='[wpQuizFlow id="2" quiz_id="<?php echo esc_attr($quiz['id']); ?>"]'
                                        style="margin-left: 5px;">
                                    <?php esc_html_e('Copy', 'wp-quiz-flow'); ?>
                                </button>
                            </td>
                            <td>
                                <div class="wp-quiz-flow-actions">
                                    <?php if (isset($quiz['post_id'])): ?>
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $quiz['post_id'] . '&action=edit')); ?>" 
                                           class="button button-small">
                                            <?php esc_html_e('Edit', 'wp-quiz-flow'); ?>
                                        </a>
                                        <button type="button" 
                                                class="button button-small wp-quiz-flow-duplicate-quiz" 
                                                data-quiz-id="<?php echo esc_attr($quiz['id']); ?>">
                                            <?php esc_html_e('Duplicate', 'wp-quiz-flow'); ?>
                                        </button>
                                        <button type="button" 
                                                class="button button-small wp-quiz-flow-export-quiz" 
                                                data-quiz-id="<?php echo esc_attr($quiz['id']); ?>">
                                            <?php esc_html_e('Export', 'wp-quiz-flow'); ?>
                                        </button>
                                        <button type="button" 
                                                class="button button-small button-link-delete wp-quiz-flow-delete-quiz" 
                                                data-post-id="<?php echo esc_attr($quiz['post_id']); ?>"
                                                data-quiz-title="<?php echo esc_attr($quiz['title']); ?>">
                                            <?php esc_html_e('Delete', 'wp-quiz-flow'); ?>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" 
                                                class="button button-small wp-quiz-flow-export-quiz" 
                                                data-quiz-id="<?php echo esc_attr($quiz['id']); ?>">
                                            <?php esc_html_e('Export', 'wp-quiz-flow'); ?>
                                        </button>
                                        <span class="description"><?php esc_html_e('(JSON file - edit manually)', 'wp-quiz-flow'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="wp-quiz-flow-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <h2><?php esc_html_e('Adding New Quizzes', 'wp-quiz-flow'); ?></h2>
            <p>
                <strong><?php esc_html_e('Option 1: Database (Recommended)', 'wp-quiz-flow'); ?></strong><br>
                <?php esc_html_e('Click "Add New Quiz" above to create a quiz in the database. This allows you to edit quizzes through the WordPress admin interface.', 'wp-quiz-flow'); ?>
            </p>
            <p>
                <strong><?php esc_html_e('Option 2: JSON Files', 'wp-quiz-flow'); ?></strong><br>
                <?php esc_html_e('To add a new quiz via JSON, create a JSON file in the', 'wp-quiz-flow'); ?>
                <code><?php echo esc_html(WP_QUIZ_FLOW_PLUGIN_DIR . 'assets/json/'); ?></code>
                <?php esc_html_e('directory. The filename should match the quiz ID.', 'wp-quiz-flow'); ?>
                <br>
                <?php esc_html_e('Example:', 'wp-quiz-flow'); ?>
                <code>my-quiz.json</code>
                <?php esc_html_e('can be used with', 'wp-quiz-flow'); ?>
                <code>[wpQuizFlow id="2" quiz_id="my-quiz"]</code>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Copy shortcode
    $('.wp-quiz-flow-copy-shortcode').on('click', function() {
        const shortcode = $(this).data('shortcode');
        navigator.clipboard.writeText(shortcode).then(function() {
            alert('<?php esc_html_e('Shortcode copied to clipboard!', 'wp-quiz-flow'); ?>');
        });
    });
    
    // Duplicate quiz
    $('.wp-quiz-flow-duplicate-quiz').on('click', function() {
        const quizId = $(this).data('quiz-id');
        const button = $(this);
        
        if (!confirm('<?php esc_html_e('Duplicate this quiz?', 'wp-quiz-flow'); ?>')) {
            return;
        }
        
        button.prop('disabled', true).text('<?php esc_html_e('Duplicating...', 'wp-quiz-flow'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_quiz_flow_duplicate_quiz',
                quiz_id: quizId,
                nonce: '<?php echo wp_create_nonce('wp_quiz_flow_admin'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    window.location.reload();
                } else {
                    alert(response.data.message || '<?php esc_html_e('Failed to duplicate quiz', 'wp-quiz-flow'); ?>');
                    button.prop('disabled', false).text('<?php esc_html_e('Duplicate', 'wp-quiz-flow'); ?>');
                }
            },
            error: function() {
                alert('<?php esc_html_e('Error duplicating quiz', 'wp-quiz-flow'); ?>');
                button.prop('disabled', false).text('<?php esc_html_e('Duplicate', 'wp-quiz-flow'); ?>');
            }
        });
    });
    
    // Export quiz
    $('.wp-quiz-flow-export-quiz').on('click', function() {
        const quizId = $(this).data('quiz-id');
        const button = $(this);
        
        button.prop('disabled', true).text('<?php esc_html_e('Exporting...', 'wp-quiz-flow'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_quiz_flow_export_quiz',
                quiz_id: quizId,
                nonce: '<?php echo wp_create_nonce('wp_quiz_flow_admin'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Create download link
                    const blob = new Blob([response.data.json], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = quizId + '.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                } else {
                    alert(response.data.message || '<?php esc_html_e('Failed to export quiz', 'wp-quiz-flow'); ?>');
                }
                button.prop('disabled', false).text('<?php esc_html_e('Export', 'wp-quiz-flow'); ?>');
            },
            error: function() {
                alert('<?php esc_html_e('Error exporting quiz', 'wp-quiz-flow'); ?>');
                button.prop('disabled', false).text('<?php esc_html_e('Export', 'wp-quiz-flow'); ?>');
            }
        });
    });
    
    // Delete quiz
    $('.wp-quiz-flow-delete-quiz').on('click', function() {
        const postId = $(this).data('post-id');
        const quizTitle = $(this).data('quiz-title');
        const button = $(this);
        const row = button.closest('tr');
        
        if (!confirm('<?php esc_html_e('Are you sure you want to delete', 'wp-quiz-flow'); ?> "' + quizTitle + '"? <?php esc_html_e('This action cannot be undone.', 'wp-quiz-flow'); ?>')) {
            return;
        }
        
        button.prop('disabled', true).text('<?php esc_html_e('Deleting...', 'wp-quiz-flow'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_quiz_flow_delete_quiz',
                post_id: postId,
                nonce: '<?php echo wp_create_nonce('wp_quiz_flow_admin'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    row.fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data.message || '<?php esc_html_e('Failed to delete quiz', 'wp-quiz-flow'); ?>');
                    button.prop('disabled', false).text('<?php esc_html_e('Delete', 'wp-quiz-flow'); ?>');
                }
            },
            error: function() {
                alert('<?php esc_html_e('Error deleting quiz', 'wp-quiz-flow'); ?>');
                button.prop('disabled', false).text('<?php esc_html_e('Delete', 'wp-quiz-flow'); ?>');
            }
        });
    });
});
</script>

