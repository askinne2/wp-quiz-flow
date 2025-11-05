<?php
/**
 * Quiz Validator
 *
 * Validates quiz JSON structure before use
 *
 * @package WpQuizFlow\Quiz
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Quiz;

/**
 * Quiz Validator Class
 *
 * Handles validation of quiz structures
 *
 * @since 1.0.0
 */
class QuizValidator
{
    /**
     * Validation errors
     *
     * @var array<string>|null
     */
    private ?array $errors = null;
    
    /**
     * Validate quiz structure
     *
     * @param array<string, mixed> $quiz Quiz structure to validate
     * @return bool True if valid, false otherwise
     */
    public function validate(array $quiz): bool
    {
        $this->errors = [];
        
        // Validate required top-level fields
        if (!isset($quiz['quiz_id']) || empty($quiz['quiz_id'])) {
            $this->errors[] = 'Quiz ID is required';
        }
        
        if (!isset($quiz['title']) || empty($quiz['title'])) {
            $this->errors[] = 'Quiz title is required';
        }
        
        if (!isset($quiz['questions']) || !is_array($quiz['questions'])) {
            $this->errors[] = 'Quiz questions are required and must be an array';
            return false; // Can't continue without questions
        }
        
        // Validate that starting question exists
        if (!isset($quiz['questions']['Q1'])) {
            $this->errors[] = 'Starting question Q1 is required';
        }
        
        // Validate each question
        foreach ($quiz['questions'] as $questionId => $question) {
            $this->validateQuestion($questionId, $question);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validate a single question
     *
     * @param string $questionId Question identifier
     * @param mixed $question Question data
     * @return void
     */
    private function validateQuestion(string $questionId, $question): void
    {
        if (!is_array($question)) {
            $this->errors[] = "Question {$questionId}: Must be an array";
            return;
        }
        
        // Validate required fields
        if (!isset($question['type']) || empty($question['type'])) {
            $this->errors[] = "Question {$questionId}: Type is required";
        }
        
        if (!isset($question['text']) || empty($question['text'])) {
            $this->errors[] = "Question {$questionId}: Text is required";
        }
        
        if (!isset($question['options']) || !is_array($question['options'])) {
            $this->errors[] = "Question {$questionId}: Options are required and must be an array";
            return; // Can't continue without options
        }
        
        if (empty($question['options'])) {
            $this->errors[] = "Question {$questionId}: Must have at least one option";
            return;
        }
        
        // Validate each option
        foreach ($question['options'] as $optionIndex => $option) {
            $this->validateOption($questionId, $optionIndex, $option);
        }
    }
    
    /**
     * Validate a single option
     *
     * @param string $questionId Question identifier
     * @param int $optionIndex Option index
     * @param mixed $option Option data
     * @return void
     */
    private function validateOption(string $questionId, int $optionIndex, $option): void
    {
        if (!is_array($option)) {
            $this->errors[] = "Question {$questionId}, Option {$optionIndex}: Must be an array";
            return;
        }
        
        // Validate required fields
        if (!isset($option['id']) || empty($option['id'])) {
            $this->errors[] = "Question {$questionId}, Option {$optionIndex}: ID is required";
        }
        
        if (!isset($option['text']) || empty($option['text'])) {
            $this->errors[] = "Question {$questionId}, Option {$optionIndex}: Text is required";
        }
        
        if (!isset($option['next']) || empty($option['next'])) {
            $this->errors[] = "Question {$questionId}, Option {$optionIndex}: Next node is required";
        }
        
        // Validate that 'next' points to a valid question or RESULTS
        if (isset($option['next']) && $option['next'] !== 'RESULTS') {
            // Note: We can't validate that the question exists here without the full structure
            // This will be caught during runtime
        }
        
        // Tags are optional but should be an array if present
        if (isset($option['tags']) && !is_array($option['tags'])) {
            $this->errors[] = "Question {$questionId}, Option {$optionIndex}: Tags must be an array";
        }
    }
    
    /**
     * Get validation errors
     *
     * @return array<string> Array of error messages
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }
    
    /**
     * Get formatted error message
     *
     * @return string Formatted error message
     */
    public function getErrorMessage(): string
    {
        if (empty($this->errors)) {
            return '';
        }
        
        return implode("\n", $this->errors);
    }
    
    /**
     * Check if quiz has errors
     *
     * @return bool True if has errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}

