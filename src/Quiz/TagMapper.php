<?php
/**
 * Tag Mapper
 *
 * Maps quiz tags to WordPress taxonomy filters
 *
 * @package WpQuizFlow\Quiz
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Quiz;

/**
 * Tag Mapper Class
 *
 * Handles conversion of quiz answer tags to WordPress taxonomy filters
 *
 * @since 1.0.0
 */
class TagMapper
{
    /**
     * Mapping configuration
     *
     * @var array<string, array<string, array<string>>>|null
     */
    private ?array $mapping = null;
    
    /**
     * Load tag mapping configuration
     *
     * @since 1.0.0
     * @return array<string, array<string, array<string>>> Tag mapping configuration
     */
    public function getMapping(): array
    {
        if ($this->mapping === null) {
            $this->mapping = $this->loadMapping();
        }
        
        return $this->mapping;
    }
    
    /**
     * Load mapping from JSON file
     *
     * @since 1.0.0
     * @return array<string, array<string, array<string>>>
     */
    private function loadMapping(): array
    {
        $mappingFile = WP_QUIZ_FLOW_PLUGIN_DIR . 'assets/json/tag-mapping.json';
        
        if (!file_exists($mappingFile)) {
            // Return default NOMA mapping if file doesn't exist
            return $this->getDefaultMapping();
        }
        
        $json = file_get_contents($mappingFile);
        if ($json === false) {
            return $this->getDefaultMapping();
        }
        
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return $this->getDefaultMapping();
        }
        
        // Extract mappings from JSON structure
        if (isset($data['mappings']) && is_array($data['mappings'])) {
            return $data['mappings'];
        }
        
        // Fallback if structure is different
        return $this->getDefaultMapping();
    }
    
    /**
     * Get default tag mapping (NOMA)
     *
     * @since 1.0.0
     * @return array<string, array<string, array<string>>>
     */
    private function getDefaultMapping(): array
    {
        return [
            // Audience tags
            'audience:self' => [
                'resource_tags' => ['for-people-in-recovery']
            ],
            'audience:family' => [
                'resource_tags' => ['for-families', 'for-parents-caregivers']
            ],
            'audience:parent' => [
                'resource_tags' => ['for-parents-caregivers']
            ],
            'audience:partner' => [
                'resource_tags' => ['for-families']
            ],
            'audience:professional' => [
                'resource_tags' => ['helpful-articles', 'evidence-based']
            ],
            
            // Stage tags
            'stage:crisis' => [
                'resource_category' => ['help-with-treatment'],
                'resource_tags' => ['interventions', 'treatment']
            ],
            'stage:exploration' => [
                'resource_category' => ['literature'],
                'resource_tags' => ['helpful-articles', 'websites', 'downloadable-pdfs']
            ],
            'stage:active_treatment' => [
                'resource_category' => ['treatment-programs', 'help-with-treatment'],
                'resource_tags' => ['detox', 'residential', 'outpatient', 'treatment']
            ],
            'stage:recovery' => [
                'resource_category' => ['support-groups'],
                'resource_tags' => ['for-people-in-recovery', 'recovery-books']
            ],
            'stage:contemplation' => [
                'resource_category' => ['literature'],
                'resource_tags' => ['helpful-articles', 'interventions']
            ],
            
            // Need tags
            'need:immediate' => [
                'resource_category' => ['help-with-treatment'],
                'resource_tags' => ['interventions', 'treatment']
            ],
            'need:counseling' => [
                'resource_category' => ['treatment-programs'],
                'resource_tags' => ['outpatient', 'evidence-based']
            ],
            'need:education' => [
                'resource_category' => ['literature'],
                'resource_tags' => ['helpful-articles', 'websites', 'downloadable-pdfs', 'podcasts']
            ],
            'need:peer_support' => [
                'resource_category' => ['support-groups'],
                'resource_tags' => ['for-people-in-recovery', '12-step-based']
            ],
            'need:medical_detox' => [
                'resource_tags' => ['detox', 'opoid-treatment', 'medically-assisted']
            ],
            'need:treatment_navigation' => [
                'resource_category' => ['help-with-treatment', 'treatment-programs'],
                'resource_tags' => ['treatment']
            ],
            'need:intervention' => [
                'resource_tags' => ['interventions', 'for-families']
            ],
            'need:grief' => [
                'resource_tags' => ['grief-support']
            ],
            'need:life_skills' => [
                'resource_category' => ['collegiate-recovery'],
                'resource_tags' => ['recovery-residence', 'sober-living', 'extended-care']
            ]
        ];
    }
    
    /**
     * Map quiz tags to taxonomy filters
     *
     * @param array<string> $tags Quiz tags to map
     * @return array<string, array<string>> Taxonomy filters
     */
    public function mapTagsToFilters(array $tags): array
    {
        $mapping = $this->getMapping();
        $filters = [
            'resource_category' => [],
            'resource_tags' => []
        ];
        
        foreach ($tags as $tag) {
            if (isset($mapping[$tag])) {
                foreach ($mapping[$tag] as $taxonomy => $terms) {
                    if (is_array($terms)) {
                        $filters[$taxonomy] = array_merge(
                            $filters[$taxonomy],
                            $terms
                        );
                    }
                }
            }
        }
        
        // Remove duplicates
        foreach ($filters as $taxonomy => $terms) {
            $filters[$taxonomy] = array_unique($terms);
            $filters[$taxonomy] = array_values($filters[$taxonomy]);
        }
        
        // Remove empty arrays
        return array_filter($filters, function ($terms) {
            return !empty($terms);
        });
    }
}

