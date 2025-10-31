# wpQuizFlow

**Version**: 1.0.0  
**Requires**: wpFieldFlow plugin  
**Description**: Decision tree quiz system for filtering wpFieldFlow resources

---

## 📋 Overview

wpQuizFlow is an **add-on plugin** for wpFieldFlow that provides an empathetic, Typeform-style questionnaire system to guide users to relevant resources. It operates as a decision logic layer on top of wpFieldFlow's synced data.

### Key Features

- ✅ **NOMA-style empathetic UX** - Warm, supportive question flow
- ✅ **Tag-based filtering** - Maps quiz answers to WordPress taxonomies
- ✅ **Full directory integration** - Uses wpFieldFlow's ResourceDirectory for results
- ✅ **JSON-driven quizzes** - Flexible quiz configuration via JSON files
- ✅ **Mobile-responsive** - Touch-friendly design
- ✅ **No data pollution** - Doesn't modify synced resource data

---

## 🔌 Installation

### Prerequisites

1. **wpFieldFlow plugin** must be installed and activated
2. WordPress 6.0+
3. PHP 8.0+

### Steps

1. Upload `wp-quiz-flow` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. If wpFieldFlow is not active, you'll see an admin notice

---

## 🚀 Usage

### Basic Shortcode

```php
[wpQuizFlow id="2"]
```

### With All Options

```php
[wpQuizFlow 
  id="2" 
  quiz_id="noma-quiz"
  show_progress="true" 
  show_contact="true"
  contact_number="205-555-0100"
  result_limit="12"
]
```

### Shortcode Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | integer | *required* | Sheet ID from wpFieldFlow |
| `sheet_id` | integer | (alias for id) | Alternative parameter name |
| `quiz_id` | string | `"noma-quiz"` | Quiz configuration to use |
| `show_progress` | string | `"true"` | Show progress bar |
| `show_contact` | string | `"true"` | Show contact button on results |
| `contact_number` | string | `"205-555-0100"` | Phone number for contact button |
| `result_limit` | string | `"12"` | Initial number of results to display |

---

## 🏗️ Architecture

### Plugin Structure

```
wp-quiz-flow/
├── wp-quiz-flow.php          # Main plugin file
├── src/
│   ├── Core/                 # Plugin core classes
│   ├── Quiz/                 # Quiz logic classes
│   └── Frontend/             # Shortcode handler
├── assets/
│   ├── js/components/        # React components
│   ├── css/                  # Quiz styles
│   └── json/                 # Quiz configurations
└── README.md
```

### How It Works

1. **User starts quiz** → Shortcode renders QuizNavigator component
2. **User answers questions** → Quiz collects tags (e.g., `audience:self`, `stage:crisis`)
3. **Tags mapped to taxonomies** → TagMapper converts to WordPress taxonomy filters
4. **Results displayed** → ResourceDirectory shows filtered resources with search/filters

---

## 📝 Quiz Configuration

### JSON Quiz Format

Quizzes are defined in `/assets/json/` directory. Example structure:

```json
{
  "quiz_id": "noma-quiz",
  "version": "1.0.0",
  "title": "Find Resources for Your Situation",
  "target_sheet_id": 2,
  "questions": {
    "Q1": {
      "type": "question",
      "text": "Who are you looking to support?",
      "subtitle": "We're here to help",
      "options": [
        {
          "id": "A",
          "text": "Myself",
          "next": "Q2-SELF",
          "tags": ["audience:self"],
          "emoji": "🙋"
        }
      ]
    }
  }
}
```

### Tag Mapping

Tags are mapped to WordPress taxonomies via `/assets/json/tag-mapping.json`:

```json
{
  "mappings": {
    "stage:crisis": {
      "resource_category": ["help-with-treatment"],
      "resource_tags": ["interventions", "treatment"]
    }
  }
}
```

---

## 🎨 Customization

### Changing Quiz Content

1. Edit `/assets/json/noma-quiz.json` to modify questions
2. Edit `/assets/json/tag-mapping.json` to update taxonomy mappings
3. Clear browser cache and refresh

### Styling

Quiz styles are in `/assets/css/quiz.css`. Uses CSS custom properties from wpFieldFlow for consistency.

### Adding New Quizzes

1. Create new JSON file in `/assets/json/` (e.g., `my-quiz.json`)
2. Use shortcode: `[wpQuizFlow id="2" quiz_id="my-quiz"]`

---

## 🔧 Development

### Code Standards

- **PHP**: PSR-12, namespaces (`WpQuizFlow\`), PSR-4 autoloading
- **JavaScript**: Modern React patterns (hooks, functional components)
- **CSS**: Mobile-first, accessibility-aware
- **Documentation**: PHPDoc comments, inline explanations

### File Structure

- `src/Core/` - Plugin initialization, activation, dependency checks
- `src/Quiz/` - Quiz logic, tag mapping, data loading
- `src/Frontend/` - Shortcode handler
- `assets/js/components/` - React components
- `assets/css/` - Styles
- `assets/json/` - Quiz configurations

---

## 🔌 Integration with wpFieldFlow

wpQuizFlow uses wpFieldFlow's:
- **Sheet configurations** - Gets sheet data via SheetsManager
- **Layout schemas** - Uses LayoutDesigner for resource display
- **REST API** - Queries resources via wpFieldFlow endpoints
- **ResourceDirectory component** - Renders filtered results
- **Design tokens** - Uses wpFieldFlow CSS variables

---

## 📚 Documentation

- **Migration Guide**: See `/docs-roadmap/WPQUIZFLOW-SEPARATION-PLAN.md`
- **NOMA Mapping**: See `/docs-roadmap/NOMA-QUIZ-MAPPING.md` in wpFieldFlow
- **Usage Examples**: See `QUIZ_USAGE.md` in wpFieldFlow docs

---

## 🐛 Troubleshooting

### Quiz Not Loading

**Problem**: Shows "Error loading quiz"  
**Solutions**:
1. Check wpFieldFlow is active
2. Verify sheet ID exists in wpFieldFlow
3. Check browser console for errors
4. Verify React is loading

### No Results Shown

**Problem**: Quiz completes but no resources display  
**Solutions**:
1. Check tag mapping matches actual taxonomy terms
2. Verify resources exist with those tags
3. Review console logs for tag → taxonomy conversion

### Dependency Error

**Problem**: "wpFieldFlow plugin is required"  
**Solution**: Install and activate wpFieldFlow first, then reactivate wpQuizFlow

---

## 📝 Changelog

### v1.0.0 - Initial Release (Oct 31, 2025)
- ✅ Separated from wpFieldFlow
- ✅ QuizNavigator component
- ✅ Tag-based filtering
- ✅ JSON quiz configuration
- ✅ NOMA quiz implementation
- ✅ Full ResourceDirectory integration

---

## 🙌 Credits

- **Developed by**: 21 ads media
- **For**: NOMA (North of Mobile Alliance)
- **Built on**: wpFieldFlow plugin

---

## 📄 License

GPL v2 or later

---

**For support or questions, please refer to the main wpFieldFlow documentation.**

