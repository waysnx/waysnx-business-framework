---
documentId: WBF-AUDIT-0004
title: Security Follow-up Report
status: Open – S-05 resolved and S-09 fixed (Phase 0.1 review); others open
version: 1.1.0
date: 2026-11-01
---

# Security Follow-up Report

This report tracks the security items raised in the foundation audit (REPOSITORY_AUDIT §I) and the Phase 0 completion report §9, re-checked against the Phase 0.1 working trees on 2026-11-01. It lists what is still open and recommends an order. **Nothing in this report was fixed in Phase 0.1**: the fixes need Entity work (blocked, ADR-0006), new contracts (M2) or dependency upgrades (not authorized).

The fail-closed authorization (ADR-0010) and fail-closed validation (ADR-0007, M0) fixes from Phase 0 remain in place and are covered by AUTH-001…004 and VAL-004 in both runtimes.

## 1. Summary

| ID | Item | Severity | Runtime | Status | Blocked by |
|---|---|---|---|---|---|
| S-01 | Tenant isolation not implemented | P1 | both | Open | Execution-context contract (M2); repository adapters (M5, ADR-0006) |
| S-02 | Mass assignment and fake persistence in `BaseRepository` | P1 | Laravel | Open | ADR-0006 (Entity work blocked) |
| S-03 | Shared static mutable state | P2 | Laravel (and process-global registries in both) | Open | LAR-11 cleanup; M2 for registries |
| S-04 | Code generator path traversal and identifier injection | P2 | Laravel (P3 Node) | Open | None (can be fixed independently) |
| S-05 | Exception information leakage in result messages | P2 | both | **Resolved** (ADR-0009 amendment A1, 2026-11-01) | — |
| S-06 | Node dev-dependency advisories | P2 (dev only) | Node | Open, **assessed** (business-framework-node `docs/DEV_DEPENDENCY_ADVISORY_ASSESSMENT.md`) | Approval of a lockfile-only update |
| S-07 | Predictable execution IDs | P3 | Node | Open | None |
| S-08 | `new RegExp(rule.pattern)` ReDoS with untrusted definitions | P3 | Node | Open | Validation vocabulary (M4) |
| S-09 | Laravel `examples/ApplyLeave.php` overrode `checkAuthorization()` with an empty body (allow-all template) | P2 | Laravel | **Fixed** (Phase 0.1 review): the example now denies with `AuthorizationException` until real checks are written | — |

## 2. Findings

### S-01 Tenant isolation (P1)

- **State (verified):** neither runtime has a tenant context. Registries are process-global. `BusinessFunctionContext` (Node) and the Laravel `$caller` carry no tenant. No repository scopes queries by tenant.
- **Risk:** any multi-tenant host that builds on WBF today must implement isolation entirely in application code. A README or marketing claim of tenant isolation would be false.
- **Rule until fixed:** documentation in all three repositories MUST NOT claim tenant isolation. Checked on 2026-11-01: the Laravel README says "Tenant context and tenant isolation are not implemented"; no README claims it.
- **Recommendation:** specify `TenantContext` as part of the execution-context contract (CORE_CONTRACTS §3, §8) in M2, with fixtures for propagation into workflow steps. Repository adapters must then require and apply it (M5, after ADR-0006). The architecture work is explicitly out of Phase 0.1 scope.

### S-02 Mass assignment in Laravel `BaseRepository` (P1)

- **State (verified unchanged):** `src/Repositories/BaseRepository.php` `create()`/`update()` assign every input key that matches a model property, and report success without persisting. `transaction()` is a no-op.
- **Risk:** if an application wires this class to real persistence, callers can set any property, including identifiers, audit fields or flags.
- **Recommendation:** short term, mark the class `@deprecated` / experimental in its docblock and README (docs-only, non-breaking). Long term, replace it with the repository port from ENTITY_ARCHITECTURE, using an allow-list from `EntityDefinition`. Code changes wait for ADR-0006.

### S-03 Shared static state (P2)

- **State (verified):** `BusinessFunctions/ApplyLeave::$createdRequests` (static array) and `ReferenceData/LeaveReferenceData` (static arrays and a cached `$approvedLeave`). `BusinessFunctionRuntime` (Laravel) keeps the last execution's context and events on the instance (LAR-16); `run()` keeps that behavior for `getContext()` compatibility.
- **Risk:** under Octane, Swoole or long-running queue workers, data written by one request is visible to the next. With a shared runtime instance, `getContext()` can return another request's data.
- **Recommendation:** move `ApplyLeave` and `LeaveReferenceData` out of `src/` into `examples/` (they are reference data, not framework). Document that `BusinessFunctionRuntime` instances must not be shared across concurrent requests, or rely on `ExecutionResult` only. Registries: resolve per-container scope as part of S-01.

### S-04 Code generator path traversal (P2)

- **State (verified unchanged):** Laravel `src/Console/Generators/*` interpolate `name`, `module` and `--namespace` into file paths and PHP source without validation. Node `src/cli/commands/make.ts` interpolates names into printed TypeScript (P3, printed only).
- **Risk:** `wbf:make` with `../` in a name writes outside the intended directory. Crafted identifiers can inject PHP into generated files. Exposure is limited to developers running the command, or to automation that passes untrusted input to it.
- **Recommendation:** validate identifiers against `^[A-Za-z_][A-Za-z0-9_]*$` (namespaces: segments separated by `\`), reject `/`, `\` in names and `..`, and resolve the target path then check it stays under the base directory. This needs no architectural decision and could be the first fix.

### S-05 Exception information leakage (P2) – resolved 2026-11-01

> **Resolution.** ADR-0009 amendment A1, WBF-SPEC-0006 RES-09…RES-12. Every `execution_failure` now carries only `An internal error occurred while executing the business function. Reference: <executionId>`, plus `errorReference`. The original exception goes to a server-side error reporter (default: Laravel `error_log()`, Node `console.error`; replaceable) with the same reference, and stays available in-process (`getException()` / non-enumerable `error`). Workflow step errors use the public message. Regression tests in both runtimes assert that messages, classes, paths, stacks and inner exceptions do not appear in any serialized form. The text below is the original finding.

- **State (verified):** Laravel `ExecutionResult::$errorMessage` and `toArray()['error']['message']` carry the raw exception message, including for `execution_failure` from unexpected errors (for example a database driver message). Node `BusinessFunctionResult.error` is the raw error; `toObject()` serializes its message. Neither serializes stack traces or exception objects (WBF-SPEC-0006 RES-06).
- **Risk:** a host that returns the result to an API client exposes internal details, contrary to WBF-SPEC-0005 BF-ERR-03.
- **Recommendation:** add a public/internal split: for `execution_failure` caused by an uncategorized error, the public message becomes a fixed text (for example "Business function execution failed") and the original stays on the non-serialized accessor (`getException()` / `error`). Categorized errors keep their message because the application wrote it for callers. This changes the public result content in both runtimes and should be approved as an amendment to ADR-0009, with a conformance fixture.

### S-06 Node dev-dependency advisories (P2, dev only)

- **State (re-run 2026-11-01):** `npm audit --omit=dev`: **0 vulnerabilities** (the package has zero runtime dependencies). `npm audit` including dev: **23** (4 low, 17 moderate, 1 high, 1 critical), all transitive:
  - `handlebars` 3.0.0–4.7.9, **critical**. *Correction (2026-11-01): it is a direct dependency of `ts-jest`, used only by `ts-jest config:init`, not by coverage reporting.*
  - `brace-expansion` ≤ 1.1.20, **high**, via glob/minimatch
- Laravel `composer audit`: no advisories.
- **Risk:** developer machines and CI only. Nothing is shipped (`files: ["dist"]`, no runtime dependencies).
- **Assessment done (2026-11-01):** see business-framework-node `docs/DEV_DEPENDENCY_ADVISORY_ASSESSMENT.md`. The 23 entries come from 4 packages. A lockfile-only, in-range refresh (`handlebars` 4.7.10, `brace-expansion` 1.1.21, `ts-jest` 29.4.15) clears the critical and high entries; a sandbox trial passed all 283 tests. The remaining 21 moderate/low entries need Jest 30 or have no fix (`deepmerge`).
- **Recommendation:** approve the lockfile-only update as a separate commit; evaluate Jest 30 separately. No upgrade was made (Phase 0.1 restriction). Add `npm audit --omit=dev --audit-level=high` to CI so runtime advisories fail the build.

### S-07 Predictable execution IDs (P3)

- **State (verified):** Node `BusinessFunctionRuntime.generateExecutionId()` uses `Date.now()` and `Math.random()`. Laravel uses `Ramsey\Uuid` v4.
- **Recommendation:** use `crypto.randomUUID()` (Node built-in, no dependency). Only matters if execution IDs are used as unguessable references.

### S-08 ReDoS in Node `format` rules (P3)

- **State:** `ValidationFramework` builds `new RegExp(rule.pattern)` from the validation definition.
- **Risk:** only if definitions come from untrusted sources.
- **Recommendation:** document that definitions are trusted configuration. Revisit with the validation vocabulary (M4).

## 3. Recommended order

1. S-04 generator validation (independent, small, Laravel).
2. S-02 docs-only deprecation notice for `BaseRepository`; S-03 move reference data to `examples/` (needs a decision because it removes classes from `src/`).
3. S-06 dependency assessment and approved upgrade PR; add the runtime-only audit to CI.
4. ~~S-05 public/internal message split~~ Done (ADR-0009 A1). A conformance fixture for the safe message is planned for suite 0.2.0.
5. S-01 tenant context in the M2 execution-context contract; enforcement with M5.
6. S-07, S-08 opportunistically.

## 4. Not found

As in the audit: no `unserialize`, `eval`, `new Function`, dynamic `require` of user paths or shell execution in either runtime's `src/`. Not re-scanned in Phase 0.1 beyond the files changed.
