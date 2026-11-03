# ADR-0010 Fail-Closed Authorization

| Field | Value |
|---|---|
| Identifier | ADR-0010 (registry alias WBF-ADR-0010) |
| Title | Business Function authorization fails closed and requires an explicit decision |
| Status | **Accepted** – semantics and API names approved by Umesh Wani, 2026-11-01 (decision D6) |
| Date | 2026-10-31 |
| Deciders | Umesh Wani (pending) |
| Related | PHASE_0_DECISION_REGISTER.md D6, REPOSITORY_AUDIT LAR-08, NOD-03, CORE_CONTRACTS §4 |

## Context

Before Phase 0:

- **Laravel.** `BusinessFunctionAbstract::checkAuthorization()` was an empty method, so every execution was allowed unless a subclass overrode it.
- **Node.** `BusinessFunctionRuntime.checkAuthorization()` allowed everything when no `permissions` were declared, and otherwise accepted **any truthy caller value**. It treated caller-supplied data as proof of authorization.

Both were reproduced. WBF-SPEC-0005 requires authorization in the pipeline (BF-SEC) but does not state default behavior.

## Problem Statement

What is the default when no trustworthy authorization decision exists, and what counts as a valid decision?

## Alternatives Considered

1. **Allow when no requirements are declared, deny otherwise.** Undeclared requirements become an implicit allow. This contradicts the Phase 0 directive "No default allow behavior". Rejected.
2. **Fail closed for every execution. An explicit decision from a host-supplied decision point is required (chosen).**
3. **WBF implements roles and permissions itself.** This is host-application responsibility (Phase 0 §15). Rejected.

## Decision

1. Every Business Function execution requires an explicit authorization decision. Absence of a decision point, an invalid decision, or an explicit deny blocks execution before business rules and business logic run. The block happens after validation, so the pipeline order is unchanged.
2. A valid decision is an explicit decision object:
   - Laravel: `AuthorizationDecision::allow()` / `::deny()`
   - Node: `{ allowed: boolean, reason? }` via `AuthorizationDecisions.allow()` / `.deny()`

   Any other return value (`true`, `null`, arrays, strings) is **invalid** and denied.
3. Caller/identity values are inputs to the decision. They are never proof of authorization.
4. Decision points are supplied by the host:
   - Laravel: `AuthorizerInterface` via `BusinessFunctionAbstract::setAuthorizer()`, or an explicit `checkAuthorization()` override that denies by throwing `AuthorizationException` (Phase 0.1 review, 2026-11-01: any other exception thrown while authorizing is unexpected and classified `execution_failure`, never `authorization_failure`)
   - Node: an `Authorizer` function via the 4th constructor argument or `setAuthorizer()`
5. Failure is reported with a machine-readable reason: `no_authorizer`, `invalid_decision` or `denied`.
   - Laravel: `AuthorizationException` (extends `\RuntimeException`), `getReasonCode()`
   - Node: `AuthorizationError`, `context.reason`
6. Until ADR-0009 is approved, Node reports authorization failures with the **existing** status `failure` and `errorCategory: 'AuthorizationError'`. The standardized `authorization_failure` status follows ADR-0009.

## Consequences

- Breaking (behavioral, intended): any Business Function executed without a configured decision point now fails. This affected:
  - Laravel `RuntimeExperimentTest` fixtures and the `wbf:createdemo` command
  - Node `runtime.test.ts`, `workflow.test.ts` and `cli/demo/auditDemo.ts`
  - Generated Business Functions (`wbf:make`) need an authorizer or a `checkAuthorization()` override before they can execute
- No method or signature was removed. Laravel subclasses that override `checkAuthorization()` keep working with `execute()`. For `run()` they must deny with `AuthorizationException`; the reference `ApplyLeave` was changed to do so in Phase 0.1 (it extends `\RuntimeException`, so existing `catch` blocks still match).
- Workflow steps execute through the same pipeline, so each step function needs an authorizer. Laravel passes the workflow `metadata['caller']` and Node passes `WorkflowContext.caller`.

## Phase 0 Review

| | |
|---|---|
| Implemented | Laravel: `src/Authorization/AuthorizationDecision.php`, `src/Contracts/AuthorizerInterface.php`, `src/Exceptions/AuthorizationException.php`, `BusinessFunctionAbstract::{setAuthorizer,getAuthorizer,checkAuthorization}`. Node: `Authorizer`, `AuthorizationDecision`, `AuthorizationDecisions`, `BusinessFunctionRuntime.{setAuthorizer,hasAuthorizer}` |
| Tests | Laravel `tests/Unit/Authorization/AuthorizationFailClosedTest.php` (11). Node `phase0-remediation.test.ts` › NOD-03 (13). Conformance AUTH-001…004 and BF-003 pass in both runtimes |
| Open questions | API naming. Whether a Laravel Gate adapter should ship (would add an `illuminate/auth` dependency). Whether `wbf:make` should scaffold an authorizer stub |
| Recommendation | **Accept** |
| Approval status | **Accepted** – Umesh Wani, 2026-11-01. API names are final (no longer provisional) |
