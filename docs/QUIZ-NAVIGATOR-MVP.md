# Quiz Navigator MVP - Implementation Complete ✅

**Date**: October 31, 2025  
**Status**: MVP Complete - Ready for Testing  
**Shortcode**: `[wpQuizFlow id="2"]` (legacy `[wpQuizFlow]` also supported)

---

## 🎯 Overview

The Quiz Navigator is a **NOMA-style guided quiz** that helps users find mental health and addiction resources through an empathetic, Typeform-inspired experience. It uses **tag-based filtering** to map quiz answers to WordPress taxonomies for intelligent resource matching.

This is **Option 2: Tag-Based Mapping** from the MVP sprint plan - the balanced approach that gets us live quickly while maintaining flexibility.

---

## 🏗️ Architecture

### Component Structure

```
QuizNavigator.jsx (React)
├── Quiz Structure (hardcoded NOMA questions)
├── Tag Accumulation (collects tags as user answers)
├── Tag-to-Taxonomy Mapping (translates tags to WP filters)
└── Results Display (uses ResourceDirectory with filters)
```

### Data Flow

```
User Answer 
  → Tag Collection (e.g., 'audience:self', 'stage:crisis')
    → Taxonomy Mapping (e.g., resource_category: ['crisis-services', 'hotlines'])
      → WP_Query Filters
        → Matched Resources
          → Full Directory View (with search + filters for refinement)
```

---

## 📦 Files Created/Modified

### New Files

1. **`/wp-quiz-flow/assets/js/components/QuizNavigator.jsx`**
   - Main quiz component with NOMA question tree
   - Tag-to-taxonomy mapping layer
   - Branching logic and state management
   - Results integration with ResourceDirectory (from wpFieldFlow)

2. **`/wp-quiz-flow/docs/QUIZ-NAVIGATOR-MVP.md`** (this file)
   - Complete implementation documentation

### Plugin Structure

1. **`/wp-quiz-flow/src/Frontend/ShortcodeManager.php`**
   - Handles `renderQuizShortcode()` method
   - Handles `enqueueQuizAssets()` method
   - Registers `wpQuizFlow` shortcode (legacy `wpFieldFlow_quiz` supported)

2. **`/wp-quiz-flow/assets/js/quiz-app.js`**
   - Quiz-specific frontend initialization
   - Initializes QuizNavigator component

3. **`/wp-quiz-flow/assets/css/quiz.css`**
   - Comprehensive quiz-specific styles
   - Empathetic UX with priority highlighting
   - Smooth animations and transitions
   - Full mobile responsive design

---

## 🎨 Quiz Structure

### Question Tree (MVP - 2-3 Example Paths)

```
Q1: Who are you looking to support?
├── Myself → Q2-SELF
│   ├── I'm in crisis → RESULTS [stage:crisis, need:immediate]
│   ├── I'm considering change → Q3-CONSIDERING
│   │   ├── Understand options → RESULTS [stage:exploration, need:education]
│   │   ├── Talk to someone → RESULTS [stage:contemplation, need:counseling]
│   │   └── Need medical help → RESULTS [stage:active_treatment, need:medical_detox]
│   └── I'm in recovery → Q3-RECOVERY
│       ├── Connect with others → RESULTS [stage:recovery, need:peer_support]
│       ├── Professional counseling → RESULTS [stage:recovery, need:counseling]
│       └── Practical support → RESULTS [stage:recovery, need:life_skills]
│
├── Someone I care about → Q2-FAMILY
│   ├── Understanding → RESULTS [stage:exploration, need:education]
│   ├── Getting them into treatment → Q3-TREATMENT
│   │   ├── They're ready → RESULTS [stage:active_treatment, need:treatment_navigation]
│   │   ├── They're resistant → RESULTS [stage:contemplation, need:intervention]
│   │   └── Not sure → RESULTS [stage:exploration, need:education]
│   └── Support for myself → RESULTS [need:peer_support]
│
└── I'm a professional → RESULTS [audience:professional]
```

### Example Paths (MVP Scope)

**Path 1: Self + Crisis**
- Q1: "Myself" → tags: `['audience:self']`
- Q2: "I'm in crisis" → tags: `['audience:self', 'stage:crisis', 'need:immediate']`
- → Shows: Crisis hotlines, emergency services

**Path 2: Family + Education**
- Q1: "Someone I care about" → tags: `['audience:family']`
- Q2: "Understanding what's happening" → tags: `['audience:family', 'stage:exploration', 'need:education']`
- → Shows: Family education resources, addiction basics

---

## 🏷️ Tag-to-Taxonomy Mapping

### Mapping Layer (in TagMapper.php or tag-mapping.json)

```javascript
const tagToTaxonomyMap = {
  // Audience tags
  'audience:self': { resource_tags: ['individual-support'] },
  'audience:family': { resource_tags: ['family-support', 'caregiver'] },
  'audience:parent': { resource_tags: ['parent-support'] },
  
  // Stage tags
  'stage:crisis': { resource_category: ['crisis-services', 'hotlines'] },
  'stage:exploration': { resource_category: ['treatment-options', 'educational'] },
  'stage:active_treatment': { resource_category: ['detox', 'outpatient', 'residential'] },
  'stage:recovery': { resource_category: ['peer-support', 'support-groups'] },
  
  // Need tags
  'need:immediate': { resource_category: ['hotlines', 'emergency-care'] },
  'need:counseling': { resource_category: ['therapy', 'mental-health'] },
  'need:education': { resource_category: ['family-education', 'addiction-basics'] },
  'need:peer_support': { resource_category: ['support-groups', 'peer-support'] },
  'need:medical_detox': { resource_category: ['detox', 'medical-services'] }
};
```

### How It Works

1. **User answers question** → Quiz collects tags (e.g., `['audience:self', 'stage:crisis', 'need:immediate']`)
2. **Build taxonomy filters** → Translate tags to WP taxonomies
3. **Query resources** → ResourceDirectory uses filters with `relation: 'OR'`
4. **Display results** → Show top 5 matches with "See all" option

---

## 📝 Usage

### Basic Shortcode

```php
[wpQuizFlow id="2"]
```

### With Attributes

```php
[wpQuizFlow 
  id="2" 
  show_progress="true" 
  show_contact="true"
  contact_number="205-555-0100"
  result_limit="5"
]
```

### Shortcode Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `id` | required | Sheet ID to query resources from |
| `sheet_id` | (alias for id) | Alternative parameter name |
| `show_progress` | `true` | Show progress bar |
| `show_contact` | `true` | Show NOMA contact button on results |
| `contact_number` | `205-555-0100` | Phone number for contact button |
| `result_limit` | `12` | Initial max results to show (users can paginate/filter) |

---

## 🎨 UX Features

### Empathetic Design
- **Warm greeting**: "Let's help you find the right resources"
- **Reassuring subtitles**: "There's no wrong answer", "We're proud of you"
- **Emoji indicators**: Visual warmth and clarity
- **Priority highlighting**: Urgent options (crisis) visually distinct

### Smooth Interactions
- **Fade-in animations**: Gentle page transitions
- **Hover effects**: Interactive feedback
- **Progress bar**: Visual journey tracking
- **Back button**: Confidence to explore

### Filterable Results
- **Full directory view**: Search, filters, and pagination on results page
- **Refinement tools**: Users can narrow down quiz-matched resources
- **Pre-filtered by quiz**: Initial results based on answers, but expandable

### Mobile-First
- Fully responsive design
- Touch-friendly large buttons
- Optimized font sizes
- Stacked layout on small screens

---

## 🧪 Testing Checklist

### MVP Test Paths

- [ ] **Test Path 1**: Self → Crisis → Immediate help
  - Should show: Hotlines, crisis services
  - Expected tags: `['audience:self', 'stage:crisis', 'need:immediate']`

- [ ] **Test Path 2**: Family → Understanding → Education
  - Should show: Family education, addiction basics
  - Expected tags: `['audience:family', 'stage:exploration', 'need:education']`

- [ ] **Test Path 3**: Self → Considering → Talk to someone
  - Should show: Counseling, therapy options
  - Expected tags: `['audience:self', 'stage:contemplation', 'need:counseling']`

### Functionality Tests

- [ ] Progress bar updates correctly
- [ ] Back button restores previous state
- [ ] Results show top 5 resources
- [ ] "Start Over" button resets quiz
- [ ] NOMA contact button displays and works
- [ ] Mobile layout renders properly
- [ ] No console errors

### Browser Tests

- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

---

## 🚀 Deployment Steps

1. **Verify Taxonomy Terms Exist**
   ```bash
   wp-local wpsyncsheet term list resource_category --format=table
   wp-local wpsyncsheet term list resource_tags --format=table
   ```

2. **Update Mapping if Needed**
   - Edit `tagToTaxonomyMap` in `QuizNavigator.jsx`
   - Match actual taxonomy slugs from your database

3. **Add Shortcode to Page**
   ```php
   [wpQuizFlow id="2"]
   ```

4. **Test All Paths**
   - Click through each question path
   - Verify results match expectations
   - Check console for errors

5. **Gather Stakeholder Feedback**
   - NOMA contact info correct?
   - Questions empathetic enough?
   - Results relevant?

---

## 📊 Future Enhancements (Option B)

Once MVP is validated with stakeholders:

### Phase 2: Enhanced Mapping
- [ ] Multi-taxonomy support (AND logic for precise matching)
- [ ] Weighted scoring system (prioritize best matches)
- [ ] Location-based filtering
- [ ] Insurance/cost filtering

### Phase 3: Admin Builder
- [ ] Visual quiz editor (drag-and-drop question tree)
- [ ] Custom tag creation
- [ ] A/B testing support
- [ ] Analytics dashboard (completion rates, popular paths)

### Phase 4: Advanced Features
- [ ] Save/resume progress (cookies/localStorage)
- [ ] Email results to user
- [ ] PDF resource guide generation
- [ ] Share quiz link with pre-selected answers

---

## 🐛 Troubleshooting

### Quiz Not Loading

**Problem**: Container shows "Loading quiz..." indefinitely

**Solutions**:
1. Check browser console for errors
2. Verify React is loading: `console.log(typeof React)`
3. Check QuizNavigator is registered: `console.log(typeof window.QuizNavigator)`
4. Ensure `data-config` attribute has valid JSON

### No Results Shown

**Problem**: Quiz completes but shows "No resources match your needs"

**Solutions**:
1. Verify taxonomy terms exist in WordPress
2. Check tag mapping matches actual term slugs
3. Review console logs for tag → taxonomy translation
4. Try `relation: 'OR'` instead of 'AND' for broader matching

### Mapping Doesn't Match

**Problem**: Results don't match expected resources

**Solutions**:
1. Update `tagToTaxonomyMap` with actual term slugs
2. Use wp-cli to list exact taxonomy terms:
   ```bash
   wp-local wpsyncsheet term list resource_category --format=csv
   ```
3. Adjust quiz questions or taxonomy structure

---

## 📞 Support

- **Documentation**: `/docs-roadmap/`
- **Console Logs**: Prefixed with `wpFieldFlow:`
- **Debug Mode**: Check `/logs/debug.log`

---

## 📝 Changelog

### v1.0.0 - MVP Release (Oct 31, 2025)
- ✅ QuizNavigator component with NOMA structure
- ✅ Tag-based resource filtering
- ✅ Empathetic UX with priority highlighting
- ✅ Mobile-responsive design
- ✅ Integration with ResourceDirectory
- ✅ Full search and filter functionality on results page
- ✅ Back/restart functionality
- ✅ Progress tracking
- ✅ NOMA contact fallback

---

## 🎉 Success Metrics

MVP is successful if:
- ✅ 2-3 quiz paths work end-to-end
- ✅ Relevant resources display for each path
- ✅ NOMA stakeholders approve UX tone
- ✅ Mobile experience is smooth
- ✅ No critical bugs in production

---

**Ready to test! 🚀**

Add `[wpQuizFlow id="2"]` to any WordPress page and start gathering feedback!

