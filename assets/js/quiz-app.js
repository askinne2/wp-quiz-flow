/**
 * wpQuizFlow Frontend App
 * 
 * Initializes React QuizNavigator component for quiz shortcode containers
 * 
 * @package WpQuizFlow
 * @since 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // Wait for React and components to be loaded
    if (typeof React === 'undefined' || typeof ReactDOM === 'undefined') {
        console.error('wpQuizFlow: React not loaded');
        return;
    }

    // Find all wpQuizFlow containers
    const containers = document.querySelectorAll('.wp-quiz-flow-container');
    
    if (containers.length === 0) {
        return; // No containers found
    }

    containers.forEach(container => {
        const sheetId = container.getAttribute('data-sheet-id');
        const configData = container.getAttribute('data-config');
        
        if (!sheetId) {
            console.error('wpQuizFlow: No sheet ID found for container', container);
            return;
        }

        let config;
        try {
            config = JSON.parse(configData);
        } catch (e) {
            console.error('wpQuizFlow: Invalid config data', e);
            return;
        }

        // Merge with global data
        const appData = {
            ...window.wpQuizFlowData,
            sheetId: sheetId,
            containerId: container.id,
            ...config
        };

        // Initialize React app
        initializeQuizApp(container, appData);
    });
});

/**
 * Initialize Quiz React app in container
 */
function initializeQuizApp(container, appData) {
    try {
        // Check if QuizNavigator is available
        // Try namespace first (bundled approach), then global fallback
        const QuizNavigator = window.wpQuizFlowComponents?.QuizNavigator || window.QuizNavigator;
        if (typeof QuizNavigator === 'undefined') {
            console.error('wpQuizFlow: QuizNavigator component not found - ensure quiz-components bundle is loaded');
            console.error('wpQuizFlow: Available components:', window.wpQuizFlowComponents);
            showError(container, 'Quiz component not loaded properly');
            return;
        }
        
        // Check if wpFieldFlow ResourceDirectory is available
        // wpFieldFlow now exposes components via window.wpFieldFlowComponents
        const ResourceDirectory = window.wpFieldFlowComponents?.ResourceDirectory || window.ResourceDirectory;
        if (typeof ResourceDirectory === 'undefined') {
            console.error('wpQuizFlow: ResourceDirectory component not found (requires wpFieldFlow)');
            console.error('wpQuizFlow: Available components:', window.wpFieldFlowComponents);
            showError(container, 'wpFieldFlow plugin is required. Please install and activate wpFieldFlow.');
            return;
        }
        
        const component = QuizNavigator;
        
        // Verify component is a valid React component
        if (typeof component !== 'function') {
            console.error('wpQuizFlow: QuizNavigator is not a function', {
                component,
                type: typeof component,
                QuizNavigator: window.QuizNavigator,
                wpQuizFlowComponents: window.wpQuizFlowComponents
            });
            showError(container, 'Quiz component is invalid');
            return;
        }
        
        const componentProps = {
            sheetId: appData.sheetId,
            quizId: appData.quiz_id || 'noma-quiz',
            config: appData.sheetConfig,
            layoutConfig: appData.layoutConfig || {},
            quizData: appData.quizData || null,
            tagMapping: appData.tagMapping || {},
            atts: appData.shortcodeAtts || {},
            strings: appData.strings || {},
            ajaxUrl: appData.ajaxUrl,
            restUrl: appData.restUrl,
            nonce: appData.nonce
        };
        
        console.log('wpQuizFlow: Initializing QuizNavigator for sheet', appData.sheetId);
        console.log('wpQuizFlow: Component props', {
            sheetId: componentProps.sheetId,
            quizId: componentProps.quizId,
            hasConfig: !!componentProps.config,
            hasQuizData: !!componentProps.quizData,
            hasTagMapping: !!componentProps.tagMapping,
            React: typeof React,
            ReactDOM: typeof ReactDOM,
            component: typeof component
        });

        // Verify React is available
        if (!React || !ReactDOM) {
            console.error('wpQuizFlow: React or ReactDOM not available', {
                React: typeof React,
                ReactDOM: typeof ReactDOM,
                windowReact: typeof window.React,
                windowReactDOM: typeof window.ReactDOM
            });
            showError(container, 'React is not available');
            return;
        }

        // Create React element
        const element = React.createElement(component, componentProps);
        
        // Verify element is valid
        if (!element || typeof element !== 'object') {
            console.error('wpQuizFlow: Invalid React element created', {
                element,
                type: typeof element,
                component,
                props: componentProps
            });
            showError(container, 'Failed to create React element');
            return;
        }

        // Render React component
        const root = ReactDOM.createRoot(container);
        root.render(element);

        console.log('wpQuizFlow: React quiz app initialized', { sheetId: appData.sheetId });

    } catch (error) {
        console.error('wpQuizFlow: Failed to initialize quiz app', error);
        showError(container, 'Failed to load quiz. Please refresh the page.');
    }
}

/**
 * Show error message in container
 */
function showError(container, message) {
    container.innerHTML = `
        <div class="wp-quiz-flow-error">
            <h3>Error Loading Quiz</h3>
            <p>${message}</p>
            <button onclick="location.reload()" class="wp-quiz-flow-button wp-quiz-flow-button-primary">
                ${window.wpQuizFlowData?.strings?.retry || 'Retry'}
            </button>
        </div>
    `;
}

/**
 * Fallback for browsers without React support
 */
if (typeof React === 'undefined') {
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.wp-quiz-flow-container');
        containers.forEach(container => {
            const noscriptContent = container.querySelector('noscript');
            if (noscriptContent) {
                container.innerHTML = noscriptContent.innerHTML;
            }
        });
    });
}

