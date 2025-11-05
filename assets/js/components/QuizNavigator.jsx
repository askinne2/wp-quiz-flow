/**
 * Quiz Navigator Component
 * 
 * NOMA-style guided quiz with empathetic UX and tag-based resource filtering
 * Uses hardcoded question structure with branching logic (can load from JSON)
 * Maps quiz tags to WordPress taxonomies for resource filtering
 * 
 * @package WpQuizFlow
 * @since 1.0.0
 */

// Use React from window (provided by wpFieldFlow)
// Don't import React to avoid externals complexity with JSX transformation
// Instead, access it directly from window and let babel use it for JSX
const React = window.React;

if (!React) {
    throw new Error('React not available - ensure wpFieldFlow is loaded first');
}

// Get hooks from window.React
const { useState, useEffect, useMemo, useCallback } = React;

function QuizNavigator({ 
    sheetId, 
    config, 
    layoutConfig = {},
    atts = {}, 
    strings = {}, 
    ajaxUrl, 
    restUrl, 
    nonce,
    quizData = null,
    tagMapping = {}
}) {
    // State management (hooks are from window.React)
    const [currentNodeId, setCurrentNodeId] = useState('Q1');
    const [userPath, setUserPath] = useState([]);
    const [collectedTags, setCollectedTags] = useState([]);
    const [showResults, setShowResults] = useState(false);
    const [loading, setLoading] = useState(false);
    const [quizError, setQuizError] = useState(null);
    const [sessionId, setSessionId] = useState(null);
    
    // Configuration
    const showProgress = atts.show_progress !== 'false';
    const alwaysShowContact = atts.show_contact !== 'false';
    
    /**
     * Tag to Taxonomy Mapping
     * Received from PHP via wp_localize_script - single source of truth
     * Supports pattern matching for grouped tags (e.g., audience:* → audience_group)
     */
    const tagToTaxonomyMap = tagMapping || {};
    
    /**
     * Load quiz structure from quizData prop
     * Transforms JSON quiz structure to internal format
     */
    const quizStructure = useMemo(() => {
        if (!quizData) {
            setQuizError('Quiz data not loaded');
            return {};
        }
        
        if (!quizData.questions || typeof quizData.questions !== 'object') {
            setQuizError('Invalid quiz structure: missing questions');
            return {};
        }
        
        // Transform questions object to quiz structure format
        // The JSON structure already matches what we need
        return quizData.questions;
    }, [quizData]);
    
    // Validate quiz structure on load
    useEffect(() => {
        if (!quizData) {
            setQuizError('Error: Quiz data not available. Please check quiz configuration.');
            return;
        }
        
        // Validate required fields
        if (!quizData.quiz_id) {
            setQuizError('Error: Quiz ID missing from quiz data');
            return;
        }
        
        if (!quizData.questions || typeof quizData.questions !== 'object') {
            setQuizError('Error: Quiz questions structure is invalid');
            return;
        }
        
        // Validate that starting question exists
        if (!quizData.questions['Q1']) {
            setQuizError('Error: Starting question Q1 not found in quiz');
            return;
        }
        
        // Validate each question has required fields
        let hasErrors = false;
        Object.entries(quizData.questions).forEach(([qId, question]) => {
            if (!question.type || !question.text || !Array.isArray(question.options)) {
                console.error(`wpQuizFlow: Invalid question structure for ${qId}`, question);
                hasErrors = true;
            }
            
            question.options?.forEach((option, optIndex) => {
                if (!option.id || !option.text || !option.next) {
                    console.error(`wpQuizFlow: Invalid option structure for ${qId}[${optIndex}]`, option);
                    hasErrors = true;
                }
            });
        });
        
        if (hasErrors) {
            setQuizError('Error: Quiz structure validation failed. Check console for details.');
        } else {
            setQuizError(null);
        }
    }, [quizData]);
    
    // Get current node
    const currentNode = quizStructure[currentNodeId];
    
    // Calculate progress (rough estimate)
    const estimatedSteps = 3;
    const progress = (userPath.length / estimatedSteps) * 100;
    
    /**
     * Convert collected tags to taxonomy filters
     * Supports explicit tag mappings and pattern-based grouping (e.g., audience:* → audience_group)
     */
    const buildTaxonomyFilters = useCallback(() => {
        const filters = {
            resource_category: [],
            resource_tags: []
        };
        
        collectedTags.forEach(tag => {
            // First try exact match
            let mapping = tagToTaxonomyMap[tag];
            
            // If no exact match, try pattern matching for grouped tags
            if (!mapping && tag.includes(':')) {
                const [prefix] = tag.split(':');
                const patternKey = `${prefix}:*`;
                mapping = tagToTaxonomyMap[patternKey];
            }
            
            if (mapping) {
                Object.entries(mapping).forEach(([taxonomy, terms]) => {
                    if (Array.isArray(terms)) {
                        // Ensure filters[taxonomy] exists
                        if (!filters[taxonomy]) {
                            filters[taxonomy] = [];
                        }
                        filters[taxonomy] = [...new Set([...filters[taxonomy], ...terms])];
                    }
                });
            }
        });
        
        // Remove empty arrays
        Object.keys(filters).forEach(key => {
            if (filters[key].length === 0) {
                delete filters[key];
            }
        });
        
        return filters;
    }, [collectedTags, tagToTaxonomyMap]);
    
    /**
     * Handle answer selection
     */
    const handleAnswer = useCallback((option) => {
        // Add to path
        const newPath = [...userPath, {
            nodeId: currentNodeId,
            optionId: option.id,
            optionText: option.text
        }];
        setUserPath(newPath);
        
        // Collect tags
        if (option.tags) {
            setCollectedTags(prev => [...prev, ...option.tags]);
        }
        
        // Track answer
        if (sessionId && ajaxUrl && nonce) {
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wp_quiz_flow_track_session',
                    track_action: 'answer',
                    session_id: sessionId,
                    node_id: currentNodeId,
                    option_id: option.id,
                    option_text: option.text,
                    tags: JSON.stringify(option.tags || []),
                    nonce: nonce
                })
            })
            .catch(error => {
                console.error('wpQuizFlow: Failed to track answer', error);
            });
            
            // Track Google Analytics event
            if (typeof gtag !== 'undefined') {
                gtag('event', 'quiz_answer', {
                    'quiz_id': quizData?.quiz_id,
                    'node_id': currentNodeId,
                    'option_id': option.id
                });
            }
        }
        
        // Log for debugging
        console.log('wpQuizFlow: Quiz answer selected', {
            option: option.text,
            tags: option.tags,
            next: option.next,
            allTags: [...collectedTags, ...(option.tags || [])]
        });
        
        // Navigate to next node
        if (option.next === 'RESULTS') {
            setShowResults(true);
        } else {
            setCurrentNodeId(option.next);
        }
    }, [currentNodeId, userPath, collectedTags]);
    
    /**
     * Navigate back
     */
    const handleBack = useCallback(() => {
        if (userPath.length === 0) return;
        
        // Remove last path item
        const newPath = userPath.slice(0, -1);
        setUserPath(newPath);
        
        // Go to previous node
        const previousNode = newPath.length > 0 
            ? newPath[newPath.length - 1].nodeId 
            : 'Q1';
        setCurrentNodeId(previousNode);
        
        // Recalculate tags
        const newTags = [];
        newPath.forEach(pathItem => {
            const node = quizStructure[pathItem.nodeId];
            const option = node?.options?.find(opt => opt.id === pathItem.optionId);
            if (option?.tags) {
                newTags.push(...option.tags);
            }
        });
        setCollectedTags(newTags);
    }, [userPath, quizStructure]);
    
    /**
     * Restart quiz
     */
    const handleRestart = useCallback(() => {
        setCurrentNodeId('Q1');
        setUserPath([]);
        setCollectedTags([]);
        setShowResults(false);
    }, []);
    
    // Initialize session tracking
    useEffect(() => {
        if (!quizData || !quizData.quiz_id) {
            return;
        }
        
        // Start tracking session
        if (ajaxUrl && nonce) {
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wp_quiz_flow_track_session',
                    track_action: 'start',
                    quiz_id: quizData.quiz_id,
                    nonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.session_id) {
                    setSessionId(data.data.session_id);
                    
                    // Track Google Analytics event
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'quiz_start', {
                            'quiz_id': quizData.quiz_id,
                            'session_id': data.data.session_id
                        });
                    }
                    
                    // Track Facebook Pixel event
                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'QuizStart', {
                            quiz_id: quizData.quiz_id
                        });
                    }
                }
            })
            .catch(error => {
                console.error('wpQuizFlow: Failed to start session tracking', error);
            });
        }
        
        // Debug logging
        console.log('wpQuizFlow: QuizNavigator initialized', {
            sheetId,
            currentNode: currentNodeId,
            collectedTags,
            quizId: quizData?.quiz_id
        });
    }, [quizData]);
    
    // Loading state
    if (loading) {
        return React.createElement('div', {
            className: 'wp-field-flow-quiz-container'
        }, React.createElement(
            (window.wpFieldFlowComponents?.LoadingSpinner || window.LoadingSpinner) || 'div',
            {
            message: 'Loading quiz...',
            size: 'large'
        }));
    }
    
    // Track completion when results are shown
    useEffect(() => {
        if (showResults && sessionId && ajaxUrl && nonce) {
            const taxonomyFilters = buildTaxonomyFilters();
            const resultLimit = parseInt(atts.result_limit || '12', 10);
            
            // Complete session tracking
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wp_quiz_flow_track_session',
                    track_action: 'complete',
                    session_id: sessionId,
                    result_count: resultLimit.toString(),
                    taxonomy_filters: JSON.stringify(taxonomyFilters),
                    nonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Track Google Analytics event
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'quiz_complete', {
                            'quiz_id': quizData?.quiz_id,
                            'session_id': sessionId,
                            'result_count': resultLimit,
                            'collected_tags': collectedTags.length
                        });
                    }
                    
                    // Track Facebook Pixel event
                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'QuizComplete', {
                            quiz_id: quizData?.quiz_id,
                            result_count: resultLimit
                        });
                    }
                }
            })
            .catch(error => {
                console.error('wpQuizFlow: Failed to complete session tracking', error);
            });
        }
    }, [showResults, sessionId, ajaxUrl, nonce, atts, collectedTags, quizData, buildTaxonomyFilters]);
    
    // Show results
    if (showResults) {
        const taxonomyFilters = buildTaxonomyFilters();
        
        console.log('wpQuizFlow: Showing quiz results', {
            collectedTags,
            taxonomyFilters
        });
        
        return React.createElement('div', {
            className: 'wp-field-flow-quiz-container'
        }, React.createElement('div', {
            className: 'wp-field-flow-quiz-results'
        }, [
            // Results header
            React.createElement('div', {
                key: 'results-header',
                className: 'wp-field-flow-quiz-results-header'
            }, [
                React.createElement('h2', { key: 'title' }, 
                    'Resources Matched To Your Needs'
                ),
                React.createElement('p', { key: 'subtitle' }, 
                    'Based on your answers, here are resources that can help. Use the filters and search below to refine your results.'
                ),
                React.createElement('div', {
                    key: 'actions',
                    className: 'wp-field-flow-quiz-results-actions'
                }, [
                    React.createElement('button', {
                        key: 'restart',
                        className: 'wp-field-flow-quiz-restart',
                        onClick: handleRestart,
                        'aria-label': 'Start quiz over'
                    }, '← Start Over'),
                    alwaysShowContact && React.createElement('a', {
                        key: 'contact',
                        className: 'wp-field-flow-quiz-contact',
                        href: 'tel:' + (atts.contact_number || '205-555-0100'),
                        'aria-label': 'Call for support'
                    }, '📞 Call NOMA Now'),
                    React.createElement('button', {
                        key: 'share',
                        className: 'wp-field-flow-quiz-share',
                        onClick: () => {
                            const shareData = {
                                title: 'Quiz Results',
                                text: 'I found resources that match my needs!',
                                url: window.location.href
                            };
                            
                            if (navigator.share) {
                                navigator.share(shareData).catch(() => {
                                    // Fallback to clipboard
                                    navigator.clipboard.writeText(window.location.href);
                                    alert('Link copied to clipboard!');
                                });
                            } else {
                                // Fallback to clipboard
                                navigator.clipboard.writeText(window.location.href);
                                alert('Link copied to clipboard!');
                            }
                        },
                        'aria-label': 'Share quiz results'
                    }, '📤 Share Results')
                ])
            ]),
            
            // Resource Directory with filters applied
            // Use wpFieldFlowComponents namespace (new bundled approach)
            (() => {
                const ResourceDirectory = window.wpFieldFlowComponents?.ResourceDirectory || window.ResourceDirectory;
                
                // Verify ResourceDirectory is a valid component (function or class)
                if (!ResourceDirectory || (typeof ResourceDirectory !== 'function' && typeof ResourceDirectory !== 'object')) {
                    console.error('wpQuizFlow: ResourceDirectory component is invalid', {
                        ResourceDirectory,
                        wpFieldFlowComponents: window.wpFieldFlowComponents,
                        windowResourceDirectory: window.ResourceDirectory
                    });
                    return React.createElement('div', {
                        key: 'directory-error',
                        className: 'wp-quiz-flow-error'
                    }, 'Error: Resource directory component not available');
                }
                
                return React.createElement(
                    ResourceDirectory,
                    {
                        key: 'directory',
                        sheetId,
                        config,
                        layoutConfig,
                        atts: {
                            ...atts,
                            show_search: 'true',
                            show_filters: 'true',
                            show_title: 'false', // Don't duplicate title
                            limit: atts.result_limit || '12' // Use shortcode limit or default to 12
                        },
                        strings,
                        ajaxUrl,
                        restUrl,
                        nonce,
                        taxonomyFilters: {
                            include: taxonomyFilters,
                            exclude: {},
                            relation: 'OR' // Match any of the tags
                        }
                    }
                );
            })()
        ]));
    }
    
    // Show error if quiz failed to load or validate
    if (quizError) {
        return React.createElement('div', {
            className: 'wp-field-flow-quiz-error'
        }, [
            React.createElement('h3', { key: 'title' }, 'Error Loading Quiz'),
            React.createElement('p', { key: 'message' }, quizError),
            React.createElement('button', {
                key: 'retry',
                className: 'wp-field-flow-quiz-button wp-field-flow-quiz-button-primary',
                onClick: () => window.location.reload()
            }, strings.retry || 'Retry')
        ]);
    }
    
    // Show question
    if (!currentNode) {
        return React.createElement('div', {
            className: 'wp-field-flow-quiz-error'
        }, 'Quiz navigation error: Question not found');
    }
    
    // Main quiz render
    return React.createElement('div', {
        className: 'wp-field-flow-quiz-container'
    }, React.createElement('div', {
        className: 'wp-field-flow-quiz'
    }, [
        // Progress bar
        showProgress && React.createElement('div', {
            key: 'progress',
            className: 'wp-field-flow-quiz-progress'
        }, React.createElement('div', {
            className: 'wp-field-flow-quiz-progress-bar'
        }, React.createElement('div', {
            className: 'wp-field-flow-quiz-progress-fill',
            style: { width: `${Math.min(progress, 100)}%` }
        }))),
        
        // Question content
        React.createElement('div', {
            key: 'question',
            className: 'wp-field-flow-quiz-step',
            role: 'region',
            'aria-live': 'polite'
        }, [
            React.createElement('h2', {
                key: 'text',
                className: 'wp-field-flow-quiz-question',
                id: 'current-question'
            }, currentNode.text),
            
            currentNode.subtitle && React.createElement('p', {
                key: 'subtitle',
                className: 'wp-field-flow-quiz-subtitle'
            }, currentNode.subtitle),
            
            // Options
            React.createElement('div', {
                key: 'options',
                className: 'wp-field-flow-quiz-options',
                role: 'radiogroup',
                'aria-label': currentNode.text
            }, currentNode.options.map((option, index) => 
                React.createElement('button', {
                    key: index,
                    className: `wp-field-flow-quiz-option ${option.priority ? 'priority-' + option.priority : ''}`,
                    onClick: () => handleAnswer(option),
                    onKeyDown: (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            handleAnswer(option);
                        }
                    },
                    'aria-label': option.text,
                    'aria-describedby': `option-${index}-description`,
                    tabIndex: 0
                }, [
                    option.emoji && React.createElement('span', {
                        key: 'emoji',
                        className: 'wp-field-flow-quiz-option-emoji',
                        'aria-hidden': 'true'
                    }, option.emoji),
                    React.createElement('span', {
                        key: 'text',
                        className: 'wp-field-flow-quiz-option-text',
                        id: `option-${index}-description`
                    }, option.text)
                ])
            ))
        ]),
        
        // Navigation
        React.createElement('div', {
            key: 'navigation',
            className: 'wp-field-flow-quiz-navigation'
        }, [
            userPath.length > 0 && React.createElement('button', {
                key: 'back',
                className: 'wp-field-flow-quiz-button wp-field-flow-quiz-button-secondary',
                onClick: handleBack,
                onKeyDown: (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        handleBack();
                    }
                },
                'aria-label': 'Go back to previous question',
                tabIndex: 0
            }, '← Back')
        ])
    ]));
}

// Export as default for module bundling
export default QuizNavigator;

// Make component available globally (for backward compatibility)
// Note: This is also handled in index.js, but kept here for safety
if (typeof window !== 'undefined') {
    window.QuizNavigator = QuizNavigator;
}

