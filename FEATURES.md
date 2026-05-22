# mai_survey — Feature Reference

## Survey Record

Each survey is stored in `tx_maisurvey_survey`:

| Field | Type | Required | Notes |
|---|---|---|---|
| `title` | `input` (max 255) | Yes | Record label; displayed in plugin selector and backend list |
| `description` | `text` | No | Optional introductory text shown above the question set |
| `is_active` | `checkbox` | No | Default `false`; inactive surveys redirect to the list view with a flash message |
| `allow_anonymous` | `checkbox` | No | Default `true`; when `false` an authenticated FE user is required |
| `prevent_duplicates` | `checkbox` | No | Default `true`; blocks repeated submissions from the same session |
| `success_message` | `text` | No | Custom confirmation message; shown on the confirmation screen after submit |
| `questions` | `ObjectStorage<Question>` | No | One-to-many relation; questions are ordered by the `sorting` field |

`SurveyRepository::findActive()` filters by `isActive = true` and sorts by `sorting ASC`.

## Question Record

Each question is stored in `tx_maisurvey_question` with a FK to its parent survey:

| Field | Type | Required | Notes |
|---|---|---|---|
| `survey` | `select` → `tx_maisurvey_survey` | No | Parent survey FK |
| `question` | `input` (max 255, trim) | Yes | The question text displayed to the respondent |
| `type` | `radio` | No | Question type; default `single`; see type table below |
| `required` | `checkbox` | No | Default `false`; required questions must have a non-empty answer |
| `options` | `json` | No | JSON-encoded `string[]` of answer options (used for `single`, `multi`); empty array for other types |
| `scale_min` | `number` (0–999) | No | Default `1`; lower bound of the scale (used for `scale` type) |
| `scale_max` | `number` (0–999) | No | Default `5`; upper bound of the scale (used for `scale` type) |

**Question types:**

| Type value | Label | Rendered as | `options` field |
|---|---|---|---|
| `single` | Single choice | Radio button group | Required — each entry is one option |
| `multi` | Multiple choice | Checkbox group | Required — each entry is one option |
| `text` | Text input | `<textarea>` or `<input type="text">` | Not used |
| `scale` | Scale / rating | Numeric range slider | Not used; `scale_min`/`scale_max` define the range |
| `date` | Date input | `<input type="date">` | Not used |

`getOptionsDecoded(): array<int, string>` parses the JSON `options` string into a PHP array;
returns `[]` for empty or `'[]'` values.

Questions are ordered by the `sorting` field (TYPO3 manual drag-and-drop ordering).

## Submission Record

Each form submission is stored in `tx_maisurvey_submission`:

| Field | Type | Notes |
|---|---|---|
| `survey` | `select` → `tx_maisurvey_survey` | FK to the parent survey |
| `session_hash` | `input` (trim) | SHA-256 session identifier; used for duplicate detection |
| `fe_user_uid` | `number` (0–99999999) | UID of the logged-in FE user, or `0` for anonymous submissions |
| `submitted_at` | `datetime` (read-only) | Set to `new \DateTime()` at persist time |
| `answers` | `ObjectStorage<Answer>` | One-to-many relation to individual answers |

Submissions are sorted by `submitted_at DESC` in the backend list. The `tx_maisurvey_submission`
table uses TYPO3's standard `enableFields` columns (`hidden`, `deleted`, `starttime`, `endtime`).

## Answer Record

Each answer is stored in `tx_maisurvey_answer`:

| Field | Type | Notes |
|---|---|---|
| `submission` | `select` → `tx_maisurvey_submission` | FK to the parent submission |
| `question` | `select` → `tx_maisurvey_question` | FK to the answered question |
| `value` | `text` (5 rows) | The respondent's answer as a plain string |

For multi-select questions (`type = 'multi'`) the selected option values are joined with `,`
before storage. The consumer is responsible for splitting on `,` when reading multi-answers back.
One `Answer` row is created per answered question; unanswered optional questions produce no row.

## Content Element Plugin

A single Extbase plugin is registered:

| Identifier | CType | Controller | Cached actions | Uncached actions |
|---|---|---|---|---|
| `maispace_survey_survey` | `maispace_survey_survey` | `SurveyController` | `list`, `show`, `confirmation` | `step`, `submit` |

The plugin belongs to the `maispace_feature` group in the CType selector.
A FlexForm (`SurveyPlugin.xml`) is attached to the CType and exposes one editor setting: `settings.surveyUid`.

## Frontend Rendering

### `listAction`

Displays all active surveys (no FlexForm override; repository-level `isActive` filter).

| Variable | Type | Description |
|---|---|---|
| `surveys` | `QueryResultInterface<Survey>` | All active surveys, sorted by `sorting ASC` |

### `showAction(Survey $survey)`

Displays a single survey with all its questions (non-step view).
Redirects to the list with a flash message if the survey is not active.

| Variable | Type | Description |
|---|---|---|
| `survey` | `Survey` | The resolved survey |
| `questions` | `array<int, Question>` | All questions as a PHP array (indexed from 0) |

### `stepAction(Survey $survey, int $step = 1)`

Displays one question at a time with a progress indicator.
Clamps `$step` to `[1, totalQuestions]`; redirects to list if survey is inactive.

| Variable | Type | Description |
|---|---|---|
| `survey` | `Survey` | The resolved survey |
| `questions` | `array<int, Question>` | All questions as a PHP array |
| `currentQuestion` | `Question\|null` | Question at index `$step - 1`; `null` if no questions |
| `currentStep` | `int` | Clamped current step number (1-based) |
| `totalSteps` | `int` | Total number of questions (minimum 1) |
| `progressPercentage` | `int` | `round(currentStep / totalSteps * 100)` |

### `submitAction(Survey $survey)`

Processes the submitted form data.

1. Checks `isActive()` — redirects to list if inactive.
2. Calls `SubmissionService::generateSessionHash()` with the current request.
3. If `preventDuplicates` is enabled and `isDuplicate()` returns `true`, adds a flash error and redirects to `confirmationAction`.
4. Reads `answers` argument from the request (an associative array keyed by question UID).
5. Calls `SubmissionService::persist()` to write the submission.
6. Adds a success flash message and redirects to `confirmationAction`.

### `confirmationAction`

Displays the post-submission confirmation screen (cached). No template variables beyond the
standard TYPO3 flash-message container and the survey's `successMessage` field (accessed via FlexForm or TypoScript settings).

### Asset registration

Both `listAction`, `showAction`, `stepAction`, and `confirmationAction` call
`registerAssets()` which adds:
- `EXT:mai_survey/Resources/Public/Css/survey.css` via `AssetCollector` (`mai_survey_css`)
- `EXT:mai_survey/Resources/Public/JavaScript/survey.js` via `AssetCollector` (`mai_survey_js`)

## Session Hash and Duplicate Prevention

`SubmissionService::generateSessionHash(Survey $survey, ServerRequestInterface $request): string`
builds a 64-character hex SHA-256 fingerprint to identify a browser session without PII:

| Priority | Source | Hash input |
|---|---|---|
| 1 | Survey-specific cookie `mai_survey_{uid}` | `sha256("{surveyUid}|{cookieValue}")` |
| 2 | TYPO3 session cookie `fe_typo_user` | `sha256("{surveyUid}|{sessionCookie}")` |
| 3 | Fallback | `sha256("{surveyUid}|{32-char random hex}")` |

`SubmissionService::isDuplicate(Survey $survey, string $sessionHash): bool` delegates to
`SubmissionRepository::findBySurveyAndSessionHash()` which queries `survey = $survey AND sessionHash = $hash LIMIT 1`.

## Backend Results Module

| Property | Value |
|---|---|
| Module ID | `maispace_survey_results` |
| Parent | `web` |
| Access | `user` (any backend user, not admin-only) |
| Icon | `mai-backend-module` |
| Labels | `EXT:mai_survey/Resources/Private/Language/Default/locallang_module.xlf` |

**Actions:**

| Action | Method | Description |
|---|---|---|
| `list` (default) | `SurveyResultsController::listAction()` | Paginated list of all surveys; links to `show` per survey |
| `show` | `SurveyResultsController::showAction(Survey $survey)` | Paginated submissions list for one survey; download CSV button |
| `export` | `SurveyResultsController::exportAction(Survey $survey)` | Streams a CSV file download for the survey |

**CSV export format (`survey-{uid}-results.csv`):**

| Column | Source |
|---|---|
| `submission_uid` | `Submission::getUid()` |
| `submitted_at` | `Submission::getSubmittedAt()?->format('Y-m-d H:i:s')` |
| `fe_user_uid` | `Submission::getFeUserUid()` (0 for anonymous) |
| `session_hash` | `Submission::getSessionHash()` |
| `question` | `Answer::getQuestion()?->getQuestion()` (question text) |
| `value` | `Answer::getValue()` (raw stored value; comma-joined for multi-select) |

Submissions with no answers produce a single row with empty `question`/`value` columns.
The export does not apply any date range or status filter.

## FlexForm Configuration

`Configuration/FlexForms/SurveyPlugin.xml` exposes one backend setting:

| Field | Type | Default | Purpose |
|---|---|---|---|
| `settings.surveyUid` | `select` → `tx_maisurvey_survey` | — | Pins the plugin to a specific survey; overrides the repository query in `showAction` |

When `settings.surveyUid` is set, the plugin renders that survey directly via Extbase argument
mapping (the UID is passed as a URL argument `tx_maisurvey_survey[survey]`).

## TypoScript Configuration

**Constants (`plugin.tx_maisurvey_survey`):**

```typoscript
plugin.tx_maisurvey_survey {
    view {
        templateRootPath =
        partialRootPath =
        layoutRootPath =
    }
    persistence {
        storagePid =
    }
}
```

**Setup defaults:**

| Setting | Default | Purpose |
|---|---|---|
| `settings.showProgressIndicator` | `1` | Show step counter and percentage bar in `stepAction` |
| `settings.allowAnonymous` | `1` | Mirror of `Survey::allowAnonymous`; can be overridden via TypoScript |
| `settings.preventDuplicates` | `1` | Mirror of `Survey::preventDuplicates`; checked before `persist()` |

View paths follow the standard two-level override chain: index `0` = extension default,
index `10` = integrator override via constant.

## Database Tables

### `tx_maisurvey_survey`

| Column | SQL type | Notes |
|---|---|---|
| `uid` | `int unsigned AUTO_INCREMENT` | Primary key |
| `pid` | `int unsigned` | Storage page |
| `tstamp` | `int unsigned` | Last modification timestamp |
| `crdate` | `int unsigned` | Creation timestamp |
| `hidden` | `tinyint(1)` | Standard enable field |
| `deleted` | `tinyint(1)` | Soft-delete flag |
| `sorting` | `int unsigned` | Manual drag-and-drop ordering |
| `title` | `varchar(255)` | Required |
| `description` | `text` | Optional |
| `is_active` | `tinyint(1)` | Default `0` |
| `allow_anonymous` | `tinyint(1)` | Default `1` |
| `prevent_duplicates` | `tinyint(1)` | Default `1` |
| `success_message` | `text` | Optional custom message |

### `tx_maisurvey_question`

| Column | SQL type | Notes |
|---|---|---|
| `uid` | `int unsigned AUTO_INCREMENT` | Primary key |
| `pid` | `int unsigned` | Storage page |
| `tstamp` | `int unsigned` | |
| `crdate` | `int unsigned` | |
| `hidden` | `tinyint(1)` | |
| `deleted` | `tinyint(1)` | |
| `sorting` | `int unsigned` | Question order within a survey |
| `survey` | `int unsigned` | FK → `tx_maisurvey_survey.uid` |
| `question` | `varchar(255)` | Required; trim eval |
| `type` | `varchar(20)` | `single` (default) \| `multi` \| `text` \| `scale` \| `date` |
| `required` | `tinyint(1)` | Default `0` |
| `options` | `json` / `text` | JSON-encoded string array; empty for non-choice types |
| `scale_min` | `int unsigned` | Default `1` |
| `scale_max` | `int unsigned` | Default `5` |

### `tx_maisurvey_submission`

| Column | SQL type | Notes |
|---|---|---|
| `uid` | `int unsigned AUTO_INCREMENT` | Primary key |
| `pid` | `int unsigned` | Storage page |
| `tstamp` | `int unsigned` | |
| `crdate` | `int unsigned` | |
| `hidden` | `tinyint(1)` | |
| `deleted` | `tinyint(1)` | |
| `survey` | `int unsigned` | FK → `tx_maisurvey_survey.uid` |
| `session_hash` | `varchar(255)` | 64-char SHA-256 hex; unique per survey + session |
| `fe_user_uid` | `int unsigned` | FK to `fe_users.uid`; `0` for anonymous |
| `submitted_at` | `datetime` | Set at persist time; read-only in backend |

Default sort: `submitted_at DESC`.

### `tx_maisurvey_answer`

| Column | SQL type | Notes |
|---|---|---|
| `uid` | `int unsigned AUTO_INCREMENT` | Primary key |
| `pid` | `int unsigned` | Storage page |
| `tstamp` | `int unsigned` | |
| `crdate` | `int unsigned` | |
| `hidden` | `tinyint(1)` | |
| `deleted` | `tinyint(1)` | |
| `sorting` | `int unsigned` | Manual ordering |
| `submission` | `int unsigned` | FK → `tx_maisurvey_submission.uid` |
| `question` | `int unsigned` | FK → `tx_maisurvey_question.uid` |
| `value` | `text` | The respondent's answer; comma-joined for multi-select |

All four tables are auto-generated from TCA (`ext_tables.sql` absent); schema is inferred
by TYPO3's schema analyser at install time.

## Architecture Constraints

1. **No SCSS** — `mai_survey` declares no `scssphp/scssphp` dependency. CSS assets are
   pre-compiled statics served from `Resources/Public/Css/`.
2. **No mail dispatch** — the extension never calls `mai_mail`. Submission confirmation is
   shown on-screen only; any notification email must be added downstream.
3. **No sys_category** — surveys and questions use no category taxonomy.
4. **No custom MM tables** — the `questions` relation (survey → questions) is stored via
   the `survey` FK on `tx_maisurvey_question`, not a join table.
5. **`mai_base` dependency** — `SurveyController` extends `AbstractActionController` and
   uses `AppendDataToPluginVariablesTrait`, `PageRendererTrait`, and `FlashMessageTrait`
   from `mai_base`. `SurveyResultsController` extends `AbstractBackendController` and uses
   `BackendCsvExportTrait` and `PaginationTrait` from `mai_base`.
6. **Services.yaml auto-wiring** — all classes under `Classes/` are auto-wired except
   `Classes/Domain/Model/*` (Extbase handles model instantiation).
7. **Layer rule** — `mai_survey` may only depend on `mai_base` (Infrastructure layer);
   it must never depend on feature-layer extensions.
