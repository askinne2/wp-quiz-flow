# NOMA Quiz → Taxonomy Mapping Reference

**Updated**: October 31, 2025  
**Status**: Production-Ready with Actual NOMA Terms

---

## 🎯 Your Actual Taxonomy

### resource_category (8 terms)
- `collegiate-recovery` - Collegiate Recovery
- `help-with-treatment` - Help with Treatment
- `insurance` - Insurance
- `literature` - Literature
- `opoid-treatment` - Opioid Treatment (typo in DB)
- `parent-caregiver-support` - Parent & Caregiver Support
- `support-groups` - Support Groups
- `treatment-programs` - Treatment Programs

### resource_tags (28 terms)
- `12-step-based` - 12-Step Based
- `addiction` - Addiction
- `adolescents` - Adolescents
- `alcoholism` - Alcoholism
- `apps-technology` - Apps & Technology
- `codependency` - Codependency
- `detox` - Detox
- `downloadable-pdfs` - Downloadable PDFs
- `evidence-based` - Evidence Based
- `extended-care` - Extended Care
- `faith-based` - Faith-Based
- `for-families` - For Families
- `for-parents-caregivers` - For Parents & Caregivers
- `for-people-in-recovery` - For People in Recovery
- `grief-support` - Grief Support
- `helpful-articles` - Helpful Articles
- `insurance` - Insurance
- `interventions` - Interventions
- `medically-assisted` - Medically Assisted
- `outpatient` - Outpatient
- `parity-law` - Parity Law
- `podcasts` - Podcasts
- `recovery-books` - Recovery Books
- `recovery-residence` - Recovery Residence
- `residential` - Residential
- `sober-living` - Sober Living
- `treatment` - Treatment
- `websites` - Websites

---

## 🗺️ Quiz Path → Resource Mapping

### Path 1: Self → Crisis → Immediate Help

**User Journey**:
1. "Let's help you find the right resources. Who are you looking to support?"
   - Click: **"Myself"** 🙋
2. "Thank you for reaching out. Where are you in your journey?"
   - Click: **"I'm in crisis and need immediate help"** 🚨

**Tags Collected**:
```javascript
['audience:self', 'stage:crisis', 'need:immediate']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['help-with-treatment'],
  resource_tags: ['for-people-in-recovery', 'interventions', 'treatment']
}
```

**Expected Resources**:
- Crisis intervention services
- Help with Treatment resources
- Immediate treatment navigation
- Resources for people in recovery

**User Experience**:
Results page shows the full directory with search and filters enabled. Users can:
- Search by keyword
- Filter by any taxonomy term
- Paginate through all results
- See quiz-matched resources highlighted/pre-filtered

---

### Path 2: Self → Considering → Understanding Options

**User Journey**:
1. "Myself" 🙋
2. "I'm considering making a change" 🤔
3. "I want to understand my options" 📚

**Tags Collected**:
```javascript
['audience:self', 'stage:contemplation', 'stage:exploration', 'need:education']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['literature'],
  resource_tags: [
    'for-people-in-recovery',
    'helpful-articles',
    'interventions',
    'websites',
    'downloadable-pdfs',
    'podcasts'
  ]
}
```

**Expected Resources**:
- Educational literature
- Helpful articles about addiction
- Downloadable PDFs
- Podcasts
- Websites with information

---

### Path 3: Self → Considering → Talk to Someone

**User Journey**:
1. "Myself" 🙋
2. "I'm considering making a change" 🤔
3. "I'm ready to talk to someone" 💬

**Tags Collected**:
```javascript
['audience:self', 'stage:contemplation', 'need:counseling']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['treatment-programs', 'literature'],
  resource_tags: [
    'for-people-in-recovery',
    'helpful-articles',
    'interventions',
    'outpatient',
    'evidence-based'
  ]
}
```

**Expected Resources**:
- Treatment programs
- Outpatient services
- Evidence-based counseling
- Literature about getting help

---

### Path 4: Self → Considering → Medical Detox

**User Journey**:
1. "Myself" 🙋
2. "I'm considering making a change" 🤔
3. "I need medical help to stop safely" 🏥

**Tags Collected**:
```javascript
['audience:self', 'stage:contemplation', 'stage:active_treatment', 'need:medical_detox']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['treatment-programs', 'help-with-treatment', 'literature'],
  resource_tags: [
    'for-people-in-recovery',
    'helpful-articles',
    'interventions',
    'detox',
    'residential',
    'outpatient',
    'treatment',
    'opoid-treatment',
    'medically-assisted'
  ]
}
```

**Expected Resources**:
- Detox facilities
- Medically assisted treatment
- Opioid treatment programs
- Residential treatment
- Help navigating treatment options

---

### Path 5: Self → Recovery → Peer Support

**User Journey**:
1. "Myself" 🙋
2. "I'm in recovery and looking for support" 🌱
3. "Connection with others in recovery" 🤝

**Tags Collected**:
```javascript
['audience:self', 'stage:recovery', 'need:peer_support']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['support-groups'],
  resource_tags: [
    'for-people-in-recovery',
    'recovery-books',
    '12-step-based'
  ]
}
```

**Expected Resources**:
- Support groups
- 12-step programs
- Peer recovery support
- Recovery books

---

### Path 6: Self → Recovery → Professional Counseling

**User Journey**:
1. "Myself" 🙋
2. "I'm in recovery and looking for support" 🌱
3. "Professional counseling or therapy" 🧠

**Tags Collected**:
```javascript
['audience:self', 'stage:recovery', 'need:counseling']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['support-groups', 'treatment-programs'],
  resource_tags: [
    'for-people-in-recovery',
    'recovery-books',
    'outpatient',
    'evidence-based'
  ]
}
```

**Expected Resources**:
- Outpatient counseling
- Treatment programs
- Evidence-based therapy
- Support groups with professional facilitation

---

### Path 7: Self → Recovery → Practical Support

**User Journey**:
1. "Myself" 🙋
2. "I'm in recovery and looking for support" 🌱
3. "Practical life support (housing, job, etc.)" 🏠

**Tags Collected**:
```javascript
['audience:self', 'stage:recovery', 'need:life_skills']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['support-groups', 'collegiate-recovery'],
  resource_tags: [
    'for-people-in-recovery',
    'recovery-books',
    'recovery-residence',
    'sober-living',
    'extended-care'
  ]
}
```

**Expected Resources**:
- Recovery residences
- Sober living homes
- Collegiate recovery programs
- Extended care services

---

### Path 8: Family → Understanding

**User Journey**:
1. "Someone I care about" 👨‍👩‍👧
2. "Understanding what's happening" 📖

**Tags Collected**:
```javascript
['audience:family', 'stage:exploration', 'need:education']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['literature'],
  resource_tags: [
    'for-families',
    'for-parents-caregivers',
    'helpful-articles',
    'websites',
    'downloadable-pdfs',
    'podcasts'
  ]
}
```

**Expected Resources**:
- Family education materials
- Helpful articles for families
- Downloadable guides for caregivers
- Podcasts about supporting loved ones
- Educational websites

---

### Path 9: Family → Getting Them Into Treatment → They're Ready

**User Journey**:
1. "Someone I care about" 👨‍👩‍👧
2. "Getting them into treatment" 🎯
3. "Yes, they're ready" ✅

**Tags Collected**:
```javascript
['audience:family', 'stage:active_treatment', 'need:treatment_navigation']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['help-with-treatment', 'treatment-programs'],
  resource_tags: [
    'for-families',
    'for-parents-caregivers',
    'detox',
    'residential',
    'outpatient',
    'treatment'
  ]
}
```

**Expected Resources**:
- Help with treatment navigation
- Treatment programs
- Detox facilities
- Residential programs
- Outpatient options

---

### Path 10: Family → Getting Them Into Treatment → They're Resistant

**User Journey**:
1. "Someone I care about" 👨‍👩‍👧
2. "Getting them into treatment" 🎯
3. "No, they're resistant" 🛡️

**Tags Collected**:
```javascript
['audience:family', 'stage:active_treatment', 'stage:contemplation', 'need:intervention']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['help-with-treatment', 'treatment-programs', 'literature'],
  resource_tags: [
    'for-families',
    'for-parents-caregivers',
    'detox',
    'residential',
    'outpatient',
    'treatment',
    'helpful-articles',
    'interventions'
  ]
}
```

**Expected Resources**:
- Intervention services
- Family guidance
- Treatment navigation
- Articles about approaching resistant loved ones

---

### Path 11: Family → Support for Myself

**User Journey**:
1. "Someone I care about" 👨‍👩‍👧
2. "Support for myself" 💚

**Tags Collected**:
```javascript
['audience:family', 'need:peer_support']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_category: ['support-groups'],
  resource_tags: [
    'for-families',
    'for-parents-caregivers',
    '12-step-based'
  ]
}
```

**Expected Resources**:
- Family support groups (Al-Anon, Nar-Anon)
- Parent/caregiver support groups
- 12-step programs for families

---

### Path 12: Professional

**User Journey**:
1. "I'm a professional seeking resources" 💼

**Tags Collected**:
```javascript
['audience:professional']
```

**Mapped to Taxonomies**:
```javascript
{
  resource_tags: [
    'helpful-articles',
    'evidence-based'
  ]
}
```

**Expected Resources**:
- Evidence-based treatment information
- Professional articles
- Research and data

---

## 🧪 Testing Each Path

### Quick Test Script

For each path above:

1. **Open quiz**: Add `[wpQuizFlow id="2"]` to a test page
2. **Follow path**: Click through the exact options listed
3. **Check console**: Look for:
   ```javascript
   wpFieldFlow: Showing quiz results
     collectedTags: [...]
     taxonomyFilters: {...}
   ```
4. **Verify results**: Do the displayed resources match the expected categories/tags?
5. **Note issues**: If results don't match, check:
   - Do resources exist with those tags?
   - Are resources properly tagged in WordPress?
   - Is the mapping logical?

---

## 🔧 Adjusting the Mapping

If results don't match expectations, edit `QuizNavigator.jsx`:

```javascript
const tagToTaxonomyMap = {
  // Find the tag that's not working
  'stage:crisis': {
    resource_category: ['help-with-treatment'],
    resource_tags: ['interventions', 'treatment']
    // Add or remove terms as needed
  }
};
```

Then refresh the page and test again.

---

## 📊 Coverage Analysis

### Well-Covered Paths ✅
- Crisis support (help-with-treatment + interventions)
- Education (literature + helpful-articles + downloadable-pdfs)
- Support groups (support-groups + 12-step-based)
- Treatment navigation (help-with-treatment + treatment-programs)
- Family support (for-families + for-parents-caregivers)

### Potential Gaps ⚠️
- Insurance navigation (term exists but not heavily mapped)
- Faith-based options (tag exists but not in quiz)
- Adolescent-specific resources (tag exists but not in quiz)
- Grief support (mapped but limited to one tag)

### Future Expansion Ideas 💡
- Add insurance question
- Add age-specific paths (adolescents, young adults)
- Add faith preference question
- Add substance-specific paths (alcohol vs opioids)

---

## ✅ Next Steps

1. **Test all 12 paths** on your site
2. **Verify resources display** for each path
3. **Adjust mapping** if results don't match expectations
4. **Get NOMA feedback** on question wording and results
5. **Iterate** based on real user testing

**You're ready to launch! 🚀**

