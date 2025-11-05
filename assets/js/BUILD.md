# wpQuizFlow JavaScript Build System

## Overview

wpQuizFlow uses **webpack** to bundle React components for the quiz frontend. All JavaScript assets are compiled, minified, and optimized through a unified build process, following modern development best practices.

## Architecture

### Entry Points

- **`quiz-app.js`** → `dist/quiz-app.min.js` (Frontend: App initialization)
- **`components/index.js`** → `dist/quiz-components.min.js` (Frontend: All React components + React)

### Component Bundling

All React components are exported via `components/index.js` and bundled together, including:
- QuizNavigator (main quiz component)

Components are exposed via:
- ES6 exports (for bundling)
- `window.wpQuizFlowComponents` (for global access)
- Individual `window.*` assignments (backwards compatibility)

## Build Commands

### Development (Watch Mode)
```bash
cd assets/js
npm install
npm run dev
```
- Watches for file changes
- Auto-rebuilds on save
- Fast builds with source maps
- Perfect for active development

### Production Build
```bash
cd assets/js
npm install
npm run build
```
- Optimized, minified output
- Source maps for debugging
- Best for deployment

## Build Output

All built files are output to `assets/js/dist/`:

```
dist/
├── quiz-app.min.js             # Frontend: App init
├── quiz-app.min.js.map         # Source map
├── quiz-components.min.js      # Frontend: All components + React
└── quiz-components.min.js.map  # Source map
```

## How It Works

### Frontend Loading Flow

1. **PHP enqueues** `wp-field-flow-frontend-components.min.js` (wpFieldFlow components - required dependency)
2. **PHP enqueues** `quiz-components.min.js` (includes React + QuizNavigator component)
3. **PHP enqueues** `quiz-app.min.js` (depends on components bundle)
4. **Browser loads** wpFieldFlow components → exposes `window.wpFieldFlowComponents`
5. **Browser loads** quiz components → exposes `window.wpQuizFlowComponents` and `window.React`
6. **Browser loads** app init → finds components on `window`, initializes React app

### Why This Approach?

- ✅ **Single bundle** = 1 HTTP request instead of multiple
- ✅ **Uses wpFieldFlow's React** (external dependency) - prevents duplicate React instances
- ✅ **Minified & optimized** for performance
- ✅ **Proper caching** via version-based cache busting
- ✅ **Source maps** for debugging in production
- ✅ **Follows wpFieldFlow patterns** for consistency
- ✅ **No React conflicts** - uses same React instance as wpFieldFlow

## Dependencies

### wpFieldFlow Components

wpQuizFlow **requires** wpFieldFlow's frontend components to be loaded first because:
- QuizNavigator uses `ResourceDirectory` component from wpFieldFlow
- QuizNavigator uses `LoadingSpinner` component from wpFieldFlow
- Both plugins **must** share the same React instance to prevent React errors

### React Bundling

wpQuizFlow **bundles** React and ReactDOM (not externalized), meaning:
- React IS bundled with wpQuizFlow components
- This avoids the complexity of externals with named imports (hooks)
- Modern React (18+) can handle multiple instances if they're the same version
- Both plugins use React 18.2.0, so they're compatible
- React is exposed to `window.React` for compatibility

### Loading Order

The PHP enqueues scripts in this order:
1. `wp-field-flow-frontend-components` (wpFieldFlow)
2. `wp-quiz-flow-components` (wpQuizFlow) - depends on wpFieldFlow
3. `wp-quiz-flow-app` (wpQuizFlow) - depends on components

## Development Workflow

1. Make changes to `.jsx` component files
2. Run `npm run dev` in `assets/js/` (auto-rebuilds)
3. Refresh browser (hard refresh: Cmd+Shift+R / Ctrl+Shift+R)
4. Test changes
5. When ready, run `npm run build` for production

## Important Notes

- **Always rebuild after changing JSX files**
- **CSS changes** don't require rebuild (they're separate)
- **Version constant** (`WP_QUIZ_FLOW_VERSION`) handles cache busting
- **Components must export** as ES6 defaults AND expose to window for compatibility
- **wpFieldFlow must be active** for components to load properly

## Troubleshooting

### Components not loading?
1. Check browser console for errors
2. Verify `dist/` files exist and are up-to-date
3. Hard refresh browser cache
4. Check Network tab to see if files are loading
5. Verify wpFieldFlow is active and components are loaded

### Build errors?
1. Ensure all dependencies installed: `npm install`
2. Check Node.js version (should be 16+)
3. Verify all components export properly
4. Check webpack config for syntax errors

### Changes not appearing?
1. Rebuild: `npm run build`
2. Clear browser cache
3. Check file modification times in `dist/`
4. Verify PHP is loading files from `dist/` not `components/`

### QuizNavigator not found?
1. Verify `quiz-components.min.js` is loaded (check Network tab)
2. Check that `window.wpQuizFlowComponents` exists in console
3. Verify wpFieldFlow components are loaded first
4. Check for JavaScript errors that might prevent component loading

## File Structure

```
assets/js/
├── components/
│   ├── index.js              # Component exports
│   └── QuizNavigator.jsx     # Main quiz component
├── dist/                     # Build output (gitignored)
│   ├── quiz-app.min.js
│   ├── quiz-app.min.js.map
│   ├── quiz-components.min.js
│   └── quiz-components.min.js.map
├── quiz-app.js              # Frontend entry point
├── webpack.config.js        # Build configuration
└── package.json             # Dependencies & scripts
```

## Integration with wpFieldFlow

wpQuizFlow is designed as an **add-on** to wpFieldFlow:

- **Reuses** wpFieldFlow's `ResourceDirectory` component for displaying results
- **Reuses** wpFieldFlow's `LoadingSpinner` component
- **Requires** wpFieldFlow's frontend components bundle to be loaded first
- **Extends** wpFieldFlow's functionality with quiz-driven filtering

This design ensures:
- ✅ No code duplication
- ✅ Consistent UI/UX across both plugins
- ✅ Shared React instance (better performance)
- ✅ Easier maintenance

