# wpQuizFlow Enhancement Roadmap

## Current Status Assessment

**Version**: 1.0.0

**Status**: MVP Complete - Production Ready

**Last Updated**: Assessment Date

### Strengths

- Clean architecture with proper separation from wpFieldFlow
- Comprehensive dependency checking and error handling
- PSR-12/PSR-4 code standards
- Complete admin interface (Dashboard, Quizzes, Settings, Usage Guide)
- Well-documented with multiple guides

### Critical Issues Identified

1. **Dynamic Quiz Loading**: QuizNavigator.jsx uses hardcoded quizStructure instead of loading from JSON
2. **Tag Mapping Duplication**: Mapping exists in both PHP (TagMapper.php) and JS (QuizNavigator.jsx)
3. **Database Storage**: QuizData.php has TODO for database storage (currently JSON-only)
4. **No Analytics**: No tracking of quiz completions, drop-offs, or popular paths
5. **No Quiz Builder**: Admin can only view quizzes, not create/edit them

---

## Phase 1: Critical Fixes & Foundation (Priority: HIGH)

### 1.1 Dynamic Quiz Loading

**Problem**: QuizNavigator.jsx has hardcoded quizStructure instead of loading from JSON files

**Impact**: Prevents multiple quizzes, requires code changes to modify questions

**Files to Modify**:

- `assets/js/components/QuizNavigator.jsx` - Remove hardcoded structure, load from props/state
- `src/Frontend/ShortcodeManager.php` - Ensure quiz data is passed to frontend
- `src/Quiz/QuizData.php` - Verify JSON loading works correctly

**Implementation Steps**:

1. Remove hardcoded `quizStructure` object from QuizNavigator.jsx
2. Load quiz data from `wpQuizFlowData.quizData` (already passed via `wp_localize_script`)
3. Validate quiz structure on load (check for required fields: questions, options, next)
4. Add error handling for malformed quiz JSON
5. Test with existing noma-quiz.json

**Acceptance Criteria**:

- QuizNavigator loads quiz structure from JSON
- Multiple quizzes can be used without code changes
- Error messages display for invalid quiz structures
- Backward compatibility maintained

---

### 1.2 Consolidate Tag Mapping & Add Grouping Logic

**Problem**: Tag mapping duplicated in PHP (TagMapper.php) and JS (QuizNavigator.jsx)

**Impact**: Maintenance burden, risk of inconsistencies

**Enhancement**: Add tag grouping logic for scalable taxonomy mapping (e.g., `audience:*` → `audience_group`, `stage:*` → `engagement_stage`)

**Files to Modify**:

- `assets/js/components/QuizNavigator.jsx` - Remove hardcoded tagToTaxonomyMap
- `src/Frontend/ShortcodeManager.php` - Pass TagMapper output to frontend
- `src/Quiz/TagMapper.php` - Add grouping logic and ensure complete mapping available

**Implementation Steps**:

1. Remove `tagToTaxonomyMap` from QuizNavigator.jsx
2. Add tag grouping logic to TagMapper.php:

   - Support pattern matching (e.g., `audience:*` → `audience_group` taxonomy)
   - Support `stage:*` → `engagement_stage` taxonomy
   - Maintain backward compatibility with explicit mappings

3. Pass tag mapping (including grouping rules) from PHP via `wp_localize_script` in ShortcodeManager
4. Use PHP mapping in `buildTaxonomyFilters()` function
5. Add mapping validation on PHP side
6. Update documentation to reflect single source of truth and grouping patterns

**Acceptance Criteria**:

- Single source of truth for tag mappings (PHP only)
- Tag grouping patterns work (audience:*, stage:*)
- Frontend receives mapping via localized script
- No duplicate mapping code
- Tag mapping changes only require PHP file edit

---

### 1.3 Quiz JSON Validation

**Problem**: No validation of quiz JSON structure before use

**Impact**: Runtime errors, poor user experience

**Files to Create/Modify**:

- `src/Quiz/QuizValidator.php` - New validator class
- `src/Quiz/QuizData.php` - Add validation calls

**Implementation Steps**:

1. Create QuizValidator class with schema validation
2. Validate required fields: quiz_id, title, questions
3. Validate question structure: type, text, options
4. Validate option structure: id, text, next, tags
5. Return validation errors with helpful messages
6. Log validation errors via Debug system

**Acceptance Criteria**:

- Invalid quiz JSON detected before rendering
- Clear error messages for validation failures
- Validation errors logged for debugging
- Admin notices displayed for invalid quizzes

---

## Phase 2: Database Storage & Admin Tools (Priority: HIGH)

### 2.1 Database Storage for Quizzes

**Problem**: Quizzes stored only in JSON files, no database persistence

**Impact**: Cannot create/edit quizzes in admin, no version history

**Files to Create/Modify**:

- `src/CPT/QuizPostType.php` - New custom post type for quizzes
- `src/Quiz/QuizData.php` - Update to load from database first, JSON fallback
- `templates/admin/quiz-editor.php` - New quiz editor template

**Implementation Steps**:

1. Create QuizPostType class to register custom post type 'wp_quiz_flow_quiz'
2. Add meta boxes for quiz structure editing
3. Store quiz JSON in post meta or custom table
4. Update QuizData::loadQuizFromDatabase() implementation
5. Maintain JSON fallback for backward compatibility
6. Add migration script for existing JSON quizzes

**Database Schema**:

```sql
-- Post type: wp_quiz_flow_quiz
-- Post meta: _quiz_structure (JSON)
-- Post meta: _quiz_version
-- Post meta: _target_sheet_id
```

**Acceptance Criteria**:

- Quizzes can be created/edited in WordPress admin
- JSON quizzes still work (backward compatibility)
- Database quizzes load first, JSON as fallback
- Version history via post revisions

---

### 2.2 Quiz Builder UI (MVP)

**Problem**: No visual interface to create/edit quizzes

**Impact**: Requires JSON editing, not user-friendly

**Files to Create/Modify**:

- `assets/js/admin/quiz-builder.jsx` - New React component
- `templates/admin/quiz-editor.php` - Enhanced editor template
- `src/Admin/QuizEditor.php` - New admin class for quiz editing

**Implementation Steps**:

1. Create React-based quiz builder component
2. Form-based question/option creation (no drag-drop initially)
3. Visual branching logic editor
4. Tag assignment interface
5. Preview mode for testing quiz flow
6. Save to database via AJAX
7. Export to JSON functionality

**Features**:

- Add/remove questions
- Add/remove options per question
- Set next question/node for branching
- Assign tags to options
- Set priority levels
- Preview quiz flow

**Acceptance Criteria**:

- Admin can create new quiz via UI
- Admin can edit existing quiz
- Quiz structure validated before save
- Preview shows quiz flow correctly
- Export to JSON works

---

### 2.3 Enhanced Quiz Management

**Problem**: Quizzes page only lists quizzes, no actions

**Impact**: Limited admin functionality

**Files to Modify**:

- `templates/admin/quizzes.php` - Add edit/delete/duplicate actions
- `src/Admin/AdminMenu.php` - Add AJAX handlers

**Implementation Steps**:

1. Add edit/delete/duplicate buttons to quizzes list
2. Add bulk actions (delete, export)
3. Add preview link functionality
4. Add shortcode generation for each quiz
5. Add quiz statistics (completion count, etc.)
6. Add import/export functionality

**Acceptance Criteria**:

- Full CRUD operations from admin
- Bulk actions work correctly
- Shortcode copied on click
- Import/export quizzes as JSON

---

## Phase 3: Analytics & Tracking (Priority: MEDIUM)

### 3.1 Quiz Session Tracking

**Problem**: No tracking of quiz usage or completion

**Impact**: No insights into user behavior

**Files to Create/Modify**:

- `src/Tracking/QuizSession.php` - New tracking class
- `database/quiz-sessions-table.php` - Database table creation
- `assets/js/components/QuizNavigator.jsx` - Add tracking calls

**Database Schema**:

```sql
CREATE TABLE wp_quiz_flow_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id VARCHAR(100) NOT NULL,
    session_id VARCHAR(64) NOT NULL,
    user_path TEXT,
    collected_tags TEXT,
    completed TINYINT(1) DEFAULT 0,
    result_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_quiz_id (quiz_id),
    INDEX idx_session_id (session_id)
);
```

**Implementation Steps**:

1. Create database table for quiz sessions
2. Generate unique session ID on quiz start
3. Track each answer selection
4. Track quiz completion
5. Track result count shown
6. Store anonymized user path
7. Add cleanup cron for old sessions

**Acceptance Criteria**:

- Quiz sessions stored in database
- Session tracking doesn't impact performance
- GDPR-compliant (anonymized data)
- Old sessions cleaned up automatically

---

### 3.2 Analytics Dashboard

**Problem**: No analytics display for quiz performance

**Impact**: Cannot measure quiz effectiveness

**Files to Create/Modify**:

- `templates/admin/dashboard.php` - Add analytics widgets
- `src/Admin/Analytics.php` - New analytics class
- `assets/js/admin/analytics-charts.js` - Chart visualization

**Metrics to Track**:

- Total quiz starts
- Completion rate
- Drop-off points (which questions cause abandonment)
- Popular paths (most common answer sequences)
- Average completion time
- Most selected answers per question
- Result click-through rates

**Implementation Steps**:

1. Create Analytics class with query methods
2. Add dashboard widgets for key metrics
3. Create charts for visualization (Chart.js or similar)
4. Add date range filtering
5. Add export functionality (CSV)
6. Add comparison view (quiz vs quiz)

**Acceptance Criteria**:

- Dashboard shows key metrics
- Charts visualize trends
- Date filtering works
- Export functionality available
- Performance optimized (cached queries)

---

### 3.3 External Analytics Integration

**Problem**: No integration with Google Analytics or other tools

**Impact**: Cannot track in existing analytics systems

**Files to Modify**:

- `assets/js/components/QuizNavigator.jsx` - Add GA events
- `src/Settings/SettingsManager.php` - Add GA tracking ID setting

**Implementation Steps**:

1. Add Google Analytics event tracking
2. Track: quiz_start, question_answer, quiz_complete
3. Add custom dimensions for tags, paths
4. Add optional Facebook Pixel integration
5. Add REST API endpoint for external analytics

**Events to Track**:

- `quiz_start` - When quiz begins
- `question_view` - When question displayed
- `answer_select` - When option selected
- `quiz_complete` - When results shown
- `result_click` - When resource clicked

**Acceptance Criteria**:

- GA events fire correctly
- Custom dimensions populated
- Settings page allows GA ID configuration
- Events visible in GA dashboard

---

## Phase 4: Advanced Features (Priority: LOW)

### 4.1 A/B Testing Framework

**Problem**: Cannot test different quiz variations

**Impact**: Cannot optimize quiz performance

**Files to Create/Modify**:

- `src/Testing/ABTestManager.php` - New A/B testing class
- `templates/admin/ab-tests.php` - A/B test management UI

**Implementation Steps**:

1. Create A/B test assignment system
2. Randomly assign users to variants
3. Track performance metrics per variant
4. Statistical significance calculation
5. Winner determination logic

**Acceptance Criteria**:

- Multiple quiz variants can be tested
- Users randomly assigned
- Performance tracked per variant
- Statistical significance calculated

---

### 4.2 Advanced Conditional Logic

**Problem**: Branching only based on single answer

**Impact**: Limited quiz flexibility

**Files to Modify**:

- `assets/js/components/QuizNavigator.jsx` - Add conditional logic engine
- `src/Quiz/QuizLogic.php` - PHP-side logic evaluation

**Features**:

- Multi-answer conditions (AND/OR logic)
- Score-based branching
- Time-based conditions
- User history-based routing

**Implementation Steps**:

1. Extend quiz JSON schema for conditions
2. Create logic evaluation engine
3. Add condition builders in quiz editor
4. Test complex branching scenarios

**Acceptance Criteria**:

- Complex conditions work correctly
- Logic evaluation is performant
- Quiz editor supports condition building

---

### 4.3 Multi-language Support

**Problem**: Quiz only in English

**Impact**: Limited accessibility

**Files to Modify**:

- All templates - Add i18n functions
- Quiz JSON structure - Support translations
- `src/i18n/QuizTranslations.php` - Translation management

**Implementation Steps**:

1. Add translation functions to all strings
2. Create .pot file for translations
3. Support translated quiz JSON files
4. Add language switcher
5. Auto-detect user language
6. RTL support for Arabic/Hebrew

**Acceptance Criteria**:

- Quiz strings translatable
- Multiple languages supported
- Language detection works
- RTL layouts supported

---

### 4.4 Quiz Templates & Marketplace

**Problem**: Every quiz must be built from scratch

**Impact**: Slower deployment for new organizations

**Files to Create**:

- `templates/quiz-templates/` - Template directory
- `src/Templates/TemplateManager.php` - Template management

**Implementation Steps**:

1. Create template system
2. Build starter templates (crisis, education, etc.)
3. Add template import/export
4. Create template marketplace (future)
5. Add template customization wizard

**Acceptance Criteria**:

- Templates can be imported
- Templates customizable
- Template library available

---

## Phase 5: Performance & UX Enhancements (Priority: MEDIUM)

### 5.1 Performance Optimization

**Problem**: Potential performance issues with large quizzes

**Impact**: Slow page loads, poor UX

**Files to Modify**:

- `src/Frontend/ShortcodeManager.php` - Add caching
- `assets/js/components/QuizNavigator.jsx` - Code splitting
- `src/Quiz/QuizData.php` - Add quiz caching

**Optimizations**:

- Quiz data caching (transients)
- Lazy loading of quiz components
- Code splitting for React components
- Asset minification
- CDN support for assets

**Implementation Steps**:

1. Implement quiz data caching
2. Add cache invalidation on quiz update
3. Lazy load ResourceDirectory component
4. Minify JS/CSS assets
5. Add performance monitoring

**Acceptance Criteria**:

- Quiz loads in <2 seconds
- Caching works correctly
- Cache invalidation on updates
- Performance metrics tracked

---

### 5.2 Enhanced Accessibility

**Problem**: May not meet WCAG 2.1 AA standards

**Impact**: Accessibility barriers

**Files to Modify**:

- `assets/js/components/QuizNavigator.jsx` - ARIA labels, keyboard nav
- `assets/css/quiz.css` - High contrast, focus states

**Implementation Steps**:

1. Add ARIA labels to all interactive elements
2. Implement keyboard navigation
3. Add screen reader announcements
4. Improve focus indicators
5. Add high contrast mode
6. Test with screen readers

**Acceptance Criteria**:

- WCAG 2.1 AA compliant
- Keyboard navigation works
- Screen reader compatible
- High contrast mode available

---

### 5.3 Mobile Enhancements

**Problem**: Basic mobile support, could be improved

**Impact**: Suboptimal mobile experience

**Files to Modify**:

- `assets/css/quiz.css` - Enhanced mobile styles
- `assets/js/components/QuizNavigator.jsx` - Touch gestures

**Enhancements**:

- Swipe gestures for navigation
- Improved touch targets
- Better mobile animations
- Offline support
- PWA features

**Implementation Steps**:

1. Add swipe gesture support
2. Improve touch target sizes
3. Add offline quiz capability
4. Add service worker for PWA
5. Test on various devices

**Acceptance Criteria**:

- Swipe gestures work
- Touch targets meet size guidelines
- Offline mode functional
- PWA installable

---

### 5.4 Social Sharing & Embedding

**Problem**: No way to share quiz results

**Impact**: Reduced viral potential

**Files to Create/Modify**:

- `assets/js/components/ShareResults.jsx` - Share component
- `src/Embed/QuizEmbed.php` - Embed handling

**Features**:

- Share quiz results on social media
- Embeddable quiz widget
- Shareable quiz links with pre-selected answers
- Email results to user

**Implementation Steps**:

1. Add share buttons to results page
2. Generate shareable links
3. Create embeddable widget
4. Add email results functionality
5. Add Open Graph tags for social sharing

**Acceptance Criteria**:

- Social sharing works
- Embeddable widget functional
- Shareable links work
- Email results sends correctly

---

## Implementation Timeline

### Phase 1: Critical Fixes (Weeks 1-2)

- Week 1: Dynamic quiz loading, tag mapping consolidation
- Week 2: Quiz validation, testing

### Phase 2: Database & Admin (Weeks 3-6)

- Week 3-4: Database storage implementation
- Week 5-6: Quiz builder UI MVP

### Phase 3: Analytics (Weeks 7-9)

- Week 7: Session tracking
- Week 8: Analytics dashboard
- Week 9: External integrations

### Phase 4: Advanced Features (Weeks 10-14)

- Week 10-11: A/B testing
- Week 12: Conditional logic
- Week 13: Multi-language
- Week 14: Templates

### Phase 5: Performance & UX (Weeks 15-17)

- Week 15: Performance optimization
- Week 16: Accessibility improvements
- Week 17: Mobile enhancements, social sharing

---

## Success Metrics

### Phase 1 Success

- Quiz loads from JSON dynamically
- No duplicate tag mapping code
- Validation prevents runtime errors

### Phase 2 Success

- Admin can create/edit quizzes in UI
- Database storage working
- Backward compatibility maintained

### Phase 3 Success

- Quiz analytics visible in dashboard
- Completion rates tracked
- External analytics integrated

### Phase 4 Success

- A/B testing functional
- Advanced logic working
- Multi-language support

### Phase 5 Success

- Performance optimized
- WCAG compliant
- Mobile experience excellent

---

## Risk Mitigation

### Technical Risks

- **Breaking Changes**: Maintain backward compatibility throughout
- **Performance**: Implement caching early
- **Data Loss**: Backup existing JSON quizzes before migration

### User Experience Risks

- **Complexity**: Keep admin UI simple, progressive enhancement
- **Migration**: Provide clear migration path for existing quizzes
- **Learning Curve**: Comprehensive documentation and tutorials

---

## Dependencies

### Required

- wpFieldFlow plugin (existing dependency)
- WordPress 6.0+
- PHP 8.0+

### Optional Enhancements

- Chart.js for analytics visualization
- React 18 for quiz builder
- Service Worker API for PWA features

---

## Documentation Updates

With each phase, update:

- README.md with new features
- Admin documentation for new UI features
- Developer docs for new APIs
- User guides for new functionality

---

**Next Steps**: Begin with Phase 1.1 (Dynamic Quiz Loading) as it unlocks all other enhancements.