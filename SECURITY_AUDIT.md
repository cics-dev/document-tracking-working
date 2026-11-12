# Security and route access audit

Date: 2026-11-13. Scope: local Laravel/Livewire code, routes, authorization, document handling, existing tests, and production asset build. No production system was probed or modified. Application behavior has not been changed.

## Confirmed findings, in priority order

1. **High — another user's draft can be overwritten and reassigned.** `app/Livewire/Documents/CreateDocument.php:651` selects a draft using the mutable public `original_document_id` without checking its creator. A sender can open the normal creation component, replace that ID with another user's draft ID, and save. The audit test confirmed both subject replacement and `created_by` reassignment to the attacker. Lock server-derived identifiers and reauthorize draft ownership at submission, inside the transaction.

2. **High — global document listing permission can be bypassed.** `app/Livewire/Documents/ListDocuments.php:19` exposes mutable `mode`; the matching permission is checked only in `mount()`. Changing `mode` from `Sent` to `all` causes `DocumentQueryService::listFor()` to return the global query. The audit test exposed another office's confidential draft subject to a user without `view_all_documents`. Lock the mode and check the required permission whenever the query is executed. Direct navigation checks alone do not cover Livewire updates.

3. **High — external document details and files lack office authorization.** The external list scopes ordinary users to their workflow offices, but `ViewExternalDocument::mount()` and `ExternalDocumentPreviewController::__invoke()` only check a general external-document permission. The audit test confirmed that an external document hidden from the list can still be opened by ID and its PDF preview returned with HTTP 200. Apply the same office/global-access policy to listing, detail, preview, and generation.

4. **High — administrator-created accounts use a shared known password.** `app/Services/UserAccountService.php` assigns the literal `password` to every new account. The audit test verified that this password matches the resulting hash. Setup and administrator seeders also contain this default. Replace shared credentials with an invitation/reset flow or unguessable initial credentials; review already provisioned accounts separately.

5. **Medium — public paid chat endpoint has no rate limit.** `routes/web.php` exposes POST `/chat/send` without authentication or throttling, and the controller invokes a paid model after validation. The audit confirmed guests reach validation and the route has neither middleware. No paid request was made. Add request throttling and an appropriate access policy; if guest chat is intentional, enforce guest quotas and an upstream spending limit.

## Additional code-review concerns

- Uploaded internal and external attachments are stored on the public disk. Configured `storage:link` exposure would let direct static URLs bypass preview authorization. This checkout has no `public/storage` link, so static exposure was not reproduced. Use private storage and authorized delivery for confidential files.
- `ViewDocument::viewAttachment()` selects arbitrary attachment IDs rather than scoping them to the current document; its public `processPDF()` method builds preview data without a document visibility check. `CreateDocument::viewAttachment()` has similar unscoped selection and calls a `processPDF()` method that is not defined in that component. These paths need dedicated authorization/error tests and review of legitimate linked-document access requirements.
- `CreateUser::saveUser()` does not repeat the permission check performed by `mount()`. Review mutation methods for permission revocation between page load and action.

## Validation

- Existing suite: **150 passed, 1 failed**, 740 assertions. The failure reproduced independently in `tests/Feature/SystemManagementRenderingTest.php:136`: the duplicate manual-document-number scenario expected a validation error but received none. Number template/tag changes may have made the fixture stale; this failure alone does not establish that duplicate identical numbers are accepted.
- Added `tests/Feature/SecurityAuditTest.php`: **5 passed**, confirming the five vulnerable behaviors above. These are explicit reproduction probes, not assertions of secure behavior; invert their expectations when implementing fixes.
- `npm run build`: passed. Generated build changes were restored after validation.
- Composer and npm dependency audits could not complete because DNS resolution for Packagist and the npm registry failed. No conclusion about dependency vulnerability status is possible.

This is a targeted local audit, not an exhaustive penetration test. Browser behavior, production server configuration, and deployed storage exposure were not verified.
