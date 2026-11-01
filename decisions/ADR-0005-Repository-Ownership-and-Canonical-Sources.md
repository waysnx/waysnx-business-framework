# ADR-0005 Repository Ownership and Canonical Sources

| Field | Value |
|---|---|
| Identifier | ADR-0005 |
| Title | Repository Ownership and Canonical Sources |
| Status | **Accepted** – approved by Umesh Wani, 2026-11-01 (removal steps R2–R4 authorized subject to removal report and verification) |
| Date | 2026-10-31 |
| Deciders | Umesh Wani (approved 2026-11-01) |
| Related | REPOSITORY_OWNERSHIP.md, REPOSITORY_AUDIT.md (PRI-05, PRI-06, PRI-09, XR-07) |

## Context

WBF is spread across three repositories:

- `waysnx-business-framework` (specification)
- `business-framework-laravel`
- `business-framework-node`

The specification repository also contains:

- `implementations/laravel/`: a full copy of the Laravel package. It was last changed on 2026-08-31 and is behind the standalone repository, which has changes in 11 `src/` files plus the README and CI, up to 2026-09-29.
- `packages/core` and `packages/types`: PHP code byte-identical to Laravel `src/Core` and `src/Types`, plus two PHPUnit test files that no CI executes.

The documentation disagrees on where implementations live:

- README and START_HERE say `implementations/`.
- REPOSITORY_GUIDE says `examples/` and also `implementations/`.

## Problem Statement

There is no declared source of truth for runtime code or for business semantics. Without one:

- the in-repo copies drift from the real packages
- contributors may fix the wrong copy
- runtime repositories can introduce contracts that the specification does not define

## Alternatives Considered

1. **Monorepo.** Move both runtimes into the specification repository, under `implementations/laravel` and `implementations/node`.
   - Pros: atomic cross-runtime changes.
   - Cons: Packagist and npm publishing from subdirectories needs split tooling, and the standalone repos already have their own CI, tags and published package names.
   - It also reverses the current, more active setup.
2. **Standalone runtimes, with mirrored copies in the spec repo.**
   - Pros: readers see the code next to the spec.
   - Cons: this is the drift we see today. Mirroring would need automation that does not exist.
3. **Standalone runtimes, with the spec repo holding only technology-independent artifacts (chosen).**
   - The spec repo owns specifications, schemas, ADRs, roadmap, capability matrix and conformance scenarios.
   - Runtimes own code and runtime docs.

## Decision

Adopt alternative 3, as detailed in REPOSITORY_OWNERSHIP.md §3–§5. Specifically:

- `business-framework-laravel` and `business-framework-node` are the only canonical sources of runtime code.
- `implementations/laravel/` and `packages/{core,types}` are **frozen** immediately. In a separate, approved change they will be replaced by pointer READMEs.
- `packages/core/tests/*` are moved to Laravel `tests/Core/`, which is already mapped in its `composer.json` autoload-dev.
- Runtime READMEs must not claim equivalence unless conformance results back it.

## Consequences

- Positive:
  - one source of truth
  - parity becomes measurable
  - the spec repo becomes genuinely technology-independent
- Negative:
  - cross-runtime changes need coordinated PRs in two or three repositories
- Follow-up:
  - documentation synchronization plan S1–S4
  - conformance suite (ADR-0008)

## Migration implications

- No files are deleted by this ADR. Removing or replacing the frozen directories is a separate change that needs explicit approval.
- Links pointing into `implementations/laravel/` must be redirected to the standalone repository.

## References

- docs/audit/REPOSITORY_AUDIT.md
- docs/architecture/REPOSITORY_OWNERSHIP.md
- GOVERNANCE.md (ADR fields, lifecycle)

## Phase 0 Review (2026-10-31)

| Aspect | Assessment |
|---|---|
| Problem statement | Re-verified against `main`. `packages/core` and `packages/types` are byte-identical to `business-framework-laravel@main` `src/Core` and `src/Types` (12 files). `implementations/laravel` has 119 files identical to Laravel `main`, 40 that differ (the copy is older), and 15 files missing that exist only in the Laravel repo (including its CI workflow). No specification or schema references either copy |
| Proposed solution | Sound. It matches the Phase 0 ownership model: Core defines *what*, runtimes define *how* |
| Gaps found | (1) No removal criteria or timeline for the frozen copies. (2) The location of shared fixtures was not stated (now `tests/conformance/fixtures/`, ADR-0008). (3) How Core contract changes propagate to runtimes was undefined (now REPOSITORY_OWNERSHIP §6). (4) Master roadmap ownership was implicit (now explicit: Core `ROADMAP.md`). (5) External links into `implementations/laravel` may exist outside this repository and could not be checked |
| Actions taken in Phase 0 | Frozen notices added: `implementations/laravel/FROZEN.md` and `packages/FROZEN.md`. **No file deleted or moved** |
| Proposed removal plan | R1: approve this ADR. R2: replace `implementations/laravel/**` with a pointer README. R3: move `packages/core/tests/*.php` to Laravel `tests/Core/` (already mapped in its `autoload-dev`). R4: remove `packages/core` and `packages/types`. R5: keep `implementations/` as an index of runtimes. Each step is a separate, reviewable change. Git history is preserved because removed files remain in history |
| Breaking changes | None for package consumers. Neither copy is published (no Core `composer.json` or `package.json`) |
| **Recommendation** | **Accept with the revisions above** |
| **Approval status** | **Accepted** – Umesh Wani, 2026-11-01. R2–R4 executed in Phase 0.1 (see docs/audit/REPOSITORY_CLEANUP_REPORT.md) |

## Phase 0.1 Execution (2026-11-01)

Details, pre-removal checks and evidence: [REPOSITORY_CLEANUP_REPORT.md](../docs/audit/REPOSITORY_CLEANUP_REPORT.md).

| Step | Result |
|---|---|
| R1 | Done. Accepted by Umesh Wani, 2026-11-01 |
| R2 | Done. `implementations/laravel/**` removed: 159 tracked files, of which 158 deleted and `README.md` replaced by a pointer. The uncommitted Phase 0 `FROZEN.md` was removed too |
| R3 | **Done.** `BaseModelAbstractTest.php` moved to Laravel `tests/Core/` (27 tests pass). `BusinessFunctionAbstractTest.php` (never passed) was retired in Core and its uncovered behavior rewritten in Laravel `tests/Core/` (29 tests), per the owner decision of the Phase 0.1 review |
| R4 | Done. `packages/core/*.php`, `packages/types/*`, `packages/FROZEN.md` removed. Pointer `packages/README.md` added. `packages/cli` and `packages/validator` placeholders untouched (not in scope) |
| R5 | Not started. `implementations/` currently holds only the Laravel pointer |

The removals are unstaged working-tree changes. Nothing was committed.
