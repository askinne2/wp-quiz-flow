# wpQuizFlow - Quick Start Guide

**Plugin**: wpQuizFlow  
**Requires**: wpFieldFlow plugin

## 🚀 Using the Quiz

### Basic Usage

Add this shortcode to any WordPress page or post:

```php
[wpQuizFlow id="2"]
```

### Full Example with All Options

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

**Note**: Legacy shortcode `[wpQuizFlow]` still works but is deprecated. Use `[wpQuizFlow]` instead.

---

## 🎯 How It Works

### 1. User Flow

```
Question 1: Who are you helping?
  ↓
Question 2-3: Specific situation
  ↓
Results: Matched resources
```

### 2. Tag System

As users answer questions, the quiz collects tags:
- **audience** tags: `audience:self`, `audience:family`
- **stage** tags: `stage:crisis`, `stage:recovery`
- **need** tags: `need:immediate`, `need:counseling`

### 3. Resource Matching

Tags are translated to WordPress taxonomy terms:

```javascript
'stage:crisis' → resource_category: ['crisis-services', 'hotlines']
'need:counseling' → resource_category: ['therapy', 'mental-health']
```

Then resources are queried and displayed with **full search and filter functionality** so users can refine their results.

---

## 📝 Shortcode Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | integer | *required* | Sheet ID containing resources |
| `sheet_id` | integer | (alias for id) | Alternative parameter name |
| `show_progress` | string | `"true"` | Show progress bar at top |
| `show_contact` | string | `"true"` | Show contact button on results |
| `contact_number` | string | `"205-555-0100"` | Phone number for emergency contact |
| `result_limit` | string | `"12"` | Initial number of results to display (users can load more) |

---

## 🧪 Testing the Quiz

### Test Path 1: Crisis Support (Self)

1. Click "Myself"
2. Click "I'm in crisis and need immediate help"
3. **Expected**: Hotlines, crisis resources

**Tags collected**: `['audience:self', 'stage:crisis', 'need:immediate']`

### Test Path 2: Family Education

1. Click "Someone I care about"
2. Click "Understanding what's happening"
3. **Expected**: Educational resources, family guides

**Tags collected**: `['audience:family', 'stage:exploration', 'need:education']`

### Test Path 3: Recovery Support

1. Click "Myself"
2. Click "I'm in recovery and looking for support"
3. Click "Connection with others in recovery"
4. **Expected**: Support groups, peer support resources

**Tags collected**: `['audience:self', 'stage:recovery', 'need:peer_support']`

---

## 🎨 Customizing the Mapping

### Step 1: Get Your Actual Taxonomy Terms

```bash
# List all resource categories
wp-local wpsyncsheet term list resource_category --format=csv

# List all resource tags
wp-local wpsyncsheet term list resource_tags --format=csv
```

### Step 2: Update the Mapping

Edit `/wp-quiz-flow/assets/json/tag-mapping.json`:

```json
{
  "mappings": {
    "stage:crisis": {
      "resource_category": ["your-crisis-term", "your-hotline-term"],
      "resource_tags": ["your-tags"]
    }
  }
}
```

Or update the default mapping in `/wp-quiz-flow/src/Quiz/TagMapper.php` if you prefer code-based configuration.

### Step 3: Test Again

Clear browser cache and test each path to verify results match.

---

## 🔧 Configuration Tips

### Adjust Contact Number

For NOMA, update the phone number:

```php
[wpQuizFlow id="2" contact_number="205-YOUR-NUMBER"]
```

### Hide Progress Bar

For a cleaner look:

```php
[wpQuizFlow id="2" show_progress="false"]
```

### Show More Initial Results

Display up to 24 resources initially:

```php
[wpQuizFlow id="2" result_limit="24"]
```

Note: Users can always use pagination or filters to see all results.

---

## 🐛 Common Issues

### Issue: "Quiz component not loaded properly"

**Solution**: Check that the page has React loaded. Try:
1. Hard refresh (Cmd/Ctrl + Shift + R)
2. Clear browser cache
3. Check browser console for errors

### Issue: No results shown

**Solution**: Taxonomy mapping might be incorrect.
1. Run wp-cli to get actual term slugs
2. Update tag mapping in `/wp-quiz-flow/assets/json/tag-mapping.json`
3. Match slugs exactly (case-sensitive)

### Issue: Wrong resources shown

**Solution**: Tags might be mapping to wrong terms.
1. Check console logs: `wpFieldFlow: Showing quiz results`
2. Verify `collectedTags` matches your expectations
3. Verify `taxonomyFilters` has correct term slugs
4. Adjust mapping in QuizNavigator.jsx

---

## 📊 Monitoring & Analytics

### Console Logging

The quiz logs helpful debug info:

```javascript
// When quiz initializes
wpFieldFlow: QuizNavigator initialized

// When user answers
wpFieldFlow: Quiz answer selected
  { tags: ['audience:self', 'stage:crisis'], next: 'RESULTS' }

// When showing results
wpFieldFlow: Showing quiz results
  { 
    collectedTags: ['audience:self', 'stage:crisis', 'need:immediate'],
    taxonomyFilters: { resource_category: ['crisis-services', 'hotlines'] }
  }
```

### User Journey Tracking

For production, consider adding:
- Google Analytics events
- Completion rate tracking
- Most popular paths
- Drop-off points

---

## 🎯 Next Steps

1. **Add quiz to your site**: `[wpQuizFlow id="2"]`
2. **Test all paths**: Click through each question combination
3. **Verify mapping**: Ensure results match user needs
4. **Gather feedback**: Show to NOMA stakeholders
5. **Iterate**: Adjust questions, mapping, and UX based on feedback

---

## 📚 Related Documentation

- **Full Implementation Docs**: `/wp-quiz-flow/docs/QUIZ-NAVIGATOR-MVP.md`
- **NOMA Quiz Mapping**: `/wp-quiz-flow/docs/NOMA-QUIZ-MAPPING.md`
- **Example Quiz Structure**: `/wp-quiz-flow/docs/example-quiz.md`
- **wpFieldFlow Directory Usage**: `/wp-sync-sheet/FRONTEND_USAGE.md` (wpFieldFlow plugin)

---

## 🙌 Feedback & Questions

Test the quiz and note:
- Which paths work well?
- Which paths need adjustment?
- Are questions empathetic enough?
- Are results relevant?
- Is the UX intuitive?

**You're all set to launch! 🚀**

