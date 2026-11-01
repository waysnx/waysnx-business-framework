---
documentId: WBF-AUDIT-0001
title: WBF Foundation Repository Audit
status: Draft – for review
version: 0.1.0
date: 2026-10-31
owner: WBF Maintainers
reviewer: Umesh Wani
---

# WBF Foundation Repository Audit

This audit covers the three repositories that make up the WaysNX Business Framework (WBF). It maps what actually exists, compares it to what the documentation claims, and lists the corrections needed before WBF-003 (Entity/BaseModel) implementation starts.

Every finding has an ID (e.g. `LAR-02`) that the other architecture documents reference.

## 0. Audit baseline

| Repository | Role | Branch / commit audited | Tags | Last commit |
|---|---|---|---|---|
| `waysnx/waysnx-business-framework` | Specification (primary) | `main` @ `754560f` | `v1.0.0-preview` (→ `65ae999`) | 2026-08 / 09 |
| `waysnx/business-framework-laravel` | PHP / Laravel runtime | `main` @ `8c20977` | `v0.1.0` | 2026-09-29 |
| `waysnx/business-framework-node` | TypeScript / Node.js runtime | `main` @ `d2866ed` | none | 2026-09-29 |

### Evidence legend

| Marker | Meaning |
|---|---|
| **[V-exec]** | Verified by executing code or tests during the audit |
| **[V-code]** | Verified by reading source code at the cited location |
| **[V-doc]** | Verified by reading documentation at the cited location |
| **[Unverified]** | Could not be verified; stated as an open question |

### Commands executed

| Repo | Command | Result |
|---|---|---|
| Laravel | `composer install` | OK (from tracked `composer.lock`) |
| Laravel | `vendor/bin/phpunit` | **841 tests, 2140 assertions, 4 errors** – all in `ApplyLeaveRuntimeIntegrationTest` ("startDate cannot be in the past") |
| Laravel | `composer validate --strict` | OK |
| Laravel | `composer lint` (PSR-12) | Fails locally on every file with CRLF line endings. Index is LF; working tree is CRLF via `core.autocrlf=true`. Environment artifact, see LAR-20 |
| Laravel | `composer audit` | Not completed – packagist.org unreachable from audit machine |
| Node | `npm ci` | OK. Deprecation warnings for transitive `inflight@1.0.6`, `glob@7.2.3` |
| Node | `npm run lint` (`tsc --noEmit`) | OK |
| Node | `npm run build` | OK |
| Node | `npx jest --coverage` | **164 / 164 passed**. Coverage: statements 77.81 %, branches **60.99 %** (threshold 70 % → fails), functions 81.35 %, lines 81.97 % |
| Node | `npm audit` | `--omit=dev`: 0 vulnerabilities. Full audit not completed – registry unreachable |
| Both | Temporary probe scripts (created, executed, deleted) | Reproduced LAR-01, LAR-02, NOD-01, NOD-02, NOD-06, NOD-07 |
| Primary | none | No build, tests, or CI exist |

---

## 1. Executive summary

1. **The specification is less mature than the README claims.** Only `specification/foundation/WBF-DOC-0000…0005` are normative (RFC-2119 keywords). Everything in `docs/` is descriptive or a stub. All four ADRs, all governance docs, all templates and all six JSON schemas are placeholders. The README marks Specifications, Standards and Governance as "✅ Complete" (PRI-02).
2. **There is one strong normative anchor:** `WBF-DOC-0005 Business Function Specification`. Both runtimes partially implement it, and they implement different parts.
3. **Laravel and Node are not functionally equivalent today.** The same validation definition produces opposite outcomes (XR-01). Business Function results use different models (XR-02). Definitions have different fields and types (XR-03). Workflows have different execution responsibility (XR-04). Both READMEs claim equivalence (XR-07).
4. **Two silent-correctness defects in Laravel (P0):**
   - `ValidationFramework::evaluateRule()` returns `true` by default, so every rule passes, including `required` (LAR-01).
   - `WorkflowEngine::determineNextStep()` silently skips steps declared out of sequence order and still reports success (LAR-02).
5. **Node has P1 defects:**
   - Execution-error subclasses lose their prototype, so request-validation failures are reported as generic `failure` (NOD-01).
   - Workflow steps run in array order, ignoring `sequence` (NOD-02).
   - Authorization passes any truthy caller (NOD-03).
6. **Entity/BaseModel does not exist as a business-entity concept in either runtime.**
   - Laravel's `BaseModelAbstract` is the base class for *meta-model artifacts* (Module, Domain, Capability, BusinessFunction, Workflow). It is not Eloquent.
   - Laravel `BaseRepository` does not persist anything (LAR-06).
   - Node has interfaces only (NOD-05).
7. **Ownership is unclear.**
   - The primary repo contains a stale full copy of the Laravel package (`implementations/laravel`, PRI-05).
   - It also contains PHP code in `packages/core` and `packages/types` (PRI-06), byte-identical to Laravel `src/Core` and `src/Types`.
8. **No shared conformance suite exists**, so no parity claim can currently be backed by tests (PRI-08).

Recommended sequence:
1. Fix the P0/P1 runtime defects behind tests.
2. Establish ownership and stop the drift.
3. Publish the missing normative contracts: BFD schema, error categories, validation result, execution context.
4. Stand up the conformance suite.
5. Only then implement the Entity architecture (ADR-0006).

---

## A. Existing architecture

### A.1 Primary repository (`waysnx-business-framework`)

| Area | Location | State |
|---|---|---|
| Normative foundation specs | `specification/foundation/WBF-DOC-0000…0005.md` | Draft, normative. 0005 (Business Function) is the most complete: 1153 lines, 106 SHALL |
| Descriptive architecture docs | `docs/00-governance … docs/14-body-of-knowledge` (WBF-DOC-0000…0140) | Descriptive, no normative keywords. ~60 files are ~240-byte placeholder stubs |
| ADRs | `decisions/ADR-0001…0004` | All stubs ("Status: Planned", "_To be defined._") |
| JSON schemas | `schemas/*.schema.json` (capability, domain, event, module, service, workflow) | Each file contains only `{"$schema": ".../2020-12/schema"}` |
| PHP code | `packages/core/*.php`, `packages/types/*.php`, `packages/core/tests/*.php` | Identical to Laravel `src/Core`, `src/Types`. Tests not run anywhere |
| Laravel copy | `implementations/laravel/**` | Full package copy, last changed 2026-08-31, behind the standalone repo |
| Conformance / tests / tools | `specification/conformance/`, `tests/`, `tools/`, `examples/`, `packages/cli`, `packages/validator` | `.gitkeep` only |
| CI | `.github/workflows/` | `.gitkeep` only |

Normative architecture (spec 0004):
- Layers: Applications → Modules → Business Function Runtime → WBF Core → Platform (external).
- Ownership hierarchy: Module ⊃ Domain ⊃ Capability ⊃ Business Function (OR-01…OR-06).
- Dependency rules: DR-01…DR-06.

Normative Business Function pipeline (spec 0005 §13):
1. Request
2. Contract Validation
3. Authorization
4. Business Rule Evaluation
5. Business Execution
6. State Changes
7. Event Publication
8. Observability
9. Response

### A.2 Laravel implementation (`business-framework-laravel`, `waysnx/business-framework`)

Requirements: PHP ^8.3, illuminate/support and container ^13. Namespace `WaysNX\BusinessFramework`.

| Layer | Classes | Notes |
|---|---|---|
| Framework-independent types | `Types\EntityInterface`, `AuditableInterface`, `MetadataInterface`, `BusinessFunctionInterface`, `SerializableInterface`, `VersionableInterface` | Serializable/Versionable unused |
| Laravel contracts | `Contracts\*Interface` | Four are empty sub-interfaces of `Types\*`. `RepositoryInterface`, `ServiceInterface` and `CollectionInterface` exist only here |
| Meta-model base | `Core\BaseModelAbstract` | UUID identity, type, integer version, audit fields, soft-delete fields, metadata, no-op lifecycle hooks. Base of **all** artifacts |
| Meta-model artifacts | `Core\ModuleAbstract`, `DomainAbstract`, `BusinessCapabilityAbstract`, `BusinessFunctionAbstract`, `WorkflowAbstract` → `Models\*` | Plain PHP objects, not Eloquent |
| Definitions (registry) | `Registry\*Definition`, `WorkflowStep`, `ValidationRuleDefinition` | Immutable value objects |
| Registries | `Registry\*Registry` (Module, Entity, BusinessFunction, Validation, Workflow) | In-memory, per-type exceptions |
| Runtime | `Runtime\BusinessFunctionRuntime` | Wraps `BusinessFunctionAbstract::execute()` and rethrows |
| Validation | `Validation\ValidationFramework`, `ValidationResult`, `ValidationIssue` | Severity error/warning/info. Rule evaluation is an empty extension point |
| Workflow | `Workflow\WorkflowEngine`, `WorkflowContext`, `WorkflowResult` | Sequential. BF invocation is an empty extension point |
| Lifecycle | `Lifecycle\LifecycleManager`, `LifecycleHandler`, `LifecycleEvent`, `LifecycleContext` | Used by workflow and validation, not by the BF pipeline |
| Data layer | `Repositories\BaseRepository`, `Services\BaseService`, `Collections\BaseCollection` | Repository does not persist |
| Laravel integration | `ServiceProvider\LaravelServiceProvider` | Singletons for registries, engines and lifecycle. Config publishing |
| CLI | `wbf:make`, `wbf:list`, `wbf:show`, `wbf:register`, `wbf:doctor`, `wbf:createdemo` | Generators write to `app/` |
| Application sample in src | `BusinessFunctions\ApplyLeave`, `ReferenceData\LeaveReferenceData` | HR-specific logic inside framework `src/` |

### A.3 Node implementation (`business-framework-node`, `@waysnx/business-framework` 0.4.0)

There are zero runtime dependencies. TypeScript is compiled with `strict` enabled.

| Layer | Exports | Notes |
|---|---|---|
| Types | `EntityInterface`, `SerializableInterface`, `MetadataInterface`, `VersionableInterface`, `AuditableInterface` | Interfaces only. No implementations |
| Definitions | `ModuleDefinition`, `EntityDefinition`, `BusinessFunctionDefinition`, `ValidationDefinition`, `WorkflowDefinition`, `WorkflowStep` | Frozen (shallow) value objects |
| Registries | `Registry<T>` base + 5 specialized registries | Generic `DuplicateError` / `NotFoundError` |
| Runtime | `BusinessFunctionRuntime` (definition + registered handler), `BusinessFunctionContext`, `BusinessFunctionResult` | Returns a result object and never throws, except NOD-06 |
| Validation | `ValidationFramework`, `ValidationIssue` | Built-in `required`, `range`, `format`. `custom` is a no-op |
| Workflow | `WorkflowEngine`, `WorkflowContext`, `WorkflowResult` | Executes registered BF runtimes sequentially |
| Lifecycle | `LifecycleManager`, `LifecycleEvent(Names)`, `LifecycleContext`, `LifecycleHandler` | BF and workflow events |
| Errors | `WBFError` (category, context, timestamp) + 13 subclasses | Category string acts as error code |
| CLI | `wbf doctor/list/show/make/register/createdemo` | `make` prints only. list/show/register use empty registries |

---

## B. Implemented capabilities (verified)

| Capability | Laravel | Node | Evidence |
|---|---|---|---|
| Immutable definitions for module, entity, BF, validation, workflow | ✅ | ✅ | [V-code] `src/Registry/*Definition.php`, `src/definitions/*.ts` |
| In-memory registries with duplicate-id and duplicate-name rejection, lookup by id/name/alias | ✅ | ✅ | [V-code] e.g. `ModuleRegistry::register`, `Registry.ts` L22-48 |
| Not-found raises an error | ✅ (`*NotFoundException`) | ✅ (`NotFoundError`) | [V-code] |
| BF pipeline order validate → authorize → rules → execute → publish → transform | ✅ (`BusinessFunctionAbstract::execute` L607-626) | ✅ (`BusinessFunctionRuntime.execute`) | [V-code] |
| Spec-0005-aligned BF mandatory metadata validation | ✅ 12 fields + contracts (`validateMandatoryFields`) | Partial: 6 fields | [V-code] |
| Sequential, fail-fast workflow execution with immutable context and result | ✅ | ✅ | [V-exec] |
| Lifecycle handler registry with event dispatch | ✅ | ✅ | [V-code] |
| Validation result with severity and pass = "no error-level issues" | ✅ | ✅ (issues list) | [V-code] |
| UUID identity for meta-model artifacts | ✅ (`BaseModel::generateUuid`, ramsey/uuid) | n/a | [V-code] |
| Developer CLI incl. end-to-end demo | ✅ | ✅ | [V-exec] (tests) |
| Laravel auto-discovery, config publishing, container singletons | ✅ | n/a | [V-code] `LaravelServiceProvider` |
| CI on push/PR to `main` | ✅ PHP 8.3 / 8.4 | ✅ Node 20 | [V-doc] `.github/workflows/ci.yml` |

## C. Partially implemented capabilities

| Capability | Laravel | Node | Gap |
|---|---|---|---|
| Request contract validation | Empty hook (`validateRequest`) | Only checks the request is a non-array object | `requestContract` is never enforced (both) |
| Response contract validation | Identity transform | Object check only | `responseContract` is never enforced (both) |
| Authorization | Empty hook, allow by default | Caller presence only | No authorizer port. Fail-open (LAR-08, NOD-03) |
| Business rule evaluation | Hook. `evaluateRule` returns `true` | Uses ValidationFramework with `scope == moduleId` only. `custom` passes | LAR-01, NOD-04 |
| Event publication | Empty hook. Runtime lists *declared* events | Returns *declared* events | No event bus port. "Published" means "declared" |
| Workflow → BF invocation | Empty extension point | Implemented | LAR-03 |
| Workflow transitions, conditions, retry, timeout | Fields exist (`WorkflowStep::getTransitions/getRetryPolicy/getTimeout/getConditions`) but are unused | Not modeled | Spec has no transition semantics (PRI-11) |
| Entity audit / soft delete / versioning | Fields and getters on `BaseModelAbstract`. Mutators are protected and never called | Interfaces only | No behavior |
| Observability / correlation | Correlation ID in workflow/validation/lifecycle contexts only | In BF, workflow and lifecycle contexts. Not propagated to steps (NOD-09) | |
| Serialization | `toArray`/`toJson` (snake_case for BaseModel, camelCase for definitions) | `toObject`/`toJson` (camelCase). Interface says `toArray` | No canonical format (LAR-21) |
| Code generation | Writes files for BF and workflow. Entity, module and validation generators are minimal | Prints templates to stdout | |

## D. Planned capabilities (as stated in documentation)

| Source | Planned item |
|---|---|
| Primary `CHANGELOG.md` [Unreleased] | Remaining business specifications, templates, DQP integration, documentation validation, repository automation |
| Primary `README.md` Phase 3–4 | Sample applications, integration examples, best-practice guides, DQP, UI Kit, Studio |
| Primary spec 0005 §25 | Standard BFD serialization ("Future specifications MAY define standard JSON, YAML…") |
| Laravel `README.md` L582-593 "Not currently included" | Persistence, retry/compensation, parallel execution, scheduling, human tasks |
| Node `README.md` "Next Steps (Phase 5+)" | Lists CLI and demo as future work, but both already exist (NOD-12) |
| Node `BusinessFunctionRuntime.ts` comments | Contract-schema validation, permission verification "Phase 3+", event bus |

## E. Missing capabilities

| Capability | Spec status | Laravel | Node |
|---|---|---|---|
| Business-entity contract (identity, attributes, lifecycle, persistence boundary) | Descriptive only (docs 0013) | Missing (meta-model base only) | Missing |
| Repository / persistence port + adapter | Descriptive (docs 0035) | Non-persistent stub (LAR-06) | Missing |
| Transaction / unit-of-work | Not specified | No-op `transaction()` | Missing |
| Tenant context | Not specified (glossary mention only) | Missing | Missing |
| Security context (actor, roles, permissions) | Not specified | `mixed $caller` | `caller?: string \| number` |
| Authorizer extension point | Not specified | Overridable hook only | Missing (private method) |
| Event bus / outbox port | Not specified | Missing | Missing |
| Canonical BFD / entity / validation / error / result JSON schemas | Missing (PRI-07) | Fixture only (`tests/Fixtures/BFD/ApplyLeave.canonical.json`) | Missing |
| Error categories Concurrency, External Dependency Failure | Specified (0005 §21) | Missing | Missing |
| Conformance test suite | Prose only (0000 §11, 0005 §24) | Missing | Missing |
| Primary repo CI (link check, schema validation) | — | — | — |

## F. Contradictions between repositories

| ID | Topic | Primary | Laravel | Node |
|---|---|---|---|---|
| XR-01 | `required` rule on empty input | Severity semantics undefined | **Passes** (default `evaluateRule` = true) [V-exec] | **Fails** with error issue [V-code] |
| XR-02 | BF outcome model | "exactly one Response"; errors distinguishable (BF-ERR-02) | Returns raw array. Failures throw native `InvalidArgumentException` / `RuntimeException` | Returns `BusinessFunctionResult{status, errorCategory, …}`. Never throws (except NOD-06) |
| XR-03 | BF definition fields | Module, Domain, Capability, Version, Lifecycle Status, owners mandatory (BF-ID, BF-META) | `Registry\BusinessFunctionDefinition` has none of domain/capability/lifecycle/owners. `Models\BusinessFunction` has all | `BusinessFunctionDefinition` requires domain + capability. Defaults lifecycle `Draft` and visibility `Public` |
| XR-03 | Entity/Module definition `version` type | Not specified | `string` `'1.0.0'` | `number` `1` |
| XR-03 | EntityDefinition required fields | Not specified | `className`, `namespace` required. No `moduleId` | `moduleId` required. No `className` |
| XR-04 | Workflow step execution | DR-06: workflows coordinate BFs | Engine does **not** call BFs unless subclassed | Engine calls registered BF runtimes |
| XR-04 | Step ordering | Not specified | Sorted for first step, then buggy next-step (LAR-02) | Array order (NOD-02) |
| XR-05 | Error representation | 8 flat categories (0005 §21) | Per-type exception classes, no categories. Validation/workflow exceptions extend `LifecycleException` | `category` strings. `ConflictError.category = 'Conflict'`. `RequestValidationError` category not mapped to spec |
| XR-06 | BF lifecycle events | Not specified | None emitted by BF pipeline | `beforeFunction`, `functionCompleted`, `functionFailed` |
| XR-06 | Workflow `afterStep` event | Not specified | Not emitted | Emitted on success only |
| XR-06 | Validation lifecycle events | Not specified | `beforeValidation`, `validationCompleted`, `validationFailed`, `afterValidation` | Names defined, never dispatched |
| XR-07 | Parity claims | README L270: "Both implementations follow the same WBF concepts and architectural semantics" | — | README L1178: "portable contracts identical to the Laravel implementation" |
| XR-08 | Serialization keys | Not specified | `BaseModel::toArray` snake_case (`entity_id`). Definitions and BFD fixture camelCase | camelCase |
| XR-09 | Interface method naming | — | `toArray()` | `SerializableInterface.toArray()` but definitions implement `toObject()` |
| XR-10 | Versions | README badge v1.0.0, tag v1.0.0-preview, CHANGELOG 1.0.0-TBD, ROADMAP v0.1 | tag v0.1.0, no version in README | package 0.4.0, `src/index.ts` exports `'0.1.0'`, no tag |

## G. Potential breaking changes

The corrections recommended in §J would change observable behavior. Each needs a migration note and a version bump (pre-1.0 minor bump per SemVer §4).

| Change | Affected | Who breaks | Mitigation |
|---|---|---|---|
| `ValidationFramework` fails closed for unknown/unimplemented rule types (LAR-01, NOD-04) | Laravel, Node | Apps relying on default pass, or Node `custom` rules | Ship built-in evaluators first. Add a `strict` flag defaulting to *warn* for one minor release, then *fail* |
| Laravel `determineNextStep` uses minimum greater sequence (LAR-02) | Laravel | Apps with out-of-order step arrays (currently skip steps) | Behavior fix. Document in CHANGELOG |
| Node workflow sorts by `sequence` (NOD-02) | Node | Apps relying on array order with inconsistent sequence | Validate unique sequences at definition time |
| Node `ExecutionError` prototype fix (NOD-01) | Node | Consumers matching `status === 'failure'` for bad requests | Status becomes `validation_failure` |
| Authorization fail-closed when requirements are declared but no authorizer is configured (LAR-08, NOD-03) | Both | Apps declaring permissions without enforcement | Opt-in flag first, then default |
| Unified BF result / error categories (XR-02, XR-05) | Laravel mostly | Callers catching `InvalidArgumentException` | Keep exceptions. Add category mapping and an optional result-returning runtime method |
| Aligning definition fields (XR-03) | Both | Constructors (positional args in PHP) | Additive optional fields only. No reordering of PHP positional params |
| Moving `ApplyLeave` / `LeaveReferenceData` out of `src/` (LAR-11) | Laravel | Anyone importing them | Deprecate in place, move to `examples/` in a later minor |
| Retiring `implementations/laravel` and `packages/*` in primary repo (PRI-05/06) | Primary | Links / readers | Replace with pointer README. Needs approval |

## H. Technical debt

| ID | Repo | Item |
|---|---|---|
| LAR-18 | Laravel | Unused code: `Traits/*` (5 traits duplicated by `BaseModelAbstract`), `WorkflowTransition`, `WorkflowExecution`, `WorkflowStepExecution`, `ValidationExecution`, `Types\SerializableInterface`, `Types\VersionableInterface`, `TransitionException`, `RepositoryException` |
| LAR-12 | Laravel | `config/business-framework.php` keys are never read (timeouts, max_steps, stop_on_error, logging, caching) |
| LAR-13 | Laravel | `BusinessFrameworkServiceProvider` is documentation-only, not a service provider |
| LAR-19 | Laravel | `composer.lock` tracked but listed in `.gitignore`. `autoload-dev` maps non-existent `tests/Core/`. Redundant PSR-4 maps |
| LAR-20 | Laravel, Node | No `.gitattributes`. Windows checkouts get CRLF and PSR-12 lint fails |
| NOD-14 | Node | Coverage thresholds configured but not enforced in CI. Branch coverage 60.99 % |
| NOD-16 | Node | No `engines` field. `clean` uses `rm -rf`. Caret ranges for devDeps. Deprecated transitive deps |
| NOD-17 | Node | Definitions and `LifecycleManager` throw plain `Error` instead of `WBFError` |
| PRI-13 | Primary | ~60 stub files. CHANGELOG has no entry for the existing preview tag |

Encoding check: all 194 pre-existing Markdown files in the primary repo decode as valid UTF-8, with no double-encoded sequences [V-exec].

## I. Security concerns

WBF must not implement authentication or authorization infrastructure. These findings concern the integration contracts and fail-safe defaults.

| ID | Sev | Repo | Concern | Recommendation |
|---|---|---|---|---|
| LAR-01 / NOD-04 | P0 / P1 | Both | Validation fails open (default pass, `custom` pass, unknown type pass) | Fail closed for unknown rule types. Explicit `custom` rule registry |
| LAR-08 / NOD-03 | P1 | Both | Authorization fails open: no authorizer port. Node accepts any truthy caller | Define an `Authorizer` port. Deny when requirements exist and no authorizer is bound |
| LAR-06 | P1 | Laravel | `BaseRepository::create/update` assign every input key matching a property (mass assignment) and report success without persisting | Allow-list attributes from EntityDefinition. Never report persistence success without an adapter |
| LAR-22 / NOD-19 | P1 | Both | No tenant context. Registries and static state are process-global | Specify `TenantContext` in the execution context. Repository adapters must scope by tenant |
| LAR-11 | P2 | Laravel | `ApplyLeave` stores created requests in a static array. Leaks across requests in Octane or queue workers | Move to examples. No static state in framework |
| LAR-16 | P2 | Laravel | `BusinessFunctionRuntime` keeps per-execution state on the instance | Return an execution record instead of storing it |
| LAR-15 | P2 | Laravel | Generators: no path sanitization (`../` in name) and raw interpolation of namespace/module/name into PHP source | Validate identifiers with `^[A-Za-z_][A-Za-z0-9_]*$`. Reject path separators |
| NOD-12 / NOD-10 | P3 | Node | `make` interpolates names into TS source (stdout only) | Same identifier validation |
| NOD-18 | P3 | Node | `new RegExp(rule.pattern)`: ReDoS if definitions come from untrusted sources | Document that definitions are trusted code. Optional pattern length limit |
| — | P2 | Both | Exception messages embed inner messages, class names and IDs (`EntityNotFoundException`, workflow wrappers). Node results carry the raw `error` object | BF-ERR-03: separate public message/category from internal diagnostics |
| — | P3 | Node | `executionId` from `Math.random` | Use `crypto.randomUUID()` (Node ≥ 14.17, no dependency) |
| — | — | Both | Not found: `unserialize`, `eval`, `new Function`, dynamic `require` of user paths, shell execution [V-code] | — |
| — | [Unverified] | Both | Dependency vulnerability scan incomplete (registry unreachable) | Re-run `composer audit` and `npm audit` in CI |

## J. Recommended corrections (prioritized)

| Order | Sev | Action | Findings |
|---|---|---|---|
| 1 | P0 | Laravel: fix `determineNextStep` (minimum greater sequence) + regression test | LAR-02 |
| 2 | P0 | Laravel: unimplemented rule types must not pass silently. Add built-in evaluators via Laravel Validator adapter (see VALIDATION_ARCHITECTURE) | LAR-01, XR-01 |
| 3 | P1 | Laravel: make `ApplyLeaveRuntimeIntegrationTest` date-independent (relative dates, as already done in `ApplyLeaveTest`) | LAR-04 |
| 4 | P1 | Node: restore prototypes in `ExecutionError` subclasses (`Object.setPrototypeOf(this, new.target.prototype)` in `WBFError`). Tighten `runtime.test.ts` L104 | NOD-01 |
| 5 | P1 | Node: sort steps by `sequence`. Reject duplicate sequences | NOD-02 |
| 6 | P1 | Both READMEs + primary README: remove "identical / same semantics" claims until the conformance suite passes | XR-07, PRI-12 |
| 7 | P1 | Approve ADR-0005 (ownership). Freeze `implementations/laravel` and `packages/*` with pointer READMEs | PRI-05, PRI-06 |
| 8 | P1 | Resolve WBF-DOC numbering collision and hierarchy contradiction | PRI-03, PRI-04 |
| 9 | P1 | Publish normative contracts: BFD JSON schema, error categories, execution context, validation result (CORE_CONTRACTS.md → spec) | PRI-07, PRI-11, XR-02, XR-05 |
| 10 | P1 | Stand up conformance suite v0 with the scenarios in CROSS_RUNTIME_CONFORMANCE.md | PRI-08 |
| 11 | P1 | Define authorizer, tenant and security-context ports. Fail closed | LAR-08, NOD-03, LAR-22, NOD-19 |
| 12 | P1 | Approve ADR-0006 (Entity) before any BaseModel work | LAR-09, NOD-05 |
| 13 | P2 | Version and status reconciliation across all READMEs, CHANGELOG, tags | PRI-01, PRI-02, NOD-11 |
| 14 | P2–P3 | Tech-debt items in §H | — |

---

## K. Findings register

Severity:
- **P0**: silent wrong business outcome or security bypass in default configuration.
- **P1**: contract or parity violation, or a blocker for the roadmap.
- **P2**: correctness or maintainability issue with a workaround.
- **P3**: hygiene.

### K.1 Primary repository

| ID | Sev | Location (file :: symbol) | Current behavior | Expected behavior | Recommended action |
|---|---|---|---|---|---|
| PRI-01 | P2 | `README.md` L14 badge; `CHANGELOG.md` L66; `ROADMAP.md`; `SECURITY.md`; tag `v1.0.0-preview` | Five different version statements [V-doc] | One version source of truth | Adopt `1.0.0-preview` (tag) as current. CHANGELOG entry. Badge "preview" |
| PRI-02 | P1 | `README.md` L516-526, L132, L545, L556 | Specs, Standards and Governance "✅ Complete". Phase 2 Validation "Complete" [V-doc] | Status reflects Draft specs and stub governance (`docs/FAQ.md` L240: "Is WBF complete? No.") | Change statuses to Draft / In progress |
| PRI-03 | P1 | `specification/foundation/WBF-DOC-0000…0005.md` vs `docs/00-governance/WBF-DOC-0000…`, `docs/01-foundation/WBF-DOC-0001…0003`, `docs/02-business-architecture/WBF-DOC-0004/0005` | Same IDs, different documents (e.g. 0005 = BF Spec vs Meta-Model) [V-doc] | Globally unique document IDs | Keep `specification/foundation` IDs (they are normative and cross-referenced). Renumber or retitle the `docs/` collisions. ADR required |
| PRI-04 | P1 | spec 0004 §5, OR-01…03 vs `docs/02-business-architecture/WBF-DOC-0004`, `WBF-DOC-0005` | Spec: Module ⊃ Domain ⊃ Capability ⊃ Function. Docs: Capability → Domain → Module [V-doc] | One hierarchy | Spec 0004 is authoritative. Correct the docs |
| PRI-05 | P1 | `implementations/laravel/**` | Full package copy with no status statement. 11 `src/` files and the README differ from the standalone repo, which is newer (2026-09-29 vs 2026-08-31) [V-exec diff] | Single source of truth | Freeze. Replace with pointer README (needs approval, ADR-0005) |
| PRI-06 | P1 | `packages/core/*.php`, `packages/types/*.php`, `packages/core/tests/*` | PHP code in the technology-independent repo, byte-identical to Laravel `src/Core`, `src/Types`. Tests run nowhere [V-exec diff] | Spec repo contains no runtime code | Move tests to the Laravel repo (`tests/Core`, already mapped in its autoload-dev). Freeze `packages/` |
| PRI-07 | P1 | `schemas/*.schema.json` | `$schema` keyword only [V-doc] | Machine-readable contracts | Author BFD, definition, validation-result, error and execution-context schemas first |
| PRI-08 | P1 | `specification/conformance/`, `tests/`, `.github/workflows/` | Empty. No CI [V-doc] | Conformance suite owned here | See CROSS_RUNTIME_CONFORMANCE.md |
| PRI-09 | P2 | `docs/REPOSITORY_GUIDE.md` L138-139, L476-483; `START_HERE.md` L296-304; `README.md` L296-297 | Implementations said to live in `examples/` *and* `implementations/`. Refers to non-existent `diagrams/`, `assets/`, `specification/functional|technical|domain` [V-doc] | Accurate repository map | Update per REPOSITORY_OWNERSHIP.md |
| PRI-10 | P2 | `docs/02-business-architecture/WBF-DOC-0011` L82-89 vs spec 0005 §13 | Runtime order "Execute Services, Apply Business Rules" vs rules before execution [V-doc] | One pipeline | Spec 0005 is authoritative |
| PRI-11 | P1 | `docs/02-business-architecture/WBF-DOC-0013`, `0014`, `0009`; spec 0002 | No normative entity, validation-severity, workflow-transition, execution/tenant/security context or error-hierarchy semantics [V-doc] | Normative contracts for each runtime-implemented concept | CORE_CONTRACTS.md → new specs 0006+ |
| PRI-12 | P1 | `README.md` L270 | Claims both implementations follow the same semantics [V-doc] | Claims backed by conformance results | Reword to "aim to", link capability matrix |
| PRI-13 | P3 | `decisions/ADR-0001…0004`, `docs/00-governance/*`, `templates/*` | Stubs | Content or removal from "Complete" lists | Track in roadmap |

### K.2 Laravel repository

| ID | Sev | Location (file :: symbol) | Current behavior | Expected behavior | Recommended action |
|---|---|---|---|---|---|
| LAR-01 | **P0** | `src/Validation/ValidationFramework.php` L247-255 :: `evaluateRule()` | Returns `true` for every rule. A `required` rule on empty input passes [V-exec] | Standard rule types evaluated. Unknown types never silently pass | Built-in evaluators via Laravel Validator adapter. Fail-closed policy (VALIDATION_ARCHITECTURE) |
| LAR-02 | **P0** | `src/Workflow/WorkflowEngine.php` L336-349 :: `determineNextStep()` | Returns the first step *in array order* with a greater sequence. Steps `[1,3,2]` execute `1,3`, skip `2`, and report success [V-exec] | Next step = minimum sequence greater than current | Fix + regression test + conformance scenario WF-ORDER-001 |
| LAR-03 | P1 | `src/Workflow/WorkflowEngine.php` L273-277 :: `executeBusinessFunction()` | Empty. Workflows "succeed" without invoking any BF [V-code] | Engine invokes BFs through a resolver port (Node parity) | Add a `BusinessFunctionResolver` port. Keep the hook overridable |
| LAR-04 | P1 | `tests/Unit/BusinessFunctions/ApplyLeaveRuntimeIntegrationTest.php` L44-45, 114-115, 145-146, 176-177, 233-234, 270-271, 286-287, 314-315, 347-348; `ApplyLeaveTest.php` L253 | Hard-coded 2026 dates. 4 tests error today, more will fail as dates pass [V-exec] | Date-independent tests | Use relative-date helpers already present in `ApplyLeaveTest` L46-63 |
| LAR-05 | P1 | `src/Registry/BusinessFunctionDefinition.php` L184 vs `src/Core/BusinessFunctionAbstract.php` L353-390 | Two BF descriptors. The registry definition lacks domain, capability, lifecycle status and owners (BF-ID-05/06/07, BF-META-02/03) [V-code] | One canonical BFD shape | Additive optional fields on the registry definition. Derive it from the model (`toDefinition()`) |
| LAR-06 | P1 | `src/Repositories/BaseRepository.php` L158-232, L552-559; `LaravelServiceProvider` L223-236 | `create`/`update` don't persist. `delete` sets only `deletedBy='system'`. `transaction()` is a no-op. Bound as default `RepositoryInterface`, but unresolvable (constructor needs `$model`) [V-code] | Repository port with real adapter (Eloquent), or clearly abstract | Mark abstract/experimental now. Eloquent adapter per ENTITY_IMPLEMENTATION_PLAN |
| LAR-07 | P1 | `src/Core/BusinessFunctionAbstract.php` L604-605; `src/Runtime/BusinessFunctionRuntime.php` | Native `InvalidArgumentException` / `RuntimeException`. No category. Runtime rethrows [V-code] | Errors classified into spec 0005 §21 categories | `BusinessError` hierarchy with category + optional result mode |
| LAR-08 | P1 | `BusinessFunctionAbstract.php` L662-665 :: `checkAuthorization()` | Empty, allows all, even with declared `authorizationRequirements` [V-code] | Fail closed when requirements are declared | `Authorizer` port bound via Laravel Gate adapter (host-owned policies) |
| LAR-09 | P1 | `src/Core/BaseModelAbstract.php`; `Models/*` | "BaseModel" is the base of meta-model artifacts, not of business entities. Not Eloquent [V-code] | Distinct concepts: artifact definition vs business entity | ADR-0006 |
| LAR-10 | P2 | `composer.json` require-dev `illuminate/console`; `src/Console/BaseWbfCommand.php` L7; `LaravelServiceProvider` L85 `config_path()`; `MakeCommand` L161 `base_path()` | Runtime code depends on dev-only / undeclared packages [V-code] | Declared runtime deps | Move `illuminate/console` to `require` or `suggest` and guard. Document foundation requirement. *Version change → needs approval* |
| LAR-11 | P2 | `src/BusinessFunctions/ApplyLeave.php` L584-605; `src/ReferenceData/LeaveReferenceData.php` | HR application logic and static mutable state in framework `src/`. Duplicate in `examples/` [V-code] | Framework core free of application logic | Deprecate. Move to `examples/` / test fixtures |
| LAR-12 | P2 | `config/business-framework.php` | No key is read by `src/` [V-code] | Config honored or removed | Document as reserved. Wire `stop_on_error`, `max_steps` |
| LAR-13 | P2 | `src/ServiceProvider/BusinessFrameworkServiceProvider.php` L64; README | Not a ServiceProvider. README says it registers components [V-code] | Accurate docs | README fix |
| LAR-14 | P2 | `src/Exceptions/*` | No root exception. `ValidationFrameworkException` and `WorkflowException` extend `LifecycleException`. `EntityNotFoundException` (registry) is used by the repository [V-code] | Root `WbfException` + category mapping | Insert root via additive parent change (non-breaking for catch blocks) |
| LAR-15 | P2 | `src/Console/Generators/ArtifactGenerator.php` L127-159; `BusinessFunctionGenerator.php` L92-110 | No path or identifier sanitization [V-code] | Reject invalid identifiers | Validate inputs |
| LAR-16 | P2 | `src/Runtime/BusinessFunctionRuntime.php` | Per-execution state on the instance. "events" = declared events [V-code] | Stateless runtime. Actual events | Return execution record |
| LAR-17 | P2 | `README.md` | `src/Providers/`, `afterExecute()` hook and "WorkflowEngine invokes business functions" do not exist [V-code] | Accurate docs | README fix |
| LAR-18 | P3 | see §H | Dead code | — | Remove after deprecation window or wire up |
| LAR-19 | P3 | `.gitignore`, `composer.json` autoload | see §H | — | Clean up |
| LAR-20 | P3 | repo root | No `.gitattributes`. Lint fails on Windows checkouts [V-exec] | `* text=auto eol=lf` | Add `.gitattributes` |
| LAR-21 | P2 | `src/Models/BaseModel.php` L145-160; `tests/Fixtures/BFD/ApplyLeave.canonical.json` `processing.validation` | snake_case entity serialization. "Canonical" BFD uses Laravel rule-string syntax (`required\|string\|max:50`) [V-exec] | Technology-neutral canonical forms | Canonical rule objects (VALIDATION_ARCHITECTURE §5) |
| LAR-22 | P1 | BF pipeline | Context is only `mixed $caller`. No tenant or correlation ID [V-code] | Execution context per CORE_CONTRACTS §3 | Additive `ExecutionContext` parameter |
| LAR-23 | P3 | `composer.json`, README | No version statement besides tag `v0.1.0` | Version and status in README | Docs |

### K.3 Node repository

| ID | Sev | Location (file :: symbol) | Current behavior | Expected behavior | Recommended action |
|---|---|---|---|---|---|
| NOD-01 | P1 | `src/errors/WBFError.ts` L22; `src/errors/ExecutionError.ts` | `Object.setPrototypeOf(this, WBFError.prototype)` breaks subclasses that don't reset it. `new RequestValidationError() instanceof RequestValidationError === false`. A string request yields `status 'failure'` instead of `'validation_failure'` [V-exec]. Masked by `runtime.test.ts` L104 regex | Correct `instanceof`. Validation failures classified as such | Use `new.target.prototype` in base. Exact assertion in test |
| NOD-02 | P1 | `src/runtime/WorkflowEngine.ts` `execute()` loop | Iterates `workflow.steps` in array order. `sequence` ignored. Steps `[2,1]` run `B,A` [V-exec] | Sequence order | Sort (stable) by `sequence`. Reject duplicates |
| NOD-03 | P1 | `src/runtime/BusinessFunctionRuntime.ts` `checkAuthorization()` | Any truthy caller passes declared permissions. Method is private [V-code] | Authorizer port. Fail closed | Constructor option `authorizer` |
| NOD-04 | P1 | `src/runtime/ValidationFramework.ts` L82-119, L219-223; `BusinessFunctionRuntime.ts` L229-254 | `custom` and unknown types pass. Warnings dropped from BF result. Only validations with `scope === moduleId` run. `ValidationDefinition.severity` ignored [V-code] | Per VALIDATION_ARCHITECTURE | Custom rule registry. Warnings in result. Explicit BF ↔ validation binding |
| NOD-05 | P1 | `src/types/*` | Entity, auditable and versionable are interfaces only. No repository, persistence or transactions [V-code] | Entity architecture per ADR-0006 | ENTITY_IMPLEMENTATION_PLAN |
| NOD-06 | P2 | `src/runtime/BusinessFunctionContext.ts` constructor; `BusinessFunctionRuntime.execute` | `execute(null)` **rejects** with plain `Error` before the try-block [V-exec] | `validation_failure` result | Build context inside try. Allow empty request object |
| NOD-07 | P2 | `src/runtime/LifecycleManager.ts` L155 | Handlers registered without `enabled: true` are silently skipped. README example does this [V-exec] | Default enabled | Default `enabled ?? true`, `priority ?? 0` |
| NOD-08 | P2 | `WorkflowEngine.ts`; `LifecycleEvent.ts` L40-61 | `afterStep` not dispatched on failure. `afterFunction` and validation events never dispatched [V-code] | Documented event contract | Align with CORE_CONTRACTS §6 |
| NOD-09 | P2 | `WorkflowEngine.executeBusinessFunction` | Calls `runtime.execute(request, caller)` without metadata, so each step gets a new correlation ID [V-code] | Correlation propagated | Pass `{correlationId}` |
| NOD-10 | P2 | `src/cli/commands/list.ts`, `show.ts`, `register.ts` L46-52 | Operate on fresh empty registries. `register` prints non-existent `FunctionDefinition` / `FunctionRegistry` [V-code] | Useful or clearly labeled guidance | Fix names. Label as guidance |
| NOD-11 | P2 | `package.json` L3, `src/index.ts` L22, `src/cli/index.ts` L127, `doctor.ts` L22-26 | 0.4.0 vs exported `'0.1.0'` vs hard-coded strings. No git tag | Single version source | Read from package.json. Tag releases |
| NOD-12 | P2 | `README.md` | `implementations/node` paths. Lists non-existent files. Parity claim (L1178). Examples that don't compile (private `businessFunctionRuntimes`, missing `priority`/`enabled`, `timestamp` prop) [V-code] | Accurate docs | README fix |
| NOD-13 | P2 | `BusinessFunctionContext.ts` L35, `WorkflowContext.ts` L47 | Shallow freeze. README claims deep immutability [V-code] | Claim matches behavior | Deep-freeze request copies or reword |
| NOD-14 | P3 | `jest.config.js`, `.github/workflows/ci.yml` L39 | Thresholds unenforced. Branches 60.99 % [V-exec] | Enforced gate | `npm test -- --coverage` once ≥ 70 % |
| NOD-15 | P3 | `BusinessFunctionRuntime.ts` L357-361 | `Math.random` + deprecated `substr` | `crypto.randomUUID()` | — |
| NOD-16 | P3 | `package.json` | No `engines`. Non-portable `clean`. Caret ranges | Pinned / portable | *Package metadata change → needs approval* |
| NOD-17 | P3 | `src/definitions/*`, `LifecycleManager` | Plain `Error` | `WBFError` subclasses | — |
| NOD-18 | P3 | `ValidationFramework.ts` L202 | `new RegExp(rule.pattern)` | Trusted-definition assumption documented | — |
| NOD-19 | P1 | `BusinessFunctionContext` | No tenant or actor object [V-code] | Execution context per CORE_CONTRACTS §3 | Additive fields |
| NOD-20 | P3 | `.github/workflows/ci.yml` L21, L24 | `actions/checkout@v7`, `actions/setup-node@v7` – tag existence [Unverified] | Pinned, existing major tags | Confirm on GitHub |

### K.4 Cross-repository

See §F (XR-01 … XR-10). XR-01 is **P0**. XR-02 … XR-07 are **P1**. XR-08 … XR-10 are **P2**.

---

## L. Architecture gap report (WBF-001 deliverable 4)

| Spec concept | Normative source | Laravel | Node | Gap owner |
|---|---|---|---|---|
| Module ⊃ Domain ⊃ Capability ⊃ Function | spec 0004 OR-01…03 | Artifacts exist as models with ID arrays. Registry has no Domain/Capability registries | Domain/capability are strings only | Spec: decide whether Domain/Capability must be runtime-registered artifacts or metadata only |
| BF identity & metadata (BF-ID, BF-META) | spec 0005 §8-9 | Model ✅, registry definition ✗ | Partial (owners optional, lifecycle defaulted) | Runtimes |
| BF pipeline (§13) | spec 0005 | Hooks ✅. Contract validation, authorization and events are stubs | Steps ✅, same stubs | Runtimes + ports in spec |
| BF validation before authorization, no state change (BF-VAL) | spec 0005 | Order ✅. Enforcement n/a | Order ✅ | — |
| Error categories (§21) | spec 0005 | ✗ | Partial | Spec: category identifiers + schema |
| BFD | spec 0005 §25 (format not mandated) | Fixture only | ✗ | Spec: JSON schema |
| Entity | docs 0013 (descriptive) | ✗ (artifact base only) | ✗ | Spec first (ADR-0006) |
| Validation semantics | none | Partial | Partial | Spec (ADR-0007) |
| Workflow semantics | docs 0009 (descriptive) | Sequential | Sequential | Spec: ordering, failure, events |
| Execution / security / tenant context | glossary only | ✗ | Partial | Spec |
| Events | docs 0016 (descriptive) | Declared only | Declared only | Spec: event envelope + publisher port |
| Conformance | 0000 §11, 0005 §24 (prose) | ✗ | ✗ | Primary repo |

## M. Open questions

1. Is `implementations/laravel` meant to be the canonical source with the standalone repo as a mirror? Git dates suggest the opposite. Answer needed for ADR-0005.
2. Must Domain and Business Capability be runtime-registered artifacts (Laravel has models, Node has strings), or governance metadata only?
3. Canonical serialization casing: camelCase (BFD fixture, Node, Laravel definitions) or snake_case (Laravel `BaseModel::toArray`)?
4. Should the Business Function runtime return a result object (Node) or throw (Laravel) as the *normative* outcome? CORE_CONTRACTS proposes: result is normative, exceptions allowed as a runtime idiom.
5. What is the intended public release line? Laravel `v0.1.0` tag vs Node `0.4.0` vs primary `1.0.0-preview`.
6. Are `actions/*@v7` tags valid (NOD-20)?
