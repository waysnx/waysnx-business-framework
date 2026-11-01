---
documentId: WBF-ARCH-OWN-0001
title: Repository Ownership Specification and Documentation Synchronization Plan
status: Approved (ADR-0005 accepted by Umesh Wani, 2026-11-01)
version: 0.3.0
date: 2026-11-01
---

# Repository Ownership Specification

> **Core defines WHAT the framework must do. Laravel and Node define HOW their runtimes implement it.**
>
> The runtimes do not need identical internals, but they must produce equivalent business behavior for equivalent inputs and contracts. No runtime may introduce semantics that contradict a Core specification.

Decision record: [ADR-0005](../../decisions/ADR-0005-Repository-Ownership-and-Canonical-Sources.md). Findings: [REPOSITORY_AUDIT.md](../audit/REPOSITORY_AUDIT.md).

## 1. What Core owns (`waysnx-business-framework`)

| Artifact | Location |
|---|---|
| Technology-independent architecture and principles | `specification/foundation/WBF-SPEC-0000…0005`, `docs/` |
| Normative specifications and contracts | `specification/**` (`WBF-SPEC-*`) |
| Architecture Decision Records | `decisions/ADR-NNNN-*.md` |
| Governance, contribution standards, **document registry** | root governance files, `docs/00-governance/` (incl. `DOCUMENT_REGISTRY.md`) |
| Shared JSON schemas | `schemas/` |
| **Shared conformance fixtures** and expected contract behavior | `tests/conformance/fixtures/` |
| Cross-runtime compatibility requirements and capability matrix | `docs/architecture/CROSS_RUNTIME_CONFORMANCE.md`, `IMPLEMENTATION_CAPABILITY_MATRIX.md` |
| **Master roadmap** | `ROADMAP.md` (root). Runtime roadmaps are subordinate and only list runtime-specific items |

Core contains **no runtime source code and no runtime-specific tests**. The former copies were removed in Phase 0.1 (§4).

## 2. What Laravel owns (`business-framework-laravel`)

- Laravel/PHP runtime implementation (`src/`), service providers, framework integration
- Eloquent adapters (future, ADR-0006), Laravel-specific validation integration (future, ADR-0007)
- Artisan commands and generators
- **Runtime-specific tests** (`tests/Unit`, `tests/Console`)
- **Conformance adapter** (`tests/Conformance/ConformanceTest.php`) and its `conformance-known-differences.json`
- Laravel README, docs (`docs/`), examples
- Package version and releases (Packagist)

## 3. What Node owns (`business-framework-node`)

- TypeScript/Node.js runtime implementation (`src/`), adapters, repository implementations (future)
- Node validation integration, CLI and runtime tooling
- **Runtime-specific tests** (`src/**/__tests__/*.test.ts`)
- **Conformance adapter** (`src/__tests__/conformance.test.ts`) and its `conformance-known-differences.json`
- Node README, docs (`docs/`), examples
- Package version and releases (npm)

## 4. Duplicate and outdated implementation copies in Core

### 4.1 Status after Phase 0.1 (2026-11-01)

| Path | Status |
|---|---|
| `implementations/laravel/**` | **Removed (R2).** Pointer `implementations/laravel/README.md` |
| `packages/core/*.php`, `packages/types/*` | **Removed (R4).** Pointer `packages/README.md` |
| `packages/core/tests/BaseModelAbstractTest.php` | **Moved (R3)** to Laravel `tests/Core/`. Passes there |
| `packages/core/tests/BusinessFunctionAbstractTest.php` | **Retired** (never passed). Uncovered behavior rewritten in Laravel `tests/Core/` |
| `packages/cli/`, `packages/validator/` | Unchanged placeholders |

Evidence: [REPOSITORY_CLEANUP_REPORT.md](../audit/REPOSITORY_CLEANUP_REPORT.md). The table below is the historical analysis that justified the removals.

### 4.2 Analysis before removal (verified 2026-10-31)

| Path | Compared with | Result | Referenced by | Disposition |
|---|---|---|---|---|
| `packages/core/*.php` (6) | `business-framework-laravel@main:src/Core/` | **Byte-identical** | README tree only ("Framework libraries") | **Frozen** (`packages/FROZEN.md`). Remove after approval (R4) |
| `packages/types/*.php` (6) | `…@main:src/Types/` | **Byte-identical** | README tree only | **Frozen**. Remove after approval (R4) |
| `packages/core/tests/*.php` (2) | `…@main:tests/Core/` (mapped in `autoload-dev`, directory absent) | No counterpart. **Run by no CI** | — | Move to Laravel `tests/Core/` (R3) |
| `packages/cli/`, `packages/validator/` | — | Empty (`.gitkeep`) | — | Reserve for neutral tooling or remove |
| `implementations/laravel/**` (159 files) | `business-framework-laravel@main` | 119 identical, **40 older**, **15 missing** (incl. `.github/workflows/ci.yml`) | README L296, START_HERE L301/L329, REPOSITORY_GUIDE L109/L139 (as a directory, not as a source) | **Frozen** (`implementations/laravel/FROZEN.md`). Replace with pointer README after approval (R2) |

No specification, schema or fixture depends on either copy. Neither copy is published: Core has no `composer.json` or `package.json`.

Removal plan (approved 2026-11-01. R2, R3 and R4 executed, R5 open; see §4.1):

| Step | Action | History impact |
|---|---|---|
| R1 | Approve ADR-0005 | — |
| R2 | Replace `implementations/laravel/**` with a pointer README | Files remain in Git history. External deep links break, so add the pointer before removal |
| R3 | Move `packages/core/tests/*.php` to Laravel `tests/Core/` and run them there | New files in Laravel. History of the originals stays in Core |
| R4 | Remove `packages/core`, `packages/types` | Remains in Git history |
| R5 | Keep `implementations/` as an index (runtime repositories, supported spec version, conformance status) | — |

## 5. Where tests live

| Kind | Owner | Location |
|---|---|---|
| Technology-independent fixtures (inputs + expected business outcomes) | Core | `tests/conformance/fixtures/*.json` |
| Conformance adapters (native test frameworks, no new framework) | each runtime | Laravel `tests/Conformance/`. Node `src/__tests__/conformance.test.ts` |
| Known differences | each runtime | `conformance-known-differences.json` next to the adapter |
| Runtime-specific unit/integration tests | each runtime | Laravel `tests/Unit`, `tests/Console`. Node `src/**/__tests__` |

## 6. How Core contract changes propagate

1. A change starts in Core as a spec PR, plus an ADR for architectural changes, plus fixture changes in `tests/conformance/fixtures/`. The fixture `suiteVersion` is bumped.
2. Core is tagged (`conformance-vX.Y.Z`) once approved. *No tags have been created. `conformance-v0.1.0` is prepared but untagged.*
3. Each runtime opens an implementation PR that references the Core PR or tag. Its adapter runs the new fixtures. Unmet fixtures are listed in `conformance-known-differences.json` with a finding or decision reference.
4. Runtime CI checks out the pinned Core tag with `WBF_CONFORMANCE_REQUIRED=1` (ADR-0008). *Workflow files `.github/workflows/conformance.yml` are prepared in both runtimes with a manual trigger only. Activation is pending the Core commit and the tag. Locally the sibling checkout or `$WBF_CONFORMANCE_FIXTURES` is used.*
5. The capability matrix is updated from the adapters' JSON reports (`$WBF_CONFORMANCE_REPORT`).
6. A runtime discovering a contract gap opens a Core issue first. It may implement behind an "experimental/provisional" marker in the meantime, never as a silent semantic change.

## 7. How compatibility is declared

A runtime README states:

- the WBF specification version it targets
- the conformance suite version it runs
- its pass/fail counts and known differences

It links to the capability matrix. Rules:

- **"Conformant to suite X"** may be claimed only with zero known differences for suite X.
- **"Equivalent / parity"** between runtimes may be claimed only for contract areas where both runtimes pass every fixture of the same suite version, and only for those areas.
- Partial results must be reported as partial.

## 8. Versioning model (proposed)

| Version | Owner | Current |
|---|---|---|
| WBF Specification | Core | Tag `v1.0.0-preview`. All normative docs are Draft |
| Conformance suite | Core | `0.1.0`, 33 fixtures (`tests/conformance/suite.json`). Untagged |
| Laravel package | Laravel | Tag `v0.1.0` (unchanged in Phase 0) |
| Node package | Node | `package.json` 0.4.0, untagged (unchanged in Phase 0) |

---

# Documentation Synchronization Plan

| # | Step | Status (Phase 0) |
|---|---|---|
| S1.1 | Core README: version, statuses, parity sentence | **Done** (Phase 0) |
| S1.2 | Single directory map (README / START_HERE / REPOSITORY_GUIDE) | **Partly done.** README tree corrected. START_HERE and REPOSITORY_GUIDE still reference `diagrams/`, `assets/`, `specification/functional\|technical\|domain` (open) |
| S1.3 | CHANGELOG entry for `1.0.0-preview` | Open (release notes are outside Phase 0 scope) |
| S1.4 | Laravel README corrections + compatibility section | **Done** (Phase 0) |
| S1.5 | Node README corrections + compatibility section | **Done** (Phase 0) |
| S2.1 | Resolve WBF-DOC ID collision | **Done** differently from the earlier proposal: normative specs moved to `WBF-SPEC-*` (DOCUMENT_REGISTRY.md) |
| S2.2 | Align descriptive docs 0004/0005 with the normative hierarchy | **Done** (CONCEPTUAL_HIERARCHY.md) |
| S2.3 | Align docs 0011 runtime order with WBF-SPEC-0005 §13 | Open |
| S2.4 | Freeze, then remove `implementations/laravel` and `packages/*` | **Done** (Phase 0.1: R2, R3, R4 executed) |
| S3 | Promote CORE_CONTRACTS sections to specs, author schemas | Open (M2) |
| S4 | Automation: link check, schema validation, conformance in runtime CI | Open (M3) |
| — | Five unresolvable legacy references in docs 0071/0101/0111 | Open: need human judgment (DOCUMENT_REGISTRY §2.1) |
