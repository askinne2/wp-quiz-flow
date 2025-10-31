<?php
/**
 * PSR-4 Autoloader for wpQuizFlow
 *
 * @package WpQuizFlow
 * @since 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoloader for wpQuizFlow classes
 *
 * @param string $class The fully qualified class name
 * @return void
 */
spl_autoload_register(function (string $class): void {
    // Only handle wpQuizFlow namespace
    $prefix = 'WpQuizFlow\\';
    $baseDir = WP_QUIZ_FLOW_PLUGIN_DIR . 'src/';
    
    // Check if class uses the prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relativeClass = substr($class, $len);
    
    // Replace namespace separator with directory separator
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // If file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

