# NextSteps.md — EXT:mai_survey

## Current Status: 📋 Scaffolded

All PHP classes, TCA, TypoScript, FlexForms, and Fluid templates are in place.
The extension structure is registered but contains no business logic yet.

---

## 1. TYPO3 Integration

- [ ] **Register extension in root `composer.json`** — add `maispace/mai-survey` to the `repositories` array and `require` block if not already auto-loaded via `packages/`
- [ ] **Run `ddev composer install`** inside the TYPO3 instance to wire the extension into the class loader
- [ ] **Run database compare** in the TYPO3 Install Tool to create `tx_maisurvey_survey`, `tx_maisurvey_question`, `tx_maisurvey_submission`, and `tx_maisurvey_answer` tables
- [ ] **Include TypoScript** — add the `maispace/mai-survey` Configuration Set in the site's TypoScript configuration, or include `EXT:mai_survey/Configuration/TypoScript/setup.typoscript` and `constants.typoscript` via the template record

---

## 2. TCA Corrections (use correct mai_base field config types)

The scaffolded TCA used generic field types. Replace with the correct mai_base configs:

- [ ] **`question.type`** — replace `SelectSingleConfig` with `RadioConfig` — it is a fixed enum of 5 values (single/multi/text/scale/date); radio buttons are the correct backend widget
- [ ] **`question.options`** — replace `TextConfig` with `JsonConfig` — this field stores a JSON array of choice labels; `JsonConfig` gives editors a proper JSON editor widget in the backend
- [ ] **`submission.submitted_at`** — replace any `InputConfig` with `DatetimeConfig` — TYPO3 will then handle the timestamp ↔ `\DateTime` mapping automatically

---

## 3. Add SubmissionService (Service Layer)

The controller must not own persistence and hash logic. Mirror the `SubscriberService` pattern from `mai_newsletter`:

- [ ] **Create `Classes/Service/SubmissionService.php`** with:
  - `generateSessionHash(int $surveyUid): string` — generates a deterministic or random hash; stores it in the PHP session (`$_SESSION['mai_survey_hash_<surveyUid>']`) and as a cookie for anonymous users
  - `isDuplicate(Survey $survey, string $hash): bool` — delegates to `SubmissionRepository::findBySurveyAndSessionHash()`
  - `persist(Survey $survey, array $postData, string $hash): Submission` — creates `Submission` + `Answer` records, persists via repositories, returns the saved `Submission`
- [ ] **Wire `SubmissionService` into `SurveyController`** via constructor injection — remove direct repository calls from `submitAction` and `stepAction`
- [ ] **Update `Services.yaml`** — `SubmissionService` is NOT in `Domain/Model/` so autowiring covers it automatically; confirm `public: false` is correct (it is, unless `mai_account` ever calls it)

---

## 4. Domain Logic: SurveyController (Frontend)

- [ ] **`listAction()`** — query `SurveyRepository::findActive()`, assign surveys to view
- [ ] **`showAction(Survey $survey)`** — render survey detail / intro page with question count and step indicator
- [ ] **`stepAction(Survey $survey, int $step = 1)`** — check `SubmissionService::isDuplicate()` first and redirect with flash message if duplicate; paginate questions by step, pass progress data to view
- [ ] **`submitAction(Survey $survey)`** — validate POST data, call `SubmissionService::persist()`, redirect to confirmation
- [ ] **`confirmationAction()`** — render thank-you page using `$survey->getSuccessMessage()`
- [ ] **Asset loading** — call `$this->addJsFile('mai-survey', 'EXT:mai_survey/Resources/Public/JavaScript/survey.js')` and `$this->addCssFile('mai-survey', 'EXT:mai_survey/Resources/Public/Css/survey.css')` in `initializeAction()` via `PageRendererTrait` — do not use raw `AssetCollector` calls

---

## 5. Domain Logic: SurveyResultsController (Backend)

- [ ] **`listAction()`** — list all surveys with submission counts; use `PaginationTrait::paginateQueryResult()` if the list grows large
- [ ] **`showAction(Survey $survey)`** — display aggregated results per question (counts/percentages for single/multi; text list for text; average for scale); use `PaginationTrait::paginateQueryResult()` on the submissions query
- [ ] **`exportAction(Survey $survey)`** — build `$rows` array (header row + one row per submission), call `$this->csvDownloadResponse($rows, 'survey-' . $survey->getUid() . '-results.csv')` from `BackendCsvExportTrait`

---

## 6. Repository Methods

- [ ] **`SurveyRepository::findActive()`** — query where `is_active = 1`, ordered by `sorting`
- [ ] **`SubmissionRepository::findBySurveyAndSessionHash(Survey $survey, string $hash)`** — return first match or null
- [ ] **`SubmissionRepository::findBySurvey(Survey $survey)`** — return all submissions for export/results view

---

## 7. Question Type Partials + mai_theme Components

Implement each partial in `Resources/Private/Partials/Survey/QuestionTypes/`:

- [ ] **`SingleChoice.html`** — radio buttons from `question.options` (JSON-decoded), required attribute support
- [ ] **`MultiChoice.html`** — checkboxes from `question.options` (JSON-decoded)
- [ ] **`TextInput.html`** — `<textarea>` with `required` if `question.required`
- [ ] **`Scale.html`** — range input or radio group from `question.scaleMin` to `question.scaleMax`
- [ ] **`DateInput.html`** — `<input type="date">` with `required` support

In `Question.html` partial, dispatch via `<f:switch>` on `question.type`.

**Use mai_theme components for shared UI elements** — do not write raw HTML for these:
- Next / Back / Submit buttons → `<f:render partial="Atom/Button" arguments="{label: '...', type: 'submit', variant: 'primary'}" contentObjectRenderer="{data}"/>` — gives consistent `.mai-button` styling for free
- Survey title and question headings → `<f:render partial="Atom/Heading" .../>` — consistent heading hierarchy
- Survey card in `List.html` → `<f:render partial="Molecule/Card" .../>` — consistent card layout
- Wrap the plugin output in `<f:render partial="Molecule/SectionWrapper" .../>` — consistent section container

**Do NOT** use the `Organism/Tabs.html` component for the step wizard — it uses a static ARIA tablist and is not designed for form step navigation with POST data.

---

## 8. Multi-Step Wizard (JavaScript)

Implement `Resources/Public/JavaScript/survey.js`:

- [ ] Step navigation: show/hide `.mai-survey__step` sections via `data-step` attributes
- [ ] Progress indicator: update `data-current-step` and width percentage on `ProgressIndicator.html`
- [ ] Client-side validation: prevent Next if required fields in current step are empty
- [ ] Back button: navigate to previous step without losing filled values
- [ ] No framework dependencies — pure vanilla JS IIFE

---

## 9. Backend: Create Test Data

- [ ] Create a **Survey** record with `is_active = 1`, `allow_anonymous = 1`, `prevent_duplicates = 1`
- [ ] Add **Question** child records covering all five types: single, multi, text, scale, date
- [ ] Submit the survey as an anonymous user and verify `tx_maisurvey_submission` and `tx_maisurvey_answer` records appear
- [ ] Submit a second time and verify the duplicate prevention flash message is shown

---

## 10. Extbase Mapping Verification

- [ ] Confirm inline `questions` relation on `Survey` is resolved via Extbase (foreign-field: `survey`)
- [ ] Confirm inline `answers` relation on `Submission` is resolved (foreign-field: `submission`)
- [ ] Confirm `submitted_at` maps to `\DateTime` in `Submission` model after switching to `DatetimeConfig` in TCA

---

## 11. Unit Tests

- [ ] `Survey::getQuestions()` — assert returns `ObjectStorage`
- [ ] `Question::getOptionsDecoded()` (add helper method) — assert JSON decode of `options` field
- [ ] `SubmissionService::generateSessionHash()` — assert returns non-empty string, deterministic per survey UID
- [ ] `SubmissionService::isDuplicate()` — mock `SubmissionRepository`, assert delegates correctly
- [ ] `SubmissionService::persist()` — mock repositories, assert `Submission` and `Answer` records created
- [ ] `SubmissionRepository::findBySurveyAndSessionHash()` — mock query, assert correct constraint

---

## 12. Localisation (uk / ar)

- [ ] Add `uk.locallang.xlf` (Ukrainian) and `ar.locallang.xlf` (Arabic) under `Resources/Private/Language/Default/`
- [ ] Verify RTL layout does not break the multi-step form when `ar` locale is active

---

## 13. Mitmach-Matrix (Site-Specific Feature)

The Mitmach-Matrix is an interactive volunteer availability grid.

- [ ] Define time-slot, language, and interest-area categories as question options in a dedicated Survey record
- [ ] Render as a grid/matrix layout — a dedicated Fluid template, not the standard step wizard
- [ ] **Consider JSON/AJAX submission via `AbstractApiMiddleware`** — extend `Maispace\MaiBase\Middleware\Api\AbstractApiMiddleware` to create a `SurveySubmitMiddleware` that accepts a JSON POST and returns a JSON response; this avoids a full page reload and is a better UX for the interactive matrix grid than a standard Extbase form POST
  - Register the middleware in `Configuration/RequestMiddlewares.php` scoped to `frontend`
  - Use `$this->decodeJsonBody($request)` and `$this->jsonResponse([...])` / `$this->errorResponse(...)` from the base class
- [ ] On submission, trigger admin email notification via `mai_mail` (suggested dependency)
- [ ] Register a second plugin `MaiSurvey/Matrix` in `ext_localconf.php` and a `maispace_survey_matrix` CType in `tt_content.php`

---

## 14. Promote to 🔨 In Progress

Update `Extensions.md` once items 1, 4, 5, and 6 above are verified and at least one unit test exists.

## 15. Promote to ✅ Implemented

Update `Extensions.md` once:
- All unit tests pass (`composer test:unit`)
- `composer lint:check` exits 0
- All five question types render and submit correctly end-to-end in the browser
- CSV export downloads a valid file from the backend module
- Duplicate prevention works for both anonymous (cookie) and authenticated (fe_user) submissions
