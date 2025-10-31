<?php
/**
 * Quiz Settings Template
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
        <h1><?php esc_html_e('QuizFlow Settings', 'wp-quiz-flow'); ?></h1>
        <p class="description"><?php esc_html_e('Configure default quiz settings and behavior.', 'wp-quiz-flow'); ?></p>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('wp_quiz_flow_settings'); ?>
        
        <div class="wp-quiz-flow-settings-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <h2><?php esc_html_e('Default Quiz Settings', 'wp-quiz-flow'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="default_quiz_id"><?php esc_html_e('Default Quiz ID', 'wp-quiz-flow'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="default_quiz_id" 
                               name="default_quiz_id" 
                               value="<?php echo esc_attr($settings['default_quiz_id'] ?? 'noma-quiz'); ?>" 
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e('The default quiz to use when no quiz ID is specified in the shortcode.', 'wp-quiz-flow'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="default_contact_number"><?php esc_html_e('Default Contact Number', 'wp-quiz-flow'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="default_contact_number" 
                               name="default_contact_number" 
                               value="<?php echo esc_attr($settings['default_contact_number'] ?? '205-555-0100'); ?>" 
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e('Default phone number to display in quiz results when contact information is shown.', 'wp-quiz-flow'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="default_result_limit"><?php esc_html_e('Default Result Limit', 'wp-quiz-flow'); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               id="default_result_limit" 
                               name="default_result_limit" 
                               value="<?php echo esc_attr($settings['default_result_limit'] ?? 12); ?>" 
                               min="1" 
                               max="100" 
                               class="small-text" />
                        <p class="description">
                            <?php esc_html_e('Default number of results to display (1-100). Can be overridden in shortcode.', 'wp-quiz-flow'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Display Options', 'wp-quiz-flow'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" 
                                       name="show_progress_default" 
                                       value="1" 
                                       <?php checked(!empty($settings['show_progress_default'])); ?> />
                                <?php esc_html_e('Show progress indicator by default', 'wp-quiz-flow'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" 
                                       name="show_contact_default" 
                                       value="1" 
                                       <?php checked(!empty($settings['show_contact_default'])); ?> />
                                <?php esc_html_e('Show contact information by default', 'wp-quiz-flow'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wp-quiz-flow-settings-section" style="background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <h2><?php esc_html_e('Debug & Logging', 'wp-quiz-flow'); ?></h2>
            
            <?php if ($wpFieldFlowDebugAvailable): ?>
                <p>
                    <?php esc_html_e('wpQuizFlow uses wpFieldFlow\'s debug system for logging.', 'wp-quiz-flow'); ?>
                </p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Debug Status', 'wp-quiz-flow'); ?></th>
                        <td>
                            <?php if ($wpFieldFlowDebugEnabled): ?>
                                <span style="color: #00a32a;">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <?php esc_html_e('Enabled', 'wp-quiz-flow'); ?>
                                </span>
                                <p class="description">
                                    <?php esc_html_e('Debug logging is enabled in wpFieldFlow settings. Quiz logs will be written to wpFieldFlow log files.', 'wp-quiz-flow'); ?>
                                </p>
                            <?php else: ?>
                                <span style="color: #d63638;">
                                    <span class="dashicons dashicons-dismiss"></span>
                                    <?php esc_html_e('Disabled', 'wp-quiz-flow'); ?>
                                </span>
                                <p class="description">
                                    <?php esc_html_e('Debug logging is disabled in wpFieldFlow settings. Enable it in', 'wp-quiz-flow'); ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-field-flow-settings')); ?>">
                                        <?php esc_html_e('wpFieldFlow Settings', 'wp-quiz-flow'); ?>
                                    </a>.
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            <?php else: ?>
                <div class="notice notice-warning">
                    <p>
                        <?php esc_html_e('wpFieldFlow debug system is not available. Logging will fall back to WordPress debug log when WP_DEBUG is enabled.', 'wp-quiz-flow'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <p class="submit">
            <input type="submit" 
                   name="wp_quiz_flow_save_settings" 
                   class="button button-primary" 
                   value="<?php esc_attr_e('Save Settings', 'wp-quiz-flow'); ?>" />
        </p>
    </form>
</div>

