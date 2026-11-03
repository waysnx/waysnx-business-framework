---
id: WBF-SPEC-0006
title: Business Function Execution Result
version: 0.2.0
status: Draft – encodes approved decision D1, the Phase 0.1 review decisions and ADR-0009 amendment A1 (Umesh Wani, 2026-11-01)
category: Runtime
owner: WaysNX Technologies Pvt. Ltd.
authors:
  - WaysNX Architecture Team
created: 2026-11-01
updated: 2026-11-01
related:
  - WBF-SPEC-0005 (§13 pipeline, §21 errors, BF-ERR-01…03)
  - ADR-0009 Business Function Result Contract
  - ADR-0010 Fail-Closed Authorization
  - docs/architecture/CORE_CONTRACTS.md §1.3–§1.4
conformance: tests/conformance/fixtures/business-function.json, authorization.json (suite 0.1.0)
---

# WBF-SPEC-0006
# Business Function Execution Result

## 1. Purpose

This specification defines the outcome of one Business Function execution: the closed set of result statuses, how failures are classified into them, and the result-returning API each runtime MUST expose.

It refines WBF-SPEC-0005 §21 (BF-ERR-01 "errors SHALL be categorized", BF-ERR-02 "errors SHALL be distinguishable from successful responses").

Keywords MUST, MUST NOT, SHOULD and MAY are used as defined in WBF-SPEC-0000.

## 2. Result statuses

**RES-01.** Every execution through the result-returning API MUST yield exactly one result whose `status` is one of:

| Status | Meaning | Error category |
|---|---|---|
| `success` | The pipeline completed. `response` holds the business response | — |
| `validation_failure` | The request is malformed or violates the request contract | `ValidationError` |
| `authorization_failure` | No valid allow decision was obtained (ADR-0010: no authorizer, invalid decision, or denial) | `AuthorizationError` |
| `not_found` | A **business resource** the function needs does not exist (for example, an order ID) | `ResourceNotFound` |
| `business_rule_failure` | A business rule or policy rejected the operation | `BusinessRuleViolation` |
| `execution_failure` | Any other failure: framework or configuration errors, missing handlers, invalid responses, unexpected exceptions | the error's own category, otherwise `InternalSystemError` |

**RES-02.** The status set is closed. A runtime MUST reject construction of a result with any other status value. In particular the legacy values `failure` and `rule_failure` are not valid statuses.

**RES-03.** An unexpected error MUST produce `execution_failure`. It MUST NOT produce `success`, and the result-returning API MUST NOT propagate it as an exception.

**RES-04.** `not_found` MUST be used only for missing business resources. A missing handler, a missing registry entry or any other framework lookup failure is `execution_failure`.

**RES-05.** A failed result MUST carry the error category. It SHOULD carry a machine-readable code where one exists (for authorization, the ADR-0010 reason: `no_authorizer`, `invalid_decision`, `denied`).

**RES-06 (BF-ERR-03).** Serialized results MUST NOT include stack traces or exception objects. Diagnostics MAY be kept on a non-serialized accessor.

**RES-09 (BF-ERR-03, ADR-0009 A1).** The public message of an `execution_failure` MUST be exactly `An internal error occurred while executing the business function. Reference: <reference>`. It MUST NOT contain the exception message, class, file, stack or inner exceptions. This invariant MUST hold for every `execution_failure` result, however it is constructed.

**RES-10.** Every failed result MUST carry an `errorReference`. Runtimes use the `executionId`.

**RES-11.** Every `execution_failure` MUST be reported to a server-side error reporter together with the reference and the original error (message and stack). Runtimes MUST provide a default reporter that writes to a log, and MUST let the host replace it. A failing reporter MUST NOT change the result or make the result API throw; the runtime falls back to its default sink.

**RES-12.** `validation_failure`, `authorization_failure`, `not_found` and `business_rule_failure` keep the message written for callers. They are expected outcomes and MUST NOT be reported as unexpected errors.

## 3. Classification

A runtime MUST classify a failure in this order. The first matching rule wins.

| # | Condition | Status |
|---|---|---|
| C1 | The request is not a structured object (map/array). The pipeline is not entered | `validation_failure` |
| C2 | The error carries a WBF category (categorized exception or error class): `ValidationError`, `AuthorizationError`, `ResourceNotFound`, `BusinessRuleViolation` | the matching status from §2 |
| C3 | A plain (uncategorized) error raised while **evaluating business rules** | `business_rule_failure` |
| C5 | A plain *argument-type* error (Laravel `\InvalidArgumentException`) raised while **validating the request** | `validation_failure` |
| C6 | Anything else, including categorized errors with any other category and **any plain error raised while authorizing** | `execution_failure` |

Notes:

- C2 before C3 and C5 is the approved D1 order: categorized exceptions first, controlled pipeline-phase fallback second.
- C3 is approved (D1 option 4). C5 is approved (Phase 0.1 review, 2026-11-01).
- **There is no authorization fallback** (rule C4 was proposed and **rejected** in the Phase 0.1 review). `authorization_failure` arises only from an explicit denial: a deny decision, an invalid decision, no authorizer, or a categorized authorization exception (Laravel `AuthorizationException`, Node `AuthorizationError`). A plain exception from an overridden `checkAuthorization()` or from an authorizer is `execution_failure`. The identifier C4 is retired and not reused.
- Phase fallbacks only apply where an application can run its own code in that phase. In Node the validation and rule phases are framework-controlled and already raise categorized errors, so C3 and C5 never fire there. A throwing Node authorizer is `execution_failure` by C6, the same as Laravel.

## 4. Result shape

**RES-07.** The result MUST expose at least: `status`, `functionId`, `executionId`, `response` (on success), the error category and message (on failure), and the events published.

Current runtime names (both satisfy RES-07):

| Concept | Laravel `ExecutionResult` | Node `BusinessFunctionResult` |
|---|---|---|
| status | `status` | `status` |
| function / execution IDs | `functionId`, `executionId` | `functionId`, `executionId` |
| response | `response` | `response` |
| error category | `errorCategory` (`error.category` in `toArray()`) | `errorCategory` |
| error code | `errorCode` | error `context.reason` |
| public message | `errorMessage` (`error.message` in `toArray()`) | `errorMessage`, `getErrorMessage()` (`error.message` in `toObject()`) |
| reference | `errorReference` (`error.reference`) | `errorReference` (`error.reference`) |
| original error (server-side only, never serialized) | `getException()` | `error` (non-enumerable, excluded from `toObject()`/`toJSON()`) |
| error reporter | constructor argument / `setErrorReporter(callable(string, array))`, default `error_log()` | `setErrorReporter(ErrorReporter)`, default `console.error` |
| failed phase | `failedPhase` | — (error `context.step`) |
| events | `events` | `eventsPublished` |
| duration | `durationMs` | `duration` |

`errors[]` / `warnings[]` / `issues[]` collections (CORE_CONTRACTS §1.3) are **not** part of this version. They are deferred to M4 together with the validation vocabulary.

## 5. Runtime APIs

**RES-08.** Each runtime MUST expose a result-returning API. A runtime MAY additionally expose an exception-throwing API. When it does, the two MUST agree: the throwing API fails exactly when the result API returns a non-`success` status.

| Runtime | Result API | Throwing API |
|---|---|---|
| Laravel | `BusinessFunctionRuntime::run(BusinessFunctionInterface, mixed $request, mixed $caller = null): ExecutionResult` | `BusinessFunctionRuntime::execute()` and `BusinessFunctionAbstract::execute()` (unchanged) |
| Node | `BusinessFunctionRuntime.execute(): Promise<BusinessFunctionResult>` | — |

Laravel categorized exceptions (all implement `CategorizedExceptionInterface::getCategory()` and extend the SPL types `execute()` already documented, so existing `catch` blocks keep working):

| Exception | Extends | Category |
|---|---|---|
| `ValidationFailedException` | `\InvalidArgumentException` | `ValidationError` |
| `AuthorizationException` | `\RuntimeException` | `AuthorizationError` |
| `ResourceNotFoundException(resourceType, resourceId)` | `\RuntimeException` | `ResourceNotFound` |
| `BusinessRuleViolationException` | `\RuntimeException` | `BusinessRuleViolation` |

Node error classes: `ValidationError`, `RequestValidationError` → `validation_failure`; `AuthorizationError` → `authorization_failure`; `ResourceNotFound` → `not_found`; `BusinessRuleViolation` → `business_rule_failure`; everything else, including `HandlerNotFoundError`, registry `NotFoundError` and `ResponseValidationError` → `execution_failure`.

## 6. Migration

### 6.1 Node (breaking, approved direct replacement)

| Before | After |
|---|---|
| `rule_failure` | `business_rule_failure` |
| `failure` + `AuthorizationError` | `authorization_failure` |
| `failure` + `ResourceNotFound` | `not_found` |
| `failure` + anything else | `execution_failure` |
| `validation_failure` + `ResponseValidationError` | `execution_failure` (an invalid response is the function's fault, not the caller's) |
| missing handler: `failure` | `execution_failure` |

`result.failed()` is `status !== 'success'`. `isRuleFailure()` checks `business_rule_failure`. Constructing a result with a legacy status throws.

### 6.2 Laravel (additive)

`execute()` behaves exactly as before. New code SHOULD call `BusinessFunctionRuntime::run()` and branch on `ExecutionResult::$status`. Applications SHOULD throw the categorized exceptions from their hooks to control classification precisely. A `checkAuthorization()` override MUST deny with `AuthorizationException`; a plain exception there is `execution_failure` under `run()`.

### 6.3 Public messages (both runtimes, ADR-0009 A1)

- `execution_failure` results no longer carry the raw exception message. Read the detail from the server-side log (by `errorReference`) or from the in-process exception (`getException()` / `error`).
- Configure the error reporter to route the detail into the host's logging (Laravel: `new BusinessFunctionRuntime([$logger, 'error'])` with any PSR-3-style logger; Node: `runtime.setErrorReporter((message, report) => logger.error(message, report))`).
- Workflow step errors embed the public message. Laravel `WorkflowEngine` now invokes step functions through `run()`; the original exception is the `previous` of the `WorkflowStepException`.

## 7. Conformance

Fixtures BF-001…BF-007 and AUTH-001…AUTH-004 (suite 0.1.0) exercise RES-01…RES-04 and C1–C3, C6. RES-09…RES-12 and C5 are covered by runtime unit tests, not yet by fixtures (planned for suite 0.2.0). Both runtimes pass them with their native statuses; no adapter normalization of statuses is applied.
