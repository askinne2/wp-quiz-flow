<?php
/**
 * Quiz Translations Manager
 *
 * Handles quiz translations and multi-language support
 *
 * @package WpQuizFlow\i18n
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WpQuizFlow\i18n;

/**
 * Quiz Translations Class
 *
 * Manages quiz translations
 *
 * @since 1.0.0
 */
class QuizTranslations
{
    /**
     * Get translated quiz
     *
     * @param array<string, mixed> $quiz Quiz structure
     * @param string $locale Locale code (e.g., 'es_ES')
     * @return array<string, mixed> Translated quiz
     */
    public function getTranslatedQuiz(array $quiz, string $locale = ''): array
    {
        if (empty($locale)) {
            $locale = get_locale();
        }
        
        // If quiz has translations, use them
        if (isset($quiz['translations'][$locale])) {
            return $this->mergeTranslations($quiz, $quiz['translations'][$locale]);
        }
        
        // Otherwise, apply WordPress translation functions
        return $this->applyWordPressTranslations($quiz);
    }
    
    /**
     * Merge translations into quiz structure
     *
     * @param array<string, mixed> $baseQuiz Base quiz
     * @param array<string, mixed> $translations Translations
     * @return array<string, mixed> Translated quiz
     */
    private function mergeTranslations(array $baseQuiz, array $translations): array
    {
        $translatedQuiz = $baseQuiz;
        
        // Translate title
        if (isset($translations['title'])) {
            $translatedQuiz['title'] = $translations['title'];
        }
        
        // Translate description
        if (isset($translations['description'])) {
            $translatedQuiz['description'] = $translations['description'];
        }
        
        // Translate questions
        if (isset($translations['questions']) && is_array($translations['questions'])) {
            foreach ($translations['questions'] as $questionId => $questionTranslations) {
                if (isset($translatedQuiz['questions'][$questionId])) {
                    // Translate question text
                    if (isset($questionTranslations['text'])) {
                        $translatedQuiz['questions'][$questionId]['text'] = $questionTranslations['text'];
                    }
                    
                    // Translate subtitle
                    if (isset($questionTranslations['subtitle'])) {
                        $translatedQuiz['questions'][$questionId]['subtitle'] = $questionTranslations['subtitle'];
                    }
                    
                    // Translate options
                    if (isset($questionTranslations['options']) && is_array($questionTranslations['options'])) {
                        foreach ($questionTranslations['options'] as $optionId => $optionTranslation) {
                            if (isset($translatedQuiz['questions'][$questionId]['options'])) {
                                foreach ($translatedQuiz['questions'][$questionId]['options'] as $index => $option) {
                                    if ($option['id'] === $optionId && isset($optionTranslation['text'])) {
                                        $translatedQuiz['questions'][$questionId]['options'][$index]['text'] = $optionTranslation['text'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $translatedQuiz;
    }
    
    /**
     * Apply WordPress translation functions
     *
     * @param array<string, mixed> $quiz Quiz structure
     * @return array<string, mixed> Translated quiz
     */
    private function applyWordPressTranslations(array $quiz): array
    {
        // Basic translation support - can be extended
        if (isset($quiz['title'])) {
            $quiz['title'] = __($quiz['title'], 'wp-quiz-flow');
        }
        
        if (isset($quiz['description'])) {
            $quiz['description'] = __($quiz['description'], 'wp-quiz-flow');
        }
        
        return $quiz;
    }
}

