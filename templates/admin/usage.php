<?php
/**
 * Usage Guide Template
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
        <h1><?php esc_html_e('wpQuizFlow Usage Guide', 'wp-quiz-flow'); ?></h1>
    </div>

    <div class="wp-quiz-flow-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
        <?php if (!empty($usageGuide)): ?>
            <div style="max-width: 800px;">
                <?php
                // Simple markdown to HTML conversion for basic formatting
                $html = $usageGuide;
                $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
                $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
                $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
                $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
                $html = nl2br($html);
                echo wp_kses_post($html);
                ?>
            </div>
        <?php else: ?>
            <p><?php esc_html_e('Usage guide not found. Check QUIZ_USAGE.md in the plugin directory.', 'wp-quiz-flow'); ?></p>
        <?php endif; ?>
    </div>

    <div class="wp-quiz-flow-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
        <h2><?php esc_html_e('Quick Start', 'wp-quiz-flow'); ?></h2>
        <ol>
            <li>
                <?php esc_html_e('Ensure wpFieldFlow is installed and a sheet is synced.', 'wp-quiz-flow'); ?>
            </li>
            <li>
                <?php esc_html_e('Use the shortcode:', 'wp-quiz-flow'); ?>
                <code>[wpQuizFlow id="2"]</code>
                <?php esc_html_e('where "2" is the wpFieldFlow sheet ID.', 'wp-quiz-flow'); ?>
            </li>
            <li>
                <?php esc_html_e('Optional: Specify a quiz:', 'wp-quiz-flow'); ?>
                <code>[wpQuizFlow id="2" quiz="noma-quiz"]</code>
            </li>
            <li>
                <?php esc_html_e('Optional: Configure result limit:', 'wp-quiz-flow'); ?>
                <code>[wpQuizFlow id="2" result_limit="24"]</code>
            </li>
        </ol>
    </div>
</div>

