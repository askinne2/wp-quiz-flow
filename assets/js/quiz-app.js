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
        if (typeof window.QuizNavigator === 'undefined') {
            console.error('wpQuizFlow: QuizNavigator component not found');
            showError(container, 'Quiz component not loaded properly');
            return;
        }
        
        // Check if wpFieldFlow ResourceDirectory is available
        if (typeof window.ResourceDirectory === 'undefined') {
            console.error('wpQuizFlow: ResourceDirectory component not found (requires wpFieldFlow)');
            showError(container, 'wpFieldFlow plugin is required. Please install and activate wpFieldFlow.');
            return;
        }
        
        const component = window.QuizNavigator;
        const componentProps = {
            sheetId: appData.sheetId,
            quizId: appData.quiz_id || 'noma-quiz',
            config: appData.sheetConfig,
            layoutConfig: appData.layoutConfig || {},
            quizData: appData.quizData || null,
            atts: appData.shortcodeAtts || {},
            strings: appData.strings || {},
            ajaxUrl: appData.ajaxUrl,
            restUrl: appData.restUrl,
            nonce: appData.nonce
        };
        
        console.log('wpQuizFlow: Initializing QuizNavigator for sheet', appData.sheetId);

        // Create React element
        const element = React.createElement(component, componentProps);

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

