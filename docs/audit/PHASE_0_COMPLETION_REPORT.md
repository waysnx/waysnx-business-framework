---
documentId: WBF-AUDIT-0002
title: WBF Foundation Phase 0 – Completion Report
status: Draft – for review by Umesh Wani
version: 0.1.0
date: 2026-10-31
---

# WBF Foundation Phase 0 – Completion Report

| Repository | Working branch | Base | State |
|---|---|---|---|
| `waysnx-business-framework` | `phase0/decisions-docs-conformance` | `main` @ `754560f` | Uncommitted changes |
| `business-framework-laravel` | `phase0/m0-remediation` | `main` @ `8c20977` | Uncommitted changes |
| `business-framework-node` | `phase0/m0-remediation` | `main` @ `d2866ed` | Uncommitted changes |

The audit branches `docs/wbf-foundation-audit` still exist in all three repositories, unchanged and pointing at `main`. Nothing was committed, pushed, merged, tagged or released. No package version, database schema or dependency was changed.

> **Important about the audit work.** The audit deliverables were never committed. Untracked files follow the working tree across branches, so they are present on the Phase 0 branches. Several were then updated **in place** as Phase 0 required:
> - ADR-0005…0008: review sections appended, targeted revisions
> - `REPOSITORY_OWNERSHIP.md`: rewritten
> - `CROSS_RUNTIME_CONFORMANCE.md`: rewritten
> - `ROADMAP.md`: rewritten
> - `IMPLEMENTATION_CAPABILITY_MATRIX.md`: delta section added
> - `CORE_CONTRACTS.md`: IDs updated
> - runtime audit docs: status sections added
>
> No separate copy of their pre-Phase-0 text exists. `docs/audit/REPOSITORY_AUDIT.md` is unchanged and serves as the historical snapshot. Audit and remediation changes therefore cannot be separated by commit **for those files**. They are separated by file in §4.

---

## 1. Executive summary

- **Defects fixed.** All P0/P1 runtime defects in M0 scope are fixed, each with regression tests:
  - Laravel: LAR-01, LAR-02, LAR-03, LAR-04, LAR-08
  - Node: NOD-01, NOD-02, NOD-03, NOD-06, NOD-07
  - Cross-runtime: XR-01 (the same `required` rule gave opposite results)
- **No implicit allow in the normal path.**
  - Validation fails closed for unknown and unimplemented rules.
  - Authorization fails closed and requires an explicit decision object.
  - Caller values are never accepted as proof of authorization.
- **Test suites.**
  - Laravel: 911 tests pass (was 841 tests with 4 errors). 3 tests are marked incomplete; these are documented conformance known differences.
  - Node: 244 tests pass (was 164).
- **Conformance foundation.** 28 fixtures in Core `tests/conformance/fixtures`, with native adapters in both runtimes. **Node passes 28/28. Laravel passes 25/28.** The 3 Laravel differences are real behavioral differences, not hidden by normalization. **Parity is not claimed** beyond the covered fixture areas.
- **Governance.**
  - ADR-0005…0008 reviewed. Two new ADRs proposed: ADR-0009 (result contract) and ADR-0010 (fail-closed authorization).
  - 12 decisions registered. **None is marked Accepted.** All await Umesh's approval.
  - Entity work (M5) stays **blocked**.
- **Documentation.**
  - Ownership specified. Duplicate copies in Core frozen, not deleted.
  - Duplicate document IDs resolved, with a migration map. Normative specs are now `WBF-SPEC-0000…0005`.
  - Hierarchy contradiction resolved.
  - READMEs in all three repositories corrected.

## 2. ADR review results

| ADR | Recommendation | Key findings | Approval |
|---|---|---|---|
| ADR-0005 Repository ownership | **Accept** (with revisions) | Re-verified duplication (§8). Added removal plan R1–R5, propagation process, fixture location, master roadmap ownership. No removal executed | Awaiting |
| ADR-0006 Entity contract | **Revise → Accept** after open items | Revised for D3 (Prisma only when justified, BaseModel untouched), D5 (string IDs, UUID/ULID recommended) and D4 (SemVer strings; `revision` stays an integer). Open: Laravel hook source, Eloquent adapter packaging, Node version-type migration | Awaiting. **M5 blocked** |
| ADR-0007 Validation | **Revise (done) → Accept** | Withdrew the "strict flag, warn first" rollout because it contradicted the directive not to be permissive for backward compatibility. M0 subset implemented. Vocabulary v1 and Laravel Validator adapter stay in M4 | Awaiting. M0 subset implemented under directive |
| ADR-0008 Conformance | **Accept** (with revisions) | Location changed to `tests/conformance/fixtures/`. Defined known-difference policy and normalization (CROSS_RUNTIME_CONFORMANCE §6). CI wiring needs a Core tag (none created) | Awaiting |
| ADR-0009 Result contract (new) | **Accept** | Six statuses per Decision 1. Breaking for Node status values. **Not implemented** | Awaiting |
| ADR-0010 Fail-closed authorization (new) | **Accept** | Behavior implemented under directive. API names provisional | Awaiting (API surface) |

Details: `decisions/ADR-0005…0010`, `docs/architecture/PHASE_0_DECISION_REGISTER.md`.

## 3. Decisions requiring Umesh's approval

| ID | Decision | What approval unblocks |
|---|---|---|
| D1 / ADR-0009 | Standard result statuses | Node status rename (breaking). Laravel categorized exceptions plus a result method. Fixes BF-004/005 in Laravel |
| D2 / ADR-0007 | Validation contract (confirm M0 subset) | M4. `illuminate/validation` dependency (separate approval) |
| D3 / ADR-0006 | Entity architecture | **M5 (currently blocked)** |
| D4 | SemVer strings everywhere | Node `ModuleDefinition`/`EntityDefinition`/`ValidationDefinition.version` number → string (breaking) |
| D5 | String identifiers, UUID/ULID recommended | Entity contract |
| D6 / ADR-0010 | Confirm fail-closed authorization **and the provisional API names** | Stable public API. `authorization_failure` status (via D1). Laravel Gate adapter (dependency) and `wbf:make` authorizer stub |
| D7 | Domain/Capability as metadata. Entity owned by a Domain (proposal) | Promotion of rules E1–E4 to normative text |
| D8 (new) | Workflow `sequence` required | Laravel `WorkflowStep` constructor signature change. Fixes WF-003 |
| D9 / ADR-0005 | Ownership. Removal steps R2–R4 | Deleting or replacing `implementations/laravel`, `packages/*` |
| D10 / ADR-0008 | Conformance suite | Creating the `conformance-v0.1.0` tag. CI wiring |
| D11 (new) | Document ID scheme (`WBF-SPEC`, `WBF-DOC`, aliases) | Committing the spec renames. Resolving 5 unresolvable legacy references |
| D12 (new) | Lifecycle `enabled` default = true | Confirms the Node behavior change |

## 4. Files changed

### 4.1 `waysnx-business-framework`

| Work package | File | Change |
|---|---|---|
| Audit (prior, untracked) | `docs/audit/REPOSITORY_AUDIT.md` | Unchanged in Phase 0 |
| P0-01 | `decisions/ADR-0005…0008-*.md` | Audit files: Phase 0 review sections appended, targeted revisions (0006, 0007, 0008) |
| P0-01 | `decisions/ADR-0009-Business-Function-Result-Contract.md`, `decisions/ADR-0010-Fail-Closed-Authorization.md` | **New** |
| P0-01 | `docs/architecture/PHASE_0_DECISION_REGISTER.md` | **New** |
| P0-03 | `docs/architecture/REPOSITORY_OWNERSHIP.md` | Rewritten (audit file) |
| P0-03 | `implementations/laravel/FROZEN.md`, `packages/FROZEN.md` | **New** (notices only; nothing deleted or moved) |
| P0-04 | `docs/00-governance/DOCUMENT_REGISTRY.md` | **New** |
| P0-04 | `specification/foundation/WBF-DOC-000{0..5}.md` → `WBF-SPEC-000{0..5}.md` | **Renamed.** `id` updated, `legacyId` added, internal references updated. Content otherwise unchanged |
| P0-04 | `docs/architecture/CONCEPTUAL_HIERARCHY.md` | **New** |
| P0-04 | `docs/02-business-architecture/WBF-DOC-0004-Business-Architecture.md`, `WBF-DOC-0005-Meta-Model.md` | Clarifications (derivation flow vs. containment). Business Function added to the meta model |
| P0-04 | `README.md` | Version and status corrections. Parity claim removed. Repository tree fixed. Status vocabulary |
| P0-04 | `ROADMAP.md` | M0–M5 with scope, dependencies, deliverables, acceptance, status and blockers. Original v0.1 lines retained |
| P0-04 | `docs/architecture/CORE_CONTRACTS.md` | Spec ID references updated (audit file) |
| P0-04 | `docs/architecture/IMPLEMENTATION_CAPABILITY_MATRIX.md` | Phase 0 delta section (audit file) |
| P0-05 | `tests/conformance/README.md`, `tests/conformance/fixtures/{validation,workflow,business-function,authorization,lifecycle}.json` | **New** (28 fixtures) |
| P0-05 | `docs/architecture/CROSS_RUNTIME_CONFORMANCE.md` | Rewritten (audit file): implemented layout, normalization §6, report §7 |
| Report | `docs/audit/PHASE_0_COMPLETION_REPORT.md` | **New** (this file) |

Not touched: `implementations/laravel/**` and `packages/**` contents (frozen), `specification/` other than the renames, `schemas/`, CI.

### 4.2 `business-framework-laravel`

| Work package | File | Change |
|---|---|---|
| LAR-08 | `src/Authorization/AuthorizationDecision.php`, `src/Contracts/AuthorizerInterface.php`, `src/Exceptions/AuthorizationException.php` | **New** (provisional API) |
| LAR-08 | `src/Core/BusinessFunctionAbstract.php` | `setAuthorizer()`, `getAuthorizer()`, fail-closed `checkAuthorization()`. Spec ID docblocks |
| LAR-01 | `src/Validation/ValidationFramework.php` | Fail-closed `evaluateRule()` with built-in `required`. `isMissingOrEmpty()`, `resolveTargetValue()`, constants. Default code/message in `createIssue()`. Framework-failure issue override |
| LAR-02, LAR-03 | `src/Workflow/WorkflowEngine.php` | Sequence fix, definition validation, completeness check, `registerBusinessFunction()` / `hasBusinessFunction()` / `setBusinessFunctionRuntime()`, default step invocation through `BusinessFunctionRuntime` |
| LAR-03 | `src/Console/Commands/CreateDemoCommand.php` | Demo registers executable functions with an explicit demo authorizer. Previously it "completed" without executing anything |
| LAR-04 | `tests/Unit/BusinessFunctions/ApplyLeaveRuntimeIntegrationTest.php` | 22 hard-coded dates replaced by `daysFromToday()` |
| LAR-04 | `tests/Unit/BusinessFunctions/ApplyLeaveTest.php` | 1 hard-coded end date replaced |
| LAR-08 | `tests/Unit/Runtime/RuntimeExperimentTest.php` | Doubles given an explicit allow. Business-rule test strengthened (it passed for the wrong reason after fail-closed) |
| Tests (new) | `tests/Unit/Validation/ValidationFailClosedTest.php` (17), `tests/Unit/Authorization/AuthorizationFailClosedTest.php` (11), `tests/Unit/Workflow/WorkflowSequencingTest.php` (14) | **New** |
| P0-05 | `tests/Conformance/ConformanceTest.php`, `tests/Conformance/conformance-known-differences.json`, `phpunit.xml` (new `conformance` testsuite) | **New** / modified |
| P0-04 | `src/Core/DomainAbstract.php`, `src/Core/BusinessCapabilityAbstract.php`, `src/Types/BusinessFunctionInterface.php`, `examples/ApplyLeave.php`, `tests/Unit/Models/{CanonicalBFDTest,DomainTest,ValidatorIntegrationTest}.php` | Docblock spec IDs `WBF-DOC-000N` → `WBF-SPEC-000N` only |
| P0-04 | `README.md` | Corrections (S1.4), validation and authorization sections, compatibility status |
| Docs | `docs/WBF_COMPATIBILITY_AUDIT.md` (Phase 0 status section), `docs/CONFORMANCE_TESTING.md` (rewritten: implemented), `docs/ENTITY_IMPLEMENTATION_PLAN.md`, `docs/VALIDATION_IMPLEMENTATION_PLAN.md` (unchanged since audit) | Audit files (untracked) |

### 4.3 `business-framework-node`

| Work package | File | Change |
|---|---|---|
| NOD-01 | `src/errors/WBFError.ts` | `Object.setPrototypeOf(this, new.target.prototype)` |
| NOD-03, NOD-06 | `src/runtime/BusinessFunctionRuntime.ts` | `Authorizer`, `AuthorizationDecision`, `AuthorizationDecisions`. 4th constructor argument. `setAuthorizer()`, `hasAuthorizer()`. Fail-closed `checkAuthorization()`. Invalid/`null` request handled inside the result path |
| NOD-03 | `src/runtime/index.ts` | Exports for the above |
| NOD-02 | `src/runtime/WorkflowEngine.ts` | Sort by `sequence`. Duplicate ID/sequence validation. Status considers definition errors |
| NOD-07 | `src/runtime/LifecycleHandler.ts`, `src/runtime/LifecycleManager.ts` | Optional `enabled`/`priority` with documented defaults. Type checks at registration |
| D2 | `src/runtime/ValidationFramework.ts`, `src/runtime/ValidationIssue.ts` | Fail-closed unknown/custom rules. Required semantics. `INVALID_RULE`. Protected `evaluateCustom()`. `ValidationIssue.code` |
| NOD-03 | `src/cli/demo/auditDemo.ts` | Explicit demo authorizer |
| Tests | `src/__tests__/runtime.test.ts` | Authorizer fixture. Authorization test rewritten (it asserted the defect). Masking regex replaced by exact assertions |
| Tests | `src/__tests__/workflow.test.ts` | Authorizer fixture |
| Tests (new) | `src/__tests__/phase0-remediation.test.ts` (52) | **New** |
| P0-05 | `src/__tests__/conformance.test.ts`, `src/__tests__/conformance-known-differences.json` | **New** |
| P0-04 | `README.md` | Corrections (S1.5), NOD-07 examples, authorization and validation sections, compatibility status |
| Docs | `docs/WBF_COMPATIBILITY_AUDIT.md` (Phase 0 status section, spec IDs), `docs/CONFORMANCE_TESTING.md` (rewritten), `docs/ENTITY_IMPLEMENTATION_PLAN.md` (spec ID), `docs/VALIDATION_IMPLEMENTATION_PLAN.md` | Audit files (untracked) |

## 5. Defects fixed (with evidence)

| ID | File :: symbol | Before (reproduced) | After | Regression tests |
|---|---|---|---|---|
| LAR-01 | `ValidationFramework::evaluateRule()` | Returned `true` for every rule. `required` on empty input passed | `required` evaluated. Unknown/custom types → error `WBF.VALIDATION.UNKNOWN_RULE`. Missing target → `WBF.VALIDATION.INVALID_RULE` | `ValidationFailClosedTest` (17 pass). VAL-001…009 |
| LAR-02 | `WorkflowEngine::determineNextStep()`, `executeWorkflow()` | Steps `[1,3,2]` ran `A,C` and reported success | Minimum greater sequence. Duplicate/negative sequence and duplicate IDs fail before any step. Skipped steps fail the run (default transitions) | `WorkflowSequencingTest` (14 pass). WF-001/002/004/006 |
| LAR-03 | `WorkflowEngine::executeBusinessFunction()` | Empty. Workflows "completed" without running any function | Runs the registered function through `BusinessFunctionRuntime`. An unregistered function fails the step | `WorkflowSequencingTest::testStepInvokesBusinessFunctionThroughPipelineIncludingAuthorization`, `…testUnregisteredBusinessFunctionFailsInsteadOfSilentSuccess`. WF-005, WF-007 |
| LAR-04 | `ApplyLeaveRuntimeIntegrationTest` | 4 errors ("startDate cannot be in the past"). 5 more tests would have failed by 2026-12 | Deterministic dates relative to `today` | 4 former errors now pass |
| LAR-08 | `BusinessFunctionAbstract::checkAuthorization()` | Empty, so everything was allowed | Fail closed: no authorizer, invalid decision or deny → `AuthorizationException` | `AuthorizationFailClosedTest` (11 pass). AUTH-001…004, BF-003 |
| NOD-01 | `WBFError` constructor | `new RequestValidationError() instanceof RequestValidationError === false`. Bad request gave status `failure` | Correct prototype chain. `validation_failure` / `ValidationError` | `phase0-remediation.test.ts` › NOD-01 (10). `runtime.test.ts` "should reject array as request" (exact). BF-002 |
| NOD-02 | `WorkflowEngine.execute()` | Array order (`[2,1]` ran `B,A`) | Sorted by `sequence`. Duplicates fail before execution | › NOD-02 (7). WF-001…007 |
| NOD-03 | `BusinessFunctionRuntime.checkAuthorization()` | Any truthy caller passed declared permissions | Fail closed. Explicit `{allowed: true}` required | › NOD-03 (13). `runtime.test.ts` "should require an explicit authorization decision (fail closed)". AUTH-001…004 |
| NOD-06 | `BusinessFunctionRuntime.execute()` | `execute(null)` rejected with a plain `Error` | Structured `validation_failure`. `functionFailed` fires | › NOD-06 (3). BF-002 |
| NOD-07 | `LifecycleManager.dispatch()` / `register()` | Handler without `enabled: true` never fired (README example) | Unspecified → enabled. Non-boolean rejected | › NOD-07 (5). LC-001…003 |
| XR-01 | both validation frameworks | Opposite outcomes for `required` | Same outcomes | VAL-001…009 pass in both |

## 6. Regression tests added

| Runtime | File | Tests | Result |
|---|---|---|---|
| Laravel | `tests/Unit/Validation/ValidationFailClosedTest.php` | 17 | Pass |
| Laravel | `tests/Unit/Authorization/AuthorizationFailClosedTest.php` | 11 | Pass |
| Laravel | `tests/Unit/Workflow/WorkflowSequencingTest.php` | 14 | Pass |
| Laravel | `tests/Conformance/ConformanceTest.php` | 28 | 25 pass, 3 incomplete (known differences) |
| Node | `src/__tests__/phase0-remediation.test.ts` | 52 | Pass |
| Node | `src/__tests__/conformance.test.ts` | 28 | Pass |

Existing tests modified, with reasons:

| Test | Modification | Why it is not hiding a defect |
|---|---|---|
| Laravel `RuntimeExperimentTest` | Doubles get an explicit allow. The business-rule test now asserts the exception is *not* an `AuthorizationException` | The doubles relied on the defect (implicit allow). The business-rule test was passing for the wrong reason |
| Node `runtime.test.ts` | Authorizer fixture. Authorization test rewritten. `toMatch(/validation_failure\|failure/)` → `toBe('validation_failure')` | The old authorization test asserted the defect ("any caller succeeds"). The regex masked NOD-01. Assertions are now stricter |
| Node `workflow.test.ts` | Authorizer fixture | Steps execute through the fail-closed pipeline |
| Laravel ApplyLeave tests | Hard-coded dates → relative dates | Same scenarios and assertions |
| Laravel model tests | Docblock IDs only | No behavioral change |

## 7. Test execution results (final run, 2026-10-31)

| Repo | Command | Result |
|---|---|---|
| Laravel | `vendor/bin/phpunit` (Unit + Console + conformance) | **OK: 911 tests, 2292 assertions, 3 incomplete** (WF-003, BF-004, BF-005 known differences). Baseline was 841 tests with 4 errors |
| Laravel | `vendor/bin/phpunit --testsuite conformance` with fixtures absent | 1 skipped, OK (simulates CI) |
| Laravel | `composer validate --strict` | Valid |
| Laravel | `phpcs --standard=PSR12 --warning-severity=0 src tests` (CI command) | 159 errors, **all `Generic.Files.LineEndings`** (CRLF in this Windows working tree with `core.autocrlf=true`; the index is LF) |
| Laravel | same with `--exclude=Generic.Files.LineEndings` | **0 errors** |
| Laravel | `composer audit` | No security vulnerability advisories |
| Node | `npm run lint` (`tsc --noEmit`) | Pass |
| Node | `npm run build` | Pass. `dist/` contains no test or conformance code |
| Node | `npx jest --coverage` | **9 suites, 244 tests pass.** Statements 80.31 %, **branches 63.96 %**, functions 85.14 %, lines 84.48 % |
| Node | `npx jest` with fixtures absent | 1 skipped suite, 216 pass (simulates CI) |
| Node | `npm audit --omit=dev` | **0 vulnerabilities** (zero runtime dependencies) |
| Node | `npm audit` (incl. dev) | 23 (4 low, 17 moderate, 1 high, 1 critical), all transitive dev dependencies: `handlebars` (critical, via Jest coverage reporting) and `brace-expansion` (high, via glob/minimatch). Not shipped. Fixing requires dependency upgrades (needs approval) |
| Both | Conformance negative control | A tampered WF-002 expectation failed in both adapters |

### 7.1 Investigation: the four previous PHPUnit errors

They were **not harmless**. `ApplyLeaveRuntimeIntegrationTest` hard-coded leave dates between 2026-10-01 and 2026-12-03. Once the system date (2026-10-31) passed a start date, `ApplyLeave::validateRequest()` rejected the request with "startDate cannot be in the past". The test then never reached the phase it claimed to test (authorization, business rules, events). Five more tests would have broken by December. The dates are now relative to `today` and avoid the reference data's approved-leave windows (+90…94, +100…102 days).

### 7.2 Investigation: Node branch coverage 60.99 % vs 70 %

- The threshold was **not lowered**. Coverage rose to 63.96 % through the new tests.
- The threshold is never enforced in CI, because `npm test` runs without `--coverage`.
- The remaining gap is concentrated in:
  - `registries/Registry.ts` (23.5 % branches)
  - `registries/BusinessFunctionRegistry.ts` (15.4 %)
  - `registries/EntityRegistry.ts` (25 %)
  - `cli/commands/show.ts` (30 %)
  - `cli/bin.ts` (0 %)
  - `ValidationFramework` range/format paths (43.9 %)
  - constructor guard branches in definitions
- Closing it needs targeted tests for those modules. This was not done in Phase 0 to avoid scope expansion. Recommended in M3.

## 8. Remaining test failures

None unexpected. The 3 incomplete Laravel conformance cases are documented behavioral differences (§12). They were not suppressed: the adapter fails if any of them starts passing.

## 9. Security issues fixed

| Issue | Severity | Fix |
|---|---|---|
| Validation fail-open (LAR-01; NOD-04 custom/unknown) | P0 / P1 | Fail closed in both runtimes |
| Authorization fail-open (LAR-08) | P1 | Fail closed. Explicit decision required |
| Caller value treated as authorization proof (NOD-03) | P1 | Removed. The decision comes from a host-supplied authorizer only |
| Workflow false success: skipped or unexecuted steps reported as completed (LAR-02, LAR-03) | P0 (integrity) | Fixed |

## 10. Outstanding security risks

| Risk | Severity | Location | Status |
|---|---|---|---|
| Mass assignment and fake persistence in `BaseRepository::create/update` | P1 | Laravel `src/Repositories/BaseRepository.php` | Open (M5, ADR-0006) |
| No tenant context or isolation. **Tenant isolation is not implemented and must not be claimed** | P1 | both | Open (M2 context contract) |
| Static mutable state shared between requests (`ApplyLeave` created-requests store, `LeaveReferenceData`) | P2 | Laravel `src/BusinessFunctions`, `src/ReferenceData` | Open (LAR-11) |
| Per-execution state on a `BusinessFunctionRuntime` instance | P2 | Laravel `src/Runtime` | Open (LAR-16) |
| Code generator path traversal (`../` in names) and raw identifier interpolation into generated PHP | P2 | Laravel `src/Console/Generators/*` | Open (LAR-15) |
| Node `make` interpolates names into printed TS | P3 | Node `src/cli/commands/make.ts` | Open |
| Exception information leakage: wrapper messages embed inner messages; Node results carry raw `error` objects. Phase 0 authorization and workflow messages follow the existing pattern (function ID + reason) | P2 | both | Open (ADR-0009 public/internal message split) |
| `new RegExp(rule.pattern)` ReDoS if definitions are untrusted | P3 | Node `ValidationFramework` | Open |
| Predictable execution IDs (`Math.random`) | P3 | Node `BusinessFunctionRuntime` | Open |
| Dev-only dependency advisories (`handlebars` critical, `brace-expansion` high) | P3 (not shipped) | Node devDependencies | Open (needs upgrade approval) |
| Generated Business Functions now deny by default | — (safe default) | Laravel `wbf:make` | Usability follow-up (D6) |

## 11. Conformance fixture status

| Contract | Fixtures | Laravel | Node |
|---|---|---|---|
| Validation | 9 | 9 pass | 9 pass |
| Workflow | 7 | 6 pass, 1 known difference (WF-003) | 7 pass |
| Business Function | 5 | 3 pass, 2 known differences (BF-004, BF-005) | 5 pass |
| Authorization | 4 | 4 pass | 4 pass |
| Lifecycle | 3 | 3 pass | 3 pass |
| **Total** | **28** | **25 pass / 3 fail** | **28 pass / 0 fail** |

All requested scenarios are implemented: validation 1–7 (+2 boundary), workflow 1–7, Business Function 1–5, authorization 1–4, lifecycle 1–3.

**Not implemented (unverified areas):**

- serialization
- error categories beyond BF-004/005
- execution context and correlation propagation
- lifecycle event ordering on failure
- full validation vocabulary
- modules
- entities

See CROSS_RUNTIME_CONFORMANCE §8. **Full parity is not claimed.**

## 12. Remaining behavioral differences

| Difference | Evidence | Resolution |
|---|---|---|
| Missing workflow step `sequence`: Laravel defaults to 0 and runs the step first; Node rejects the definition | WF-003 | D8 (Laravel constructor signature) |
| Laravel cannot distinguish business-rule failures from execution failures (no error categories) | BF-004, BF-005 | ADR-0009 / D1 |
| Representation: Laravel throws, Node returns results. Node status names differ from the D1 vocabulary | normalized (§6.2/6.3), not counted as behavioral | ADR-0009 |
| Node Business Function results drop validation warnings; Node runtime looks up validations by `scope === moduleId` | not fixture-covered | M4 (NOD-04) |
| Laravel has `info` severity; Node does not | not fixture-covered | M4 |
| Node `afterStep` only after success; Laravel emits no `afterStep` | not fixture-covered | M2 lifecycle contract (NOD-08) |
| Node does not propagate the workflow `correlationId` to step executions | not fixture-covered | M2 (NOD-09) |
| Version types (Node number vs string), serialization casing (Laravel `BaseModel::toArray` snake_case) | not fixture-covered | D4, M2 |

## 13. Breaking changes

**Introduced in Phase 0 (behavioral, intended, mandated by the directive; no API removed):**

| Runtime | Change | Who is affected |
|---|---|---|
| Both | Execution without an authorizer now fails | Every application executing Business Functions without an explicit decision. Laravel subclasses overriding `checkAuthorization()` are unaffected |
| Both | Unknown/unimplemented rule types fail (Laravel default `ruleType: 'custom'`; Node `custom`) | Applications relying on silent pass |
| Both | Stricter `required`: whitespace-only and empty arrays fail | Applications relying on the previous lenient check (Node) |
| Laravel | Default `executeBusinessFunction()` runs registered functions; unregistered → step fails | Applications that relied on silent workflow success without overriding |
| Both | Duplicate/negative sequences or duplicate step IDs fail the run. Execution order follows `sequence` | Workflows with inconsistent definitions (previously wrong silently) |
| Node | `instanceof` now correct for execution errors. Invalid requests → `validation_failure` (was `failure`). `null` → result, not rejection | Consumers matching the old status |
| Node | Lifecycle handlers without `enabled` now fire. Non-boolean `enabled`/`priority` throw at registration. `LifecycleHandler.priority`/`enabled` became optional (type widening) | Code relying on silently skipped handlers |

**Additive (provisional) public API:**

- Laravel:
  - `AuthorizationDecision`, `AuthorizerInterface`, `AuthorizationException`
  - `BusinessFunctionAbstract::setAuthorizer()` / `getAuthorizer()`
  - `WorkflowEngine::registerBusinessFunction()` / `hasBusinessFunction()` / `setBusinessFunctionRuntime()`
  - `ValidationFramework` constants and `isMissingOrEmpty()`
- Node:
  - `Authorizer`, `AuthorizationDecision`, `AuthorizationDecisions`
  - `BusinessFunctionRuntime` 4th constructor argument, `setAuthorizer()` / `hasAuthorizer()`
  - `ValidationIssue.code`, `ValidationFramework` static codes and `isMissingOrEmpty()`
  - `evaluateCustom()` changed from private to protected

**Proposed, not introduced (need approval):**

- D1: Node status values
- D4: Node `version` number → string
- D8: Laravel `WorkflowStep` `$sequence` default

## 14. Documentation changes

- **Core:** README (status vocabulary, version, parity claim removed, repository tree), ROADMAP (M0–M5), DOCUMENT_REGISTRY (IDs, migration map, 157 unique IDs), spec renames to `WBF-SPEC`, CONCEPTUAL_HIERARCHY plus descriptive docs 0004/0005 clarified, REPOSITORY_OWNERSHIP, CROSS_RUNTIME_CONFORMANCE, decision register, ADR reviews, capability matrix delta, frozen notices.
- **Laravel:** README (workflow execution, validation, authorization, compatibility status; `afterExecute` and `Providers/` errors removed; `BusinessFrameworkServiceProvider` role corrected), CONFORMANCE_TESTING (implemented), compatibility audit status.
- **Node:** README (status, install path, file tree, shallow immutability, authorization, validation, lifecycle defaults, workflow behavior, uncompilable examples fixed, "identical to Laravel" removed, CLI no longer listed as future work), CONFORMANCE_TESTING (implemented), compatibility audit status.
- **Still open:**
  - START_HERE / REPOSITORY_GUIDE directory maps
  - CHANGELOG entries in all three repositories (release notes are out of Phase 0 scope)
  - 5 unresolvable legacy references (DOCUMENT_REGISTRY §2.1)
  - docs 0011 runtime order

## 15. Recommended next phase

1. **Approvals (Umesh):**
   - D6 API names and D2 M0 subset (to stabilize what was implemented)
   - ADR-0005 (unblocks removals R2–R4)
   - ADR-0008 plus creation of the `conformance-v0.1.0` tag (unblocks CI)
   - D1 and D8 (unblock the remaining 3 Laravel differences)
2. **Commit plan** once approved:
   - Core: one commit for audit docs, one for Phase 0 docs/fixtures, and the spec rename staged with `git add -A` so Git records renames.
   - Each runtime: one commit for docs, one for remediation code and tests.
3. **M3 completion:** wire conformance into both CIs against the tag. Add the fixture JSON schema. Add Node branch-coverage tests for the registries and CLI, then enforce `--coverage` in CI.
4. **M2:** promote CORE_CONTRACTS to `WBF-SPEC-0006+` (result/error, execution context including tenant, lifecycle events, workflow sequencing), with fixtures first.
5. **M4 after M2. M5 stays blocked until ADR-0006 is explicitly accepted.**

---

## Summary of status

| Category | Items |
|---|---|
| **Completed work** | M0 fixes plus regression tests (both runtimes). Conformance foundation (28 fixtures, 2 adapters, report). ADR reviews and decision register. Ownership specification and frozen notices. Document registry and spec renames. Hierarchy resolution. README corrections. Roadmap |
| **Proposed decisions** | D1–D12. ADR-0009 and ADR-0010 (new) |
| **Awaiting Umesh's approval** | All of D1–D12 and ADR-0005…0010. Provisional API names (D6). Tag creation (D10). Removal steps R2–R4 (D9) |
| **Blocked work** | M5 Entity (ADR-0006). Removal of frozen copies (ADR-0005). CI conformance wiring (tag). Node status rename (D1). Node version type (D4). Laravel sequence signature (D8). Laravel Validator adapter (dependency approval) |
| **Remaining defects** | LAR-05, 06, 07, 09–23 and NOD-04 (rest), 05, 08–20, as listed in REPOSITORY_AUDIT. 3 Laravel conformance differences. Security risks in §10 |
| **Recommended next action** | Review and approve D6 + D2 (confirm what was implemented), then ADR-0005/0008 and the conformance tag, then D1/D8 |
