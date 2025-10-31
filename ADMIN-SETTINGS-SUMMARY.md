# wpQuizFlow Admin Settings Implementation

## Overview

wpQuizFlow now has a complete admin interface that integrates with wpFieldFlow's Debug system and follows the same architectural patterns.

## New Components

### 1. AdminMenu (`src/Admin/AdminMenu.php`)
- Manages all admin menu pages for wpQuizFlow
- Attempts to attach to wpFieldFlow's menu if available, otherwise creates its own
- Pages:
  - **Dashboard**: Overview of quizzes and quick stats
  - **Quizzes**: List of available quizzes with shortcode examples
  - **Settings**: Configure default quiz settings
  - **Usage Guide**: Display usage documentation

### 2. SettingsManager (`src/Admin/SettingsManager.php`)
- Handles all quiz settings
- Reuses wpFieldFlow's Debug system for logging (via `\WpFieldFlow\Core\Debug::log()`)
- Settings include:
  - Default quiz ID
  - Default contact number
  - Default result limit
  - Display options (show progress, show contact)
  - Debug logging status (linked to wpFieldFlow's debug mode)

### 3. Admin Templates (`templates/admin/`)
- `dashboard.php`: Main dashboard with stats and quick links
- `quizzes.php`: List of available quizzes
- `settings.php`: Settings form with wpFieldFlow Debug integration
- `usage.php`: Usage guide display

## Key Features

### Debug Integration
- wpQuizFlow **reuses** wpFieldFlow's Debug system entirely
- All logging calls check for `\WpFieldFlow\Core\Debug::log()` availability
- Falls back to WordPress `error_log()` if wpFieldFlow Debug is unavailable
- Settings page displays wpFieldFlow debug status and links to wpFieldFlow settings

### Reusability
- Follows the same patterns as wpFieldFlow's AdminMenu and SettingsManager
- Reuses wpFieldFlow's constants and utilities
- Integrates seamlessly with wpFieldFlow's admin interface

### Settings
- Stored in `wp_quiz_flow_settings` option
- Includes defaults for all settings
- Sanitization and validation built-in
- AJAX handlers for dynamic updates (future)

## Usage

### Accessing Admin Pages
1. **Dashboard**: `wp-admin/admin.php?page=wp-quiz-flow`
2. **Settings**: `wp-admin/admin.php?page=wp-quiz-flow-settings`
3. **Quizzes**: `wp-admin/admin.php?page=wp-quiz-flow-quizzes`
4. **Usage**: `wp-admin/admin.php?page=wp-quiz-flow-usage`

### Settings Configuration
- Navigate to **wpQuizFlow > Settings**
- Configure default values that will be used when shortcode attributes are not provided
- Debug status is read-only (controlled by wpFieldFlow settings)

## Architecture

```
wpQuizFlow Plugin
├── Core/Plugin.php (initializes AdminMenu)
├── Admin/
│   ├── AdminMenu.php (menu registration & rendering)
│   └── SettingsManager.php (settings handling & Debug integration)
└── templates/admin/
    ├── dashboard.php
    ├── quizzes.php
    ├── settings.php
    └── usage.php
```

## Integration Points

1. **wpFieldFlow Debug System**: All logging goes through `\WpFieldFlow\Core\Debug::log()`
2. **wpFieldFlow Menu**: wpQuizFlow attempts to attach to wpFieldFlow's menu if available
3. **Settings Storage**: Uses WordPress options API (separate from wpFieldFlow settings)

## Future Enhancements

- Quiz builder interface (currently JSON files only)
- AJAX-powered settings updates
- Quiz cache clearing functionality
- Quiz analytics dashboard
- Custom quiz templates

