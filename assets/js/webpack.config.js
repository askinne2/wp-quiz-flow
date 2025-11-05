const path = require('path');

/**
 * Webpack Configuration for wpQuizFlow
 * 
 * Bundles React components for quiz frontend
 * Follows modern best practices: proper code splitting, source maps, and optimization
 * 
 * @package WpQuizFlow
 * @since 1.0.0
 */
module.exports = (env, argv) => {
    const isProduction = argv.mode === 'production';
    
    return {
        entry: {
            // Frontend: Main app initialization (uses bundled components)
            'quiz-app': './quiz-app.js',
            
            // Frontend: Components bundle (exposes QuizNavigator to window)
            'quiz-components': './components/index.js'
        },
        output: {
            path: path.resolve(__dirname, 'dist'),
            filename: '[name].min.js',
            clean: true,
            // Ensure proper global access
            globalObject: 'this',
            // Disable chunk loading entirely - WordPress loads scripts synchronously
            chunkLoading: false,
            chunkFormat: false,
        },
        module: {
            rules: [
                {
                    test: /\.(js|jsx)$/,
                    exclude: /node_modules/,
                            use: {
                                loader: 'babel-loader',
                                options: {
                                    presets: [
                                        '@babel/preset-env',
                                        ['@babel/preset-react', { runtime: 'classic' }]
                                    ]
                                }
                            }
                },
                {
                    test: /\.css$/,
                    use: ['style-loader', 'css-loader']
                }
            ]
        },
        resolve: {
            extensions: ['.js', '.jsx', '.json']
        },
        devtool: isProduction ? 'source-map' : 'eval-source-map',
        optimization: {
            minimize: isProduction,
            // Disable code splitting and chunk loading entirely
            // WordPress handles script dependencies synchronously, so we bundle everything
            splitChunks: false, // Completely disable code splitting
            runtimeChunk: false // Don't create a separate runtime chunk
        },
        // No externals needed - components access React directly from window
        // This avoids externals complexity with JSX transformation
        externals: {}
    };
};

