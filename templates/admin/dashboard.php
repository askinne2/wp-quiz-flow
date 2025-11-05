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
        
        <?php if (isset($analyticsData['overall'])): ?>
            <div class="stats-card" style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 4px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span class="dashicons dashicons-chart-line" style="font-size: 32px; color: #2271b1;"></span>
                    <div>
                        <h3 style="margin: 0; font-size: 28px;"><?php echo esc_html($analyticsData['overall']['total_sessions'] ?? 0); ?></h3>
                        <p style="margin: 5px 0 0; color: #666;"><?php esc_html_e('Total Quiz Sessions', 'wp-quiz-flow'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card" style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 4px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span class="dashicons dashicons-yes-alt" style="font-size: 32px; color: #00a32a;"></span>
                    <div>
                        <h3 style="margin: 0; font-size: 28px;"><?php echo esc_html($analyticsData['overall']['completion_rate'] ?? 0); ?>%</h3>
                        <p style="margin: 5px 0 0; color: #666;"><?php esc_html_e('Completion Rate', 'wp-quiz-flow'); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Analytics Section -->
    <?php if (isset($analyticsData['overall']) && $analyticsData['overall']['total_sessions'] > 0): ?>
        <div class="wp-quiz-flow-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <h2><?php esc_html_e('Analytics Overview', 'wp-quiz-flow'); ?></h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                <div>
                    <h3><?php esc_html_e('Session Statistics', 'wp-quiz-flow'); ?></h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                            <strong><?php esc_html_e('Total Sessions:', 'wp-quiz-flow'); ?></strong>
                            <?php echo esc_html($analyticsData['overall']['total_sessions'] ?? 0); ?>
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                            <strong><?php esc_html_e('Completed Sessions:', 'wp-quiz-flow'); ?></strong>
                            <?php echo esc_html($analyticsData['overall']['completed_sessions'] ?? 0); ?>
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                            <strong><?php esc_html_e('Completion Rate:', 'wp-quiz-flow'); ?></strong>
                            <?php echo esc_html($analyticsData['overall']['completion_rate'] ?? 0); ?>%
                        </li>
                        <li style="padding: 10px 0;">
                            <strong><?php esc_html_e('Avg Results per Session:', 'wp-quiz-flow'); ?></strong>
                            <?php echo esc_html($analyticsData['overall']['avg_result_count'] ?? 0); ?>
                        </li>
                    </ul>
                </div>
                
                <?php if (!empty($analyticsData['by_quiz'])): ?>
                    <div>
                        <h3><?php esc_html_e('Per-Quiz Statistics', 'wp-quiz-flow'); ?></h3>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Quiz ID', 'wp-quiz-flow'); ?></th>
                                    <th><?php esc_html_e('Sessions', 'wp-quiz-flow'); ?></th>
                                    <th><?php esc_html_e('Completed', 'wp-quiz-flow'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($analyticsData['by_quiz'], 0, 5) as $quizStat): ?>
                                    <tr>
                                        <td><code><?php echo esc_html($quizStat['quiz_id'] ?? ''); ?></code></td>
                                        <td><?php echo esc_html($quizStat['total_sessions'] ?? 0); ?></td>
                                        <td><?php echo esc_html($quizStat['completed_sessions'] ?? 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

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

