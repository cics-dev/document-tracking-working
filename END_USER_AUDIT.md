# Ordinary end-user audit

Date: 2026-11-13

Scope: problems reachable by normal links, buttons, form inputs, saved drafts, previews, and configured role permissions. No request tampering, developer tools, hidden identifier changes, or guessed document IDs were used for the findings below. The earlier technical security findings are deferred, as requested.

Method: Laravel HTTP tests and Livewire component tests exercise the routes and server actions wired to the actual interface. These are automated integration reproductions, not a browser-driven visual or JavaScript assessment. The database is isolated SQLite in memory and uploads use fake storage. No existing user records were changed. Application code has not been modified.

## Confirmed problems

### 1. Save as Draft loses attachments, typed recipient, and carbon-copy selections

**Normal steps:** Write a document, enter a free-text recipient, upload an allowed file, add a CF office, click Save as Draft, then reopen it from Sent Documents → Edit.

**Observed:** The attachment is not persisted and both attachment lists are empty when reopened. The typed recipient is stored in `to_text` but is not restored to the form. CF selections are not persisted and reopen empty. These were reproduced in three independent tests. A user may send an incomplete document later unless they notice and re-enter everything.

**Cause:** `CreateDocument::submitDocument()` persists attachments/steps/CFs only inside its send-only branch (lines 657–664). `handlePassedData()` does not restore `document_to_text`.

**Priority:** High — loss of user-entered work.

### 2. Automatic sending can fail because a manual draft already reserved the same number

**Normal steps:** Save a manual-number draft using the next available sequence (for example ORD-OL-1-2026). Open Write Document again, compose another document of the same type and office, leave automatic numbering enabled, and click Send.

**Observed:** Sending throws a database unique-constraint exception and the second document is not saved. The existing record is protected by the database, but the user encounters an error instead of a successfully sent document or a helpful validation message.

**Cause:** `generateDocumentNumber()` excludes drafts when finding the last number, even though a manual-number draft already occupies a unique document number.

**Priority:** High — blocks ordinary sending.

### 3. Dashboard history reveals activity from documents the user cannot access

**Normal steps:** Log in and open Dashboard.

**Observed:** A sender without permission to view another office's document sees that document's number and its activity description in Document History. The test separately verifies that normal document authorization denies this sender access to that document.

**Cause:** `DashboardController` loads the latest eight document logs globally without applying the document visibility rules. The view displays document numbers, actor names, and descriptions. The test does not establish exposure of the document body.

**Priority:** High if activity metadata is confidential. If the organization intentionally wants a global activity feed, clarify that requirement and restrict sensitive descriptions accordingly.

### 4. Draft rows display a View button that fails for unnumbered drafts

**Normal steps:** Create a draft with automatic numbering, open Sent Documents, and click its View button.

**Observed:** The draft's document number is null, but the row renders `viewDocument('')`. Calling this displayed action raises a missing route parameter exception because the View route requires a document number. Edit remains available.

**Cause:** The View button is shown for every row, including drafts that do not have a document number.

**Priority:** Medium.

### 5. Existing attachment Remove button fails

**Normal steps:** Open a draft or revision with an existing attachment and click the X beside the attachment.

**Observed:** The action throws Livewire's `MethodNotFoundException`.

**Cause:** The view invokes `removeExistingAttachment(id)`, but `CreateDocument` does not implement that action. The test uses an existing stored attachment, not an arbitrary attachment ID.

**Priority:** Medium — users cannot remove existing files through the provided control.

### 6. Previewing an attached internal document while revising fails

**Normal steps:** Open a returned document for revision. If it contains a linked internal document, click the eye beside that existing attachment.

**Observed:** The action throws a missing-method exception.

**Cause:** `CreateDocument::viewAttachment()` calls `$this->processPDF(...)`, but this component does not define `processPDF`. Such a method exists in a different component (`ViewDocument`). The linked record in the test belongs to the user's own office.

**Priority:** Medium.

### 7. Refreshing a PDF preview returns 404

**Normal steps:** Click Preview, let its PDF load, then refresh that preview URL.

**Observed:** The first request returns a PDF successfully. The same URL immediately returns 404 on the second request.

**Cause:** `DocumentPreviewController` uses `session()->pull($key)`, deleting the preview payload on the first load. This also makes ordinary reopening of that preview URL fail. Browser PDF range-request behavior was not tested.

**Priority:** Medium.

### 8. Reopened manual-number drafts show incorrect numbering state in Preview

**Normal steps:** Enable Manual Document No., enter sequence 42 and a year, Save as Draft, reopen it, and click Preview.

**Observed:** The saved number exists in the database, but the manual switch resets off and sequence resets empty. Preview data no longer contains the saved number and uses an automatic placeholder instead. This test does not establish that the saved database number is lost.

**Cause:** Draft loading restores `revision_document_number` but does not restore the manual-number input state; preview chooses its number from that input state.

**Priority:** Medium — confusing or inaccurate preview.

### 9. External-response-only users are sent to an access-denied page after successful Send

**Normal steps:** A user with `send_external_documents` but without `send_documents` opens their assigned external letter, clicks a configured response-generation button, writes the response, and clicks Send.

**Observed:** The response is saved with status Sent, then the system redirects to Sent Documents, which returns 403 for that role. A user can mistake the resulting error for a failed send.

**Cause:** The creation component permits the external response context, but its final redirect always targets the internal Sent Documents list.

**Priority:** Medium. Applies to this supported combination of configured permissions, not every role.

## Route and navigation results

For an ordinary sender with an assigned office, 22 route checks covered Dashboard, settings, Write Document, physical upload, Sent Documents, management pages, external pages, Received Documents, and All Documents.

- Allowed pages loaded successfully.
- Management, external, Received, and All Documents routes returned 403 when the corresponding permission was absent.
- Checked management and All Documents navigation links were hidden.
- The external-response redirect mismatch above is a separate reproduced route/permission problem.

## Test evidence

`php artisan test tests/Feature/EndUserAuditTest.php`

Result: **12 passed, 72 assertions**: eleven problem-reproduction scenarios and one route/navigation test. These explicitly assert the current faulty behavior so findings can be reproduced; they must be changed to assert the intended behavior when fixes are implemented. Passing reproduction probes do not mean the application is free of bugs.

Earlier baseline: 150 tests passed and one existing test failed at `SystemManagementRenderingTest.php:136`. That test uses a hard-coded `TEST(ADMIN)` number while its newly created office has no explicit number tag, so it is not reliable evidence of an actual duplicate-number bypass. The separate automatic-number collision above was reproduced using the current number format and does cause a real database exception.

Not covered: browser rendering, JavaScript timing, mobile layout, production deployment behavior, real mail delivery, real file-converter availability, and simultaneous requests. The report is a targeted integration audit, not a claim that every ordinary workflow is correct.
