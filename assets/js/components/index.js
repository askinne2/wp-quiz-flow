/**
 * wpQuizFlow Components Index
 * 
 * Exports all React components for bundling
 * Components are exposed to window for global access
 * 
 * @package WpQuizFlow
 * @since 1.0.0
 */

// Access React directly from window (provided by wpFieldFlow)
// Don't import to avoid externals complexity
const React = window.React;
const ReactDOM = window.ReactDOM;

if (!React || !ReactDOM) {
    throw new Error('React not available - ensure wpFieldFlow is loaded first');
}

import QuizNavigator from './QuizNavigator.jsx';

// Export as ES6 modules (for bundling)
export { QuizNavigator };
export default QuizNavigator;

// Expose to window for global access (WordPress-style)
if (typeof window !== 'undefined') {
    // Expose components
    window.QuizNavigator = QuizNavigator;
    
    // Create a namespace for all components
    window.wpQuizFlowComponents = window.wpQuizFlowComponents || {};
    window.wpQuizFlowComponents.QuizNavigator = QuizNavigator;
    
    console.log('wpQuizFlow: Components loaded and exposed to window');
}

