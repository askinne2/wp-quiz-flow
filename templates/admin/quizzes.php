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
        <p class="description"><?php esc_html_e('Manage your quiz definitions. Quizzes are currently stored as JSON files.', 'wp-quiz-flow'); ?></p>
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
                        <th><?php esc_html_e('Description', 'wp-quiz-flow'); ?></th>
                        <th><?php esc_html_e('Shortcode', 'wp-quiz-flow'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availableQuizzes as $quiz): ?>
                        <tr>
                            <td><code><?php echo esc_html($quiz['id']); ?></code></td>
                            <td><strong><?php echo esc_html($quiz['title']); ?></strong></td>
                            <td><?php echo esc_html($quiz['version'] ?? 'N/A'); ?></td>
                            <td><?php echo esc_html($quiz['description'] ?? ''); ?></td>
                            <td>
                                <code>[wpQuizFlow id="2" quiz="<?php echo esc_attr($quiz['id']); ?>"]</code>
                                <button type="button" 
                                        class="button button-small" 
                                        onclick="navigator.clipboard.writeText('[wpQuizFlow id=\"2\" quiz=\"<?php echo esc_js($quiz['id']); ?>\"')"
                                        style="margin-left: 5px;">
                                    <?php esc_html_e('Copy', 'wp-quiz-flow'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="wp-quiz-flow-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <h2><?php esc_html_e('Adding New Quizzes', 'wp-quiz-flow'); ?></h2>
            <p>
                <?php esc_html_e('To add a new quiz, create a JSON file in the', 'wp-quiz-flow'); ?>
                <code><?php echo esc_html(WP_QUIZ_FLOW_PLUGIN_DIR . 'assets/json/'); ?></code>
                <?php esc_html_e('directory. The filename should match the quiz ID.', 'wp-quiz-flow'); ?>
            </p>
            <p>
                <?php esc_html_e('Example:', 'wp-quiz-flow'); ?>
                <code>my-quiz.json</code>
                <?php esc_html_e('can be used with', 'wp-quiz-flow'); ?>
                <code>[wpQuizFlow id="2" quiz="my-quiz"]</code>
            </p>
        </div>
    <?php endif; ?>
</div>

