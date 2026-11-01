# ADR-0009 Business Function Result Contract

| Field | Value |
|---|---|
| Identifier | ADR-0009 (registry alias WBF-ADR-0009) |
| Title | Standardized Business Function result and error classification |
| Status | **Accepted** – approved by Umesh Wani, 2026-11-01 (decision D1, options recorded below) |
| Date | 2026-10-31 |
| Deciders | Umesh Wani (approved 2026-11-01; amendment A1 approved 2026-11-01) |
| Related | CORE_CONTRACTS.md §1.3–1.4, PHASE_0_DECISION_REGISTER.md D1, REPOSITORY_AUDIT XR-02, XR-05, LAR-07 |

## Context

- **Laravel.** `BusinessFunctionAbstract::execute()` returns a raw array. Failures throw native `\InvalidArgumentException` (validation) or `\RuntimeException` (authorization, rules), or since Phase 0 `AuthorizationException` (a `\RuntimeException`). There is no error category. `BusinessFunctionRuntime::execute()` rethrows.
- **Node.** `BusinessFunctionRuntime.execute()` returns a `BusinessFunctionResult` with `status ∈ {success, failure, validation_failure, rule_failure}` and an `errorCategory`. It never throws for expected failures. Since Phase 0 this also holds for `null` input.
- **Spec.** WBF-SPEC-0005 §21 requires classified, distinguishable errors (BF-ERR-01/02) but defines no result shape. The two runtimes therefore cannot be compared without normalization. Conformance fixtures BF-004 and BF-005 currently fail in Laravel because rule failures and execution failures are indistinguishable.

## Problem Statement

What observable outcome must every Business Function execution produce, independently of runtime exception idioms?

## Alternatives Considered

1. **Exceptions are normative.** Laravel already behaves this way. It would force Node to throw and lose structured results. Error categories would still need defining. Rejected.
2. **Result object is normative. Runtimes keep their native exception idioms internally and translate at the execution boundary (chosen direction).**
3. **Both are normative.** Doubles the contract surface and the conformance burden. Rejected.

## Decision (proposed)

1. Every execution through the runtime execution boundary yields exactly one **result** with `status`:

   | Status | Meaning |
   |---|---|
   | `success` | Response produced |
   | `validation_failure` | Request rejected by contract or rule validation (before authorization) |
   | `authorization_failure` | No valid explicit allow decision (D6) |
   | `not_found` | A referenced business resource does not exist |
   | `business_rule_failure` | A business rule rejected the operation |
   | `execution_failure` | Unexpected error during execution (internal detail not exposed, BF-ERR-03) |

2. Unexpected internal exceptions may occur inside runtimes, but the execution boundary translates them into `execution_failure`. It never translates them into `success`, and never applies a broad catch that hides the category.
3. Results carry `errors[]` with `{category, code, message}` and `warnings[]` (validation warnings are never dropped).
4. Runtimes may additionally expose an exception-throwing API (Laravel `execute()`), provided it maps 1:1 to the result statuses.

## Consequences

- **Node.** Rename or extend statuses: `rule_failure` becomes `business_rule_failure`, and `failure` splits into `authorization_failure`, `not_found` and `execution_failure`. **This is breaking** for consumers that switch on `status`. Migration: keep the old values available for one minor release behind `legacyStatus`, or add a new field first.
- **Laravel.** Additive. Introduce error-category exceptions (`ValidationError`, `BusinessRuleViolation`, …), subclassing the documented `\InvalidArgumentException` / `\RuntimeException` so existing `catch` blocks keep working. Add a result-returning runtime method. `execute()` is not removed.
- **Conformance.** BF-004 and BF-005 become passable in Laravel. The status normalization (CROSS_RUNTIME_CONFORMANCE §6.2) can then be deleted.

## Migration implications

Public contract change in both runtimes. **Must not be implemented before approval** (Phase 0 rule). In Phase 0 only the fail-closed authorization behavior (ADR-0010) and the Node `null`-input result (NOD-06) were implemented, using **existing** status values.

## Phase 0 Review

| | |
|---|---|
| Recommendation | **Accept** (direction given by the Phase 0 directive, Decision 1) |
| Open questions | Exact Node migration path. Whether `not_found` covers missing handlers (proposed: no, that is `execution_failure`). The shape of `errors[]`. Whether Laravel gets a new runtime method name (`run()`) or an option |
| Approval status | **Accepted** – Umesh Wani, 2026-11-01 |

## Approved options (Umesh Wani, 2026-11-01)

1. **Node.** Status values are replaced directly. There is no dual field, and the migration is documented.
2. **Laravel.** `BusinessFunctionRuntime::run(): ExecutionResult` is added. `execute()` keeps throwing.
3. **Laravel.** Categorized exceptions extend the existing base exceptions, so existing `catch` blocks keep working.
4. **Business-rule classification.** A categorized exception decides first. A plain exception from `evaluateBusinessRules()` falls back to `business_rule_failure` by pipeline phase.
5. **`not_found`.** Used only for missing business resources. A missing handler is `execution_failure`.

Scope note: ADR-0009 Decision 3 (`errors[]` / `warnings[]` collections on results) is **not** implemented in Phase 0.1. Surfacing warnings in results depends on the M4 validation integration (NOD-04). Results carry a single error category and code.

## Phase 0.1 review decisions (Umesh Wani, 2026-11-01)

### Classification fallbacks

| Rule | Decision |
|---|---|
| Plain `\InvalidArgumentException` from the **validation** phase → `validation_failure` | **Approved** as a controlled phase-based fallback. Categorized exceptions take precedence. A plain `\InvalidArgumentException` from any other phase is `execution_failure` |
| Plain exception from an overridden `checkAuthorization()` (or a throwing authorizer) → `authorization_failure` | **Rejected.** Only explicit denials produce `authorization_failure`: an `AuthorizationDecision::deny()` / `AuthorizationDecisions.deny()`, a non-decision (`invalid_decision`), no authorizer (`no_authorizer`), or a categorized authorization exception (`AuthorizationException` / `AuthorizationError`). Any other exception while authorizing is unexpected and produces `execution_failure` |

The complete fallback list is therefore: business-rules phase (approved option 4) and validation phase with `\InvalidArgumentException`. There is no authorization-phase fallback. Normative text: WBF-SPEC-0006 §3.

### Amendment A1: public error messages (BF-ERR-03) – **Approved**

WBF-SPEC-0005 BF-ERR-03 forbids exposing internal implementation details through business errors. Before this amendment, both runtimes passed the raw exception message of unexpected failures into the result (Laravel `ExecutionResult::$errorMessage`; Node `BusinessFunctionResult.error` and `toObject()`).

Decision:

1. **Unexpected failures.** For every `execution_failure` the public message is the fixed text `An internal error occurred while executing the business function. Reference: <reference>`. It never contains the exception message, class, file, stack or inner exceptions. This holds whoever constructs the result.
2. **Reference.** Every failed result carries `errorReference`, equal to the `executionId`. The same reference appears in the server-side report, so support can correlate a caller's report with the log entry.
3. **Server-side diagnostics are not weakened.** Each `execution_failure` is sent to a runtime error reporter with the reference, function ID, execution ID, phase/category and the original exception (message and stack). The default reporter logs it (Laravel `error_log()` with the full exception string; Node `console.error` with the error object). Hosts can plug in their logger (Laravel: any PSR-3-style `error(string, array)` callable; Node: `setErrorReporter()`). A failing reporter falls back to the default sink and never changes the result. The original exception also stays available in-process (`ExecutionResult::getException()`, Node `result.error`, which is non-enumerable and excluded from serialization).
4. **Expected failures keep their messages.** `validation_failure`, `authorization_failure`, `not_found` and `business_rule_failure` keep the message the application or framework wrote for callers. They are expected outcomes and are not reported as unexpected errors.
5. **Workflows.** A step failure recorded in a workflow result uses the Business Function result's public message, so workflow results do not leak raw messages either.
6. **Equivalence.** Same message text, same reference rule, same status scope in both runtimes.

Evidence: Laravel `tests/Unit/Runtime/ExecutionResultTest.php` and `tests/Unit/Workflow/WorkflowSequencingTest.php`; Node `src/__tests__/phase0-remediation.test.ts` ("ADR-0009 amendment" and "authorization has no exception fallback" blocks).

Compatibility: consumers that displayed or parsed the raw message of an `execution_failure` must read it from the server-side log (by reference) or from the in-process exception instead. Node `getErrorMessage()` and `toObject().error.message` now return the public message. `toObject().error` gains `reference`.
