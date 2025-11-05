<?php
/**
 * Quiz Template Manager
 *
 * Manages quiz templates and template library
 *
 * @package WpQuizFlow\Templates
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Templates;

/**
 * Template Manager Class
 *
 * Handles quiz templates
 *
 * @since 1.0.0
 */
class TemplateManager
{
    /**
     * Get available templates
     *
     * @return array<string, array<string, mixed>> Available templates
     */
    public function getAvailableTemplates(): array
    {
        $templates = [];
        $templateDir = WP_QUIZ_FLOW_PLUGIN_DIR . 'templates/quiz-templates/';
        
        if (!is_dir($templateDir)) {
            return $templates;
        }
        
        $files = glob($templateDir . '*.json');
        if ($files === false) {
            return $templates;
        }
        
        foreach ($files as $file) {
            $json = file_get_contents($file);
            if ($json === false) {
                continue;
            }
            
            $template = json_decode($json, true);
            if (!is_array($template) || !isset($template['template_id'])) {
                continue;
            }
            
            $templates[$template['template_id']] = [
                'id' => $template['template_id'],
                'name' => $template['name'] ?? 'Untitled Template',
                'description' => $template['description'] ?? '',
                'category' => $template['category'] ?? 'general',
                'version' => $template['version'] ?? '1.0.0'
            ];
        }
        
        return $templates;
    }
    
    /**
     * Load template by ID
     *
     * @param string $templateId Template identifier
     * @return array<string, mixed>|null Template structure or null
     */
    public function loadTemplate(string $templateId): ?array
    {
        $templateFile = WP_QUIZ_FLOW_PLUGIN_DIR . 'templates/quiz-templates/' . sanitize_file_name($templateId) . '.json';
        
        if (!file_exists($templateFile)) {
            return null;
        }
        
        $json = file_get_contents($templateFile);
        if ($json === false) {
            return null;
        }
        
        $template = json_decode($json, true);
        if (!is_array($template)) {
            return null;
        }
        
        return $template;
    }
    
    /**
     * Create quiz from template
     *
     * @param string $templateId Template identifier
     * @param string $newQuizId New quiz ID
     * @param string $title New quiz title
     * @return array<string, mixed>|null Quiz structure or null
     */
    public function createQuizFromTemplate(string $templateId, string $newQuizId, string $title): ?array
    {
        $template = $this->loadTemplate($templateId);
        
        if (!$template) {
            return null;
        }
        
        // Clone template structure
        $quiz = $template['quiz_structure'] ?? $template;
        
        // Update quiz ID and title
        $quiz['quiz_id'] = $newQuizId;
        $quiz['title'] = $title;
        
        return $quiz;
    }
}

