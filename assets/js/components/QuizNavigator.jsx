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

function QuizNavigator({ 
    sheetId, 
    config, 
    layoutConfig = {},
    atts = {}, 
    strings = {}, 
    ajaxUrl, 
    restUrl, 
    nonce 
}) {
    const { useState, useEffect, useMemo, useCallback } = React;
    
    // State management
    const [currentNodeId, setCurrentNodeId] = useState('Q1');
    const [userPath, setUserPath] = useState([]);
    const [collectedTags, setCollectedTags] = useState([]);
    const [showResults, setShowResults] = useState(false);
    const [loading, setLoading] = useState(false);
    
    // Configuration
    const showProgress = atts.show_progress !== 'false';
    const alwaysShowContact = atts.show_contact !== 'false';
    
    /**
     * Tag to Taxonomy Mapping
     * Translates quiz tags to WordPress taxonomy filters
     * Updated with actual NOMA taxonomy terms (Oct 31, 2025)
     */
    const tagToTaxonomyMap = {
        // Audience tags → resource_tags
        'audience:self': { 
            resource_tags: ['for-people-in-recovery'] 
        },
        'audience:family': { 
            resource_tags: ['for-families', 'for-parents-caregivers'] 
        },
        'audience:parent': { 
            resource_tags: ['for-parents-caregivers'] 
        },
        'audience:partner': { 
            resource_tags: ['for-families'] 
        },
        'audience:professional': { 
            resource_tags: ['helpful-articles', 'evidence-based'] 
        },
        
        // Stage tags → resource_category + resource_tags
        'stage:crisis': { 
            resource_category: ['help-with-treatment'],
            resource_tags: ['interventions', 'treatment'] 
        },
        'stage:exploration': { 
            resource_category: ['literature'],
            resource_tags: ['helpful-articles', 'websites', 'downloadable-pdfs'] 
        },
        'stage:active_treatment': { 
            resource_category: ['treatment-programs', 'help-with-treatment'],
            resource_tags: ['detox', 'residential', 'outpatient', 'treatment'] 
        },
        'stage:recovery': { 
            resource_category: ['support-groups'],
            resource_tags: ['for-people-in-recovery', 'recovery-books'] 
        },
        'stage:contemplation': { 
            resource_category: ['literature'],
            resource_tags: ['helpful-articles', 'interventions'] 
        },
        
        // Need tags → resource_category + resource_tags
        'need:immediate': { 
            resource_category: ['help-with-treatment'],
            resource_tags: ['interventions', 'treatment'] 
        },
        'need:counseling': { 
            resource_category: ['treatment-programs'],
            resource_tags: ['outpatient', 'evidence-based'] 
        },
        'need:education': { 
            resource_category: ['literature'],
            resource_tags: ['helpful-articles', 'websites', 'downloadable-pdfs', 'podcasts'] 
        },
        'need:peer_support': { 
            resource_category: ['support-groups'],
            resource_tags: ['for-people-in-recovery', '12-step-based'] 
        },
        'need:medical_detox': { 
            resource_tags: ['detox', 'opoid-treatment', 'medically-assisted'] 
        },
        'need:treatment_navigation': { 
            resource_category: ['help-with-treatment', 'treatment-programs'],
            resource_tags: ['treatment'] 
        },
        'need:intervention': { 
            resource_tags: ['interventions', 'for-families'] 
        },
        'need:grief': { 
            resource_tags: ['grief-support'] 
        },
        'need:life_skills': { 
            resource_category: ['collegiate-recovery'],
            resource_tags: ['recovery-residence', 'sober-living', 'extended-care'] 
        }
    };
    
    /**
     * NOMA Quiz Structure (Simplified for MVP)
     * Full structure available in docs-roadmap/example-quiz.md
     */
    const quizStructure = {
        'Q1': {
            type: 'question',
            text: "Let's help you find the right resources. Who are you looking to support?",
            subtitle: "We're here to help",
            options: [
                {
                    id: 'A',
                    text: 'Myself',
                    next: 'Q2-SELF',
                    tags: ['audience:self'],
                    emoji: '🙋'
                },
                {
                    id: 'B',
                    text: 'Someone I care about',
                    next: 'Q2-FAMILY',
                    tags: ['audience:family'],
                    emoji: '👨‍👩‍👧'
                },
                {
                    id: 'C',
                    text: "I'm a professional seeking resources",
                    next: 'RESULTS',
                    tags: ['audience:professional'],
                    emoji: '💼'
                }
            ]
        },
        
        // Path A: Supporting Myself
        'Q2-SELF': {
            type: 'question',
            text: "Thank you for reaching out. Where are you in your journey?",
            subtitle: "There's no wrong answer",
            options: [
                {
                    id: 'A1',
                    text: "I'm in crisis and need immediate help",
                    next: 'RESULTS',
                    tags: ['stage:crisis', 'need:immediate'],
                    priority: 'urgent',
                    emoji: '🚨'
                },
                {
                    id: 'A2',
                    text: "I'm considering making a change",
                    next: 'Q3-CONSIDERING',
                    tags: ['stage:contemplation'],
                    emoji: '🤔'
                },
                {
                    id: 'A3',
                    text: "I'm in recovery and looking for support",
                    next: 'Q3-RECOVERY',
                    tags: ['stage:recovery'],
                    emoji: '🌱'
                }
            ]
        },
        
        'Q3-CONSIDERING': {
            type: 'question',
            text: "What feels like the right next step for you?",
            subtitle: "Take your time",
            options: [
                {
                    id: 'B1',
                    text: 'I want to understand my options',
                    next: 'RESULTS',
                    tags: ['stage:exploration', 'need:education'],
                    emoji: '📚'
                },
                {
                    id: 'B2',
                    text: "I'm ready to talk to someone",
                    next: 'RESULTS',
                    tags: ['stage:contemplation', 'need:counseling'],
                    emoji: '💬'
                },
                {
                    id: 'B3',
                    text: 'I need medical help to stop safely',
                    next: 'RESULTS',
                    tags: ['stage:active_treatment', 'need:medical_detox'],
                    priority: 'high',
                    emoji: '🏥'
                }
            ]
        },
        
        'Q3-RECOVERY': {
            type: 'question',
            text: "That's wonderful. What kind of support would help you most?",
            subtitle: "We're proud of you",
            options: [
                {
                    id: 'C1',
                    text: 'Connection with others in recovery',
                    next: 'RESULTS',
                    tags: ['stage:recovery', 'need:peer_support'],
                    emoji: '🤝'
                },
                {
                    id: 'C2',
                    text: 'Professional counseling or therapy',
                    next: 'RESULTS',
                    tags: ['stage:recovery', 'need:counseling'],
                    emoji: '🧠'
                },
                {
                    id: 'C3',
                    text: 'Practical life support (housing, job, etc.)',
                    next: 'RESULTS',
                    tags: ['stage:recovery', 'need:life_skills'],
                    emoji: '🏠'
                }
            ]
        },
        
        // Path B: Supporting Family
        'Q2-FAMILY': {
            type: 'question',
            text: "Your care and concern matter. What's your most pressing need right now?",
            subtitle: "You're not alone in this",
            options: [
                {
                    id: 'D1',
                    text: 'Understanding what\'s happening',
                    next: 'RESULTS',
                    tags: ['stage:exploration', 'need:education'],
                    emoji: '📖'
                },
                {
                    id: 'D2',
                    text: 'Getting them into treatment',
                    next: 'Q3-TREATMENT',
                    tags: ['stage:active_treatment', 'need:treatment_navigation'],
                    emoji: '🎯'
                },
                {
                    id: 'D3',
                    text: 'Support for myself',
                    next: 'RESULTS',
                    tags: ['need:peer_support'],
                    emoji: '💚'
                }
            ]
        },
        
        'Q3-TREATMENT': {
            type: 'question',
            text: "Is your loved one open to getting help?",
            subtitle: "This will help us guide you better",
            options: [
                {
                    id: 'E1',
                    text: "Yes, they're ready",
                    next: 'RESULTS',
                    tags: ['stage:active_treatment', 'need:treatment_navigation'],
                    emoji: '✅'
                },
                {
                    id: 'E2',
                    text: "No, they're resistant",
                    next: 'RESULTS',
                    tags: ['stage:contemplation', 'need:intervention'],
                    emoji: '🛡️'
                },
                {
                    id: 'E3',
                    text: "I'm not sure",
                    next: 'RESULTS',
                    tags: ['stage:exploration', 'need:education'],
                    emoji: '❓'
                }
            ]
        }
    };
    
    // Get current node
    const currentNode = quizStructure[currentNodeId];
    
    // Calculate progress (rough estimate)
    const estimatedSteps = 3;
    const progress = (userPath.length / estimatedSteps) * 100;
    
    /**
     * Convert collected tags to taxonomy filters
     */
    const buildTaxonomyFilters = useCallback(() => {
        const filters = {
            resource_category: [],
            resource_tags: []
        };
        
        collectedTags.forEach(tag => {
            const mapping = tagToTaxonomyMap[tag];
            if (mapping) {
                Object.entries(mapping).forEach(([taxonomy, terms]) => {
                    if (Array.isArray(terms)) {
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
        
        // Log for debugging
        console.log('wpFieldFlow: Quiz answer selected', {
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
    
    // Debug logging
    useEffect(() => {
        console.log('wpFieldFlow: QuizNavigator initialized', {
            sheetId,
            currentNode: currentNodeId,
            collectedTags
        });
    }, []);
    
    // Loading state
    if (loading) {
        return React.createElement('div', {
            className: 'wp-field-flow-quiz-container'
        }, React.createElement(window.LoadingSpinner || 'div', {
            message: 'Loading quiz...',
            size: 'large'
        }));
    }
    
    // Show results
    if (showResults) {
        const taxonomyFilters = buildTaxonomyFilters();
        
        console.log('wpFieldFlow: Showing quiz results', {
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
                        onClick: handleRestart
                    }, '← Start Over'),
                    alwaysShowContact && React.createElement('a', {
                        key: 'contact',
                        className: 'wp-field-flow-quiz-contact',
                        href: 'tel:205-555-0100'
                    }, '📞 Call NOMA Now')
                ])
            ]),
            
            // Resource Directory with filters applied
            React.createElement(window.ResourceDirectory || 'div', {
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
            })
        ]));
    }
    
    // Show question
    if (!currentNode) {
        return React.createElement('div', {
            className: 'wp-field-flow-quiz-error'
        }, 'Quiz navigation error');
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
            className: 'wp-field-flow-quiz-step'
        }, [
            React.createElement('h2', {
                key: 'text',
                className: 'wp-field-flow-quiz-question'
            }, currentNode.text),
            
            currentNode.subtitle && React.createElement('p', {
                key: 'subtitle',
                className: 'wp-field-flow-quiz-subtitle'
            }, currentNode.subtitle),
            
            // Options
            React.createElement('div', {
                key: 'options',
                className: 'wp-field-flow-quiz-options'
            }, currentNode.options.map((option, index) => 
                React.createElement('button', {
                    key: index,
                    className: `wp-field-flow-quiz-option ${option.priority ? 'priority-' + option.priority : ''}`,
                    onClick: () => handleAnswer(option)
                }, [
                    option.emoji && React.createElement('span', {
                        key: 'emoji',
                        className: 'wp-field-flow-quiz-option-emoji'
                    }, option.emoji),
                    React.createElement('span', {
                        key: 'text',
                        className: 'wp-field-flow-quiz-option-text'
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
                onClick: handleBack
            }, '← Back')
        ])
    ]));
}

// Make component available globally
window.QuizNavigator = QuizNavigator;

