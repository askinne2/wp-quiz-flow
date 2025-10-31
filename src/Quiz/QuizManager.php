<?php
/**
 * Quiz Manager
 *
 * Manages quiz logic and state
 *
 * @package WpQuizFlow\Quiz
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\Quiz;

/**
 * Quiz Manager Class
 *
 * Handles quiz operations and state management
 *
 * @since 1.0.0
 */
class QuizManager
{
    /**
     * Tag Mapper instance
     *
     * @var TagMapper
     */
    private TagMapper $tagMapper;
    
    /**
     * Quiz Data loader instance
     *
     * @var QuizData
     */
    private QuizData $quizData;
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->tagMapper = new TagMapper();
        $this->quizData = new QuizData();
    }
    
    /**
     * Get quiz data by ID
     *
     * @param string $quizId Quiz identifier
     * @return array<string, mixed>|null Quiz structure or null
     */
    public function getQuizData(string $quizId): ?array
    {
        return $this->quizData->loadQuiz($quizId);
    }
    
    /**
     * Map quiz tags to taxonomy filters
     *
     * @param array<string> $tags Quiz tags
     * @return array<string, array<string>> Taxonomy filters
     */
    public function mapTagsToFilters(array $tags): array
    {
        return $this->tagMapper->mapTagsToFilters($tags);
    }
    
    /**
     * Get Tag Mapper instance
     *
     * @since 1.0.0
     * @return TagMapper
     */
    public function getTagMapper(): TagMapper
    {
        return $this->tagMapper;
    }
    
    /**
     * Get Quiz Data loader instance
     *
     * @since 1.0.0
     * @return QuizData
     */
    public function getQuizDataLoader(): QuizData
    {
        return $this->quizData;
    }
    
    /**
     * Get all available quizzes
     *
     * @since 1.0.0
     * @return array<string, array<string, mixed>>
     */
    public function getAvailableQuizzes(): array
    {
        return $this->quizData->getAvailableQuizzes();
    }
}

