<?php
/**
 * Quiz Logic Engine
 *
 * Handles advanced conditional logic for quiz branching
 *
 * @package WpQuizFlow\Quiz
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Quiz;

/**
 * Quiz Logic Class
 *
 * Evaluates conditional logic for quiz branching
 *
 * @since 1.0.0
 */
class QuizLogic
{
    /**
     * Evaluate condition
     *
     * @param array<string, mixed> $condition Condition structure
     * @param array<string, mixed> $userPath User's path through quiz
     * @param array<string> $collectedTags Collected tags
     * @return bool True if condition matches
     */
    public function evaluateCondition(array $condition, array $userPath, array $collectedTags): bool
    {
        if (!isset($condition['type'])) {
            return true; // No condition, always true
        }
        
        switch ($condition['type']) {
            case 'has_tag':
                return $this->evaluateHasTag($condition, $collectedTags);
                
            case 'has_tags_all':
                return $this->evaluateHasTagsAll($condition, $collectedTags);
                
            case 'has_tags_any':
                return $this->evaluateHasTagsAny($condition, $collectedTags);
                
            case 'path_contains':
                return $this->evaluatePathContains($condition, $userPath);
                
            case 'score_gte':
                return $this->evaluateScore($condition, $collectedTags, '>=');
                
            case 'score_lte':
                return $this->evaluateScore($condition, $collectedTags, '<=');
                
            default:
                return true; // Unknown condition type, default to true
        }
    }
    
    /**
     * Evaluate has_tag condition
     *
     * @param array<string, mixed> $condition Condition
     * @param array<string> $collectedTags Tags
     * @return bool
     */
    private function evaluateHasTag(array $condition, array $collectedTags): bool
    {
        $requiredTag = $condition['tag'] ?? '';
        return in_array($requiredTag, $collectedTags, true);
    }
    
    /**
     * Evaluate has_tags_all condition (AND)
     *
     * @param array<string, mixed> $condition Condition
     * @param array<string> $collectedTags Tags
     * @return bool
     */
    private function evaluateHasTagsAll(array $condition, array $collectedTags): bool
    {
        $requiredTags = $condition['tags'] ?? [];
        if (!is_array($requiredTags)) {
            return false;
        }
        
        foreach ($requiredTags as $tag) {
            if (!in_array($tag, $collectedTags, true)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Evaluate has_tags_any condition (OR)
     *
     * @param array<string, mixed> $condition Condition
     * @param array<string> $collectedTags Tags
     * @return bool
     */
    private function evaluateHasTagsAny(array $condition, array $collectedTags): bool
    {
        $requiredTags = $condition['tags'] ?? [];
        if (!is_array($requiredTags)) {
            return false;
        }
        
        foreach ($requiredTags as $tag) {
            if (in_array($tag, $collectedTags, true)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Evaluate path_contains condition
     *
     * @param array<string, mixed> $condition Condition
     * @param array<string, mixed> $userPath User path
     * @return bool
     */
    private function evaluatePathContains(array $condition, array $userPath): bool
    {
        $requiredNode = $condition['node_id'] ?? '';
        if (empty($requiredNode)) {
            return false;
        }
        
        foreach ($userPath as $pathItem) {
            if (isset($pathItem['node_id']) && $pathItem['node_id'] === $requiredNode) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Evaluate score condition
     *
     * @param array<string, mixed> $condition Condition
     * @param array<string> $collectedTags Tags
     * @param string $operator Operator (>= or <=)
     * @return bool
     */
    private function evaluateScore(array $condition, array $collectedTags, string $operator): bool
    {
        $threshold = (int) ($condition['threshold'] ?? 0);
        $scoreTags = $condition['score_tags'] ?? [];
        
        if (!is_array($scoreTags)) {
            return false;
        }
        
        $score = 0;
        foreach ($scoreTags as $tag => $points) {
            if (in_array($tag, $collectedTags, true)) {
                $score += (int) $points;
            }
        }
        
        if ($operator === '>=') {
            return $score >= $threshold;
        } elseif ($operator === '<=') {
            return $score <= $threshold;
        }
        
        return false;
    }
}

