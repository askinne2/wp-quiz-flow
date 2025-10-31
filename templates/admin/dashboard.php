<?php
/**
 * Quiz Dashboard Template
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
        <h1><?php esc_html_e('QuizFlow Dashboard', 'wp-quiz-flow'); ?></h1>
        <p class="description"><?php esc_html_e('Manage your interactive quizzes and configure quiz settings.', 'wp-quiz-flow'); ?></p>
    </div>

    <?php if (!$wpFieldFlowActive): ?>
        <div class="notice notice-error">
            <p><strong><?php esc_html_e('wpFieldFlow Required', 'wp-quiz-flow'); ?></strong>: <?php esc_html_e('wpFieldFlow plugin must be installed and active for wpQuizFlow to function.', 'wp-quiz-flow'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Quick Stats Cards -->
    <div class="wp-quiz-flow-stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="stats-card" style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 4px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span class="dashicons dashicons-clipboard" style="font-size: 32px; color: #2271b1;"></span>
                <div>
                    <h3 style="margin: 0; font-size: 28px;"><?php echo esc_html($stats['total_quizzes'] ?? 0); ?></h3>
                    <p style="margin: 5px 0 0; color: #666;"><?php esc_html_e('Available Quizzes', 'wp-quiz-flow'); ?></p>
                </div>
            </div>
        </div>

        <div class="stats-card" style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 4px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span class="dashicons dashicons-admin-plugins" style="font-size: 32px; color: #00a32a;"></span>
                <div>
                    <h3 style="margin: 0; font-size: 28px;"><?php echo $wpFieldFlowActive ? '✓' : '✗'; ?></h3>
                    <p style="margin: 5px 0 0; color: #666;"><?php esc_html_e('wpFieldFlow Status', 'wp-quiz-flow'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Quizzes -->
    <?php if (!empty($stats['available_quizzes'])): ?>
        <div class="wp-quiz-flow-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <h2><?php esc_html_e('Available Quizzes', 'wp-quiz-flow'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Quiz ID', 'wp-quiz-flow'); ?></th>
                        <th><?php esc_html_e('Title', 'wp-quiz-flow'); ?></th>
                        <th><?php esc_html_e('Version', 'wp-quiz-flow'); ?></th>
                        <th><?php esc_html_e('Description', 'wp-quiz-flow'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['available_quizzes'] as $quiz): ?>
                        <tr>
                            <td><code><?php echo esc_html($quiz['id']); ?></code></td>
                            <td><strong><?php echo esc_html($quiz['title']); ?></strong></td>
                            <td><?php echo esc_html($quiz['version'] ?? 'N/A'); ?></td>
                            <td><?php echo esc_html($quiz['description'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Quick Links -->
    <div class="wp-quiz-flow-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
        <h2><?php esc_html_e('Quick Links', 'wp-quiz-flow'); ?></h2>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-quiz-flow-settings')); ?>" class="button button-primary">
                <?php esc_html_e('Configure Settings', 'wp-quiz-flow'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-quiz-flow-usage')); ?>" class="button">
                <?php esc_html_e('View Usage Guide', 'wp-quiz-flow'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-quiz-flow-quizzes')); ?>" class="button">
                <?php esc_html_e('Manage Quizzes', 'wp-quiz-flow'); ?>
            </a>
        </p>
    </div>
</div>

