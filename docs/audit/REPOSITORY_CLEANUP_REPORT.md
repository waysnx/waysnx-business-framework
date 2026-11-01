---
documentId: WBF-AUDIT-0003
title: Repository Cleanup Report (ADR-0005 removals R2–R4)
status: Final for Phase 0.1 – changes uncommitted
version: 1.1.0
date: 2026-11-01
---

# Repository Cleanup Report

Authority: ADR-0005, accepted by Umesh Wani on 2026-11-01. R2, R3 and R4 were authorized "subject to the removal report, dependency checks and successful verification of the moved tests". This report is that removal report.

All changes are unstaged working-tree changes on branch `phase0/decisions-docs-conformance`. Nothing was committed, pushed or tagged. Every removed file is still in git history and can be restored with `git checkout -- <path>` until a commit is made, and with `git checkout <commit> -- <path>` afterwards.

## 1. Pre-removal checks

| Check | Method | Result |
|---|---|---|
| Build or package manifests in Core that could consume the copies | Searched for `composer.json`, `package.json` outside the copies | None. Core builds and publishes nothing |
| Core CI that could run the copies | `.github/` contents | Only `.gitkeep`. No workflows |
| Runtime repositories referencing the copies | Searched Laravel and Node configs, CI, `composer.json` autoload, `package.json` | No references in configuration or CI |
| Runtime source references | Searched Laravel `src/`, `tests/` for `packages/types`, `implementations/laravel` | Docblocks only, in `Contracts/{BusinessFunctionInterface,EntityInterface,MetadataInterface}.php` and `ServiceProvider/BusinessFrameworkServiceProvider.php`. Updated to the canonical locations |
| Specs, schemas, fixtures referencing the copies | Searched `specification/`, `schemas/`, `tests/conformance/` | None |
| Core documents referencing the copies | Searched all Markdown | README tree, ADR-0005, REPOSITORY_OWNERSHIP, DOCUMENT_REGISTRY, PHASE_0_COMPLETION_REPORT, REPOSITORY_AUDIT. Current-state documents updated (§4). The two Phase 0 reports are historical snapshots and were left unchanged |
| External deep links into `implementations/laravel` | Not checkable from here | Mitigated by the pointer README at the old path |

## 2. Git history of the removed paths

| Path | Commits | First | Last | Tracked files |
|---|---|---|---|---|
| `implementations/laravel/` | 29 | `6208744` (2026-07-19) | `b7311a9` (2026-08-31) | 159 |
| `packages/core/` | 8 | — | `b1323b9` (2026-08-17) | 9 |
| `packages/types/` | 3 | — | `a527803` (2026-08-15) | 7 |

The Phase 0 notices `implementations/laravel/FROZEN.md` and `packages/FROZEN.md` were never committed. They were removed with the directories and replaced by the pointer READMEs.

## 3. Actions

| Step | Action | Files | Verification |
|---|---|---|---|
| R2 | Removed `implementations/laravel/**`. Replaced by pointer `implementations/laravel/README.md` (canonical repository, removal date, last commit, how to read history) | 158 tracked files deleted, `README.md` replaced (159 total) | `git ls-files --deleted` count. The canonical Laravel repository passes its full suite (§5) |
| R3 | Moved `packages/core/tests/BaseModelAbstractTest.php` to Laravel `tests/Core/BaseModelAbstractTest.php`. The inline test double was split into `tests/Core/ConcreteTestModel.php` (one class per file, PSR-12). `tests/Core` added to the Laravel `phpunit.xml` default suite | 1 deleted in Core, 2 added in Laravel | `vendor/bin/phpunit tests/Core`: **OK, 27 tests, 67 assertions**. Also part of the full Laravel run |
| R3 | `packages/core/tests/BusinessFunctionAbstractTest.php` **retired** in Core (Phase 0.1 review) and **rewritten** in Laravel `tests/Core/BusinessFunctionAbstractTest.php` + `ConcreteBusinessFunction.php` | 1 deleted in Core, 2 added in Laravel | `vendor/bin/phpunit tests/Core`: **OK, 56 tests, 127 assertions** (27 moved + 29 rewritten). See §3.1 |
| R4 | Removed `packages/core/*.php` (6), `packages/core/.gitkeep`, `packages/types/*` (6 PHP + `.gitkeep`). Pointer `packages/README.md` | 9 + 7 tracked files deleted (including both former tests) | The canonical copies in Laravel `src/Core` and `src/Types` were byte-identical at the time of the Phase 0 check and remain under test there |
| — | `packages/cli/.gitkeep`, `packages/validator/.gitkeep` | untouched | Not in the scope of R2–R4 |

Totals: 174 tracked files deleted and 1 replaced by R2–R4. The other 6 deletions in `git status` are the Phase 0 `WBF-DOC → WBF-SPEC` renames (D11), not part of this cleanup.

### 3.1 BusinessFunctionAbstractTest.php (decided: rewrite the uncovered part in Laravel, retire the rest)

Owner decision (Phase 0.1 review, 2026-11-01): do not move the file unchanged. Investigate it, rewrite it in Laravel if it verifies important behavior not covered elsewhere, otherwise retire it with a rationale. Keep no Laravel-specific implementation tests in Core.

**Purpose.** It tested the PHP class `Core\BusinessFunctionAbstract`: 25 accessor/serialization checks of the Business Function Definition (identity, metadata, contracts, processing, operational, `getCompleteContractDefinition()`) and 2 checks that `initializeBusinessFunction()` rejects incomplete definitions.

**Core contract or Laravel behavior?** The *rules* it touches (mandatory BFD fields, non-empty request/response contract) come from WBF-SPEC-0005. The *test* exercises a Laravel/PHP class, protected PHP members and PHP exceptions. It is therefore a Laravel implementation test and does not belong in Core. The technology-independent form of such rules belongs in `tests/conformance/` (no BFD-validation fixture exists yet; planned with the BFD schema in M2).

**History.** Added in `a527803` (2026-08-15, "feat(core): add business function definition model") and never changed afterwards. It never ran successfully:

- From the first commit, the double declared `toArray()`/`toJson()` `protected` while `BaseModelAbstract` declares them `abstract public`. PHP rejects that when the class is loaded (fatal error), so no test in the file could execute.
- The tests assigned protected properties (`$func->functionId = …`) and called the protected `initializeBusinessFunction()` from outside the class.
- Core never had CI (`9e07753` removed the placeholder workflows on 2026-07-20), so nothing reported the failure. The next commit, `141d075` ("All 81 tests pass"), changed only `packages/core/BusinessFunctionAbstract.php` and files under `implementations/laravel`; its test count refers to the Laravel package, not to this file.

**Coverage comparison (Laravel):**

| Behavior in the legacy file | Already covered in Laravel? |
|---|---|
| Identity, metadata, tags, events, contracts, processing, operational accessors (tests 1–20) | Yes: `tests/Unit/Models/BusinessFunctionTest.php` (27 tests on the same accessors and serialization) |
| Complete contract definition shape (21–23) | Yes: `BusinessFunctionTest::testCompleteContractDefinitionIsComprehensive`, `tests/Unit/Models/CanonicalBFDTest.php`, `ApplyLeaveTest::testBfdRemainsAccurate` |
| Inherited entity ID / version (24–25) | Partly: `BusinessFunctionTest` (entity ID, versioning) |
| `initializeBusinessFunction()` rejects missing mandatory fields (26) | **No** (searched for the exception messages; no test asserts them) |
| Rejects an empty request contract (27) | **No** |

**Action.**

- Rewritten in Laravel as `tests/Core/BusinessFunctionAbstractTest.php` with a proper double `tests/Core/ConcreteBusinessFunction.php` (public `toArray()`/`toJson()`, definition set from inside the class). It covers the uncovered behavior and goes slightly further: each of the 12 mandatory fields missing, each whitespace-only, empty request contract, empty response contract, a complete definition initializing, inherited identity fields, and distinct entity IDs. 29 tests pass.
- The duplicate accessor tests were **retired**, not rewritten.
- The Core file was deleted. `packages/core/` no longer exists on disk.

## 4. Documents updated

| File | Change |
|---|---|
| `README.md` | Repository tree lines for `packages/` and `implementations/` |
| `decisions/ADR-0005-…md` | Deciders line, "Phase 0.1 Execution" section |
| `docs/architecture/REPOSITORY_OWNERSHIP.md` | Status Approved, §4.1 post-removal status, removal plan status, §6 CI note, S2.4 |
| `docs/architecture/PHASE_0_DECISION_REGISTER.md` | D9 body |
| `docs/00-governance/DOCUMENT_REGISTRY.md` | "Not changed" note, this report registered as WBF-AUDIT-0003 |
| Laravel `src/Contracts/*`, `src/ServiceProvider/BusinessFrameworkServiceProvider.php` | Docblock references away from `packages/types` and `implementations/laravel` |

Left unchanged on purpose: `docs/audit/REPOSITORY_AUDIT.md` and `docs/audit/PHASE_0_COMPLETION_REPORT.md` (dated snapshots), and `START_HERE.md` / `REPOSITORY_GUIDE.md`, which describe `implementations/` as a generic directory for technology references. That remains true (R5) and is part of the open S1.2 directory-map cleanup.

## 5. Verification after removal

| Repository | Command | Result |
|---|---|---|
| Laravel | `vendor/bin/phpunit` | See PHASE_0_1_COMPLETION_REPORT.md §5 (final run) |
| Laravel | `vendor/bin/phpunit tests/Core` | OK, 56 tests, 127 assertions |
| Node | `npm test` | Unaffected by the removal (no reference). See completion report |
| Core | `git ls-files packages implementations` vs. working tree | Only the two pointer READMEs and two `.gitkeep` placeholders remain |

## 6. Open items

1. ~~Owner decision on `BusinessFunctionAbstractTest.php`~~ Decided and done (§3.1).
2. R5: turn `implementations/` into an index of runtime repositories with supported spec and suite versions.
3. Commit the removals as a separate, reviewable change, as ADR-0005 requires ("each step is a separate, reviewable change"). Suggested split: R2, R3+R4, docs.
