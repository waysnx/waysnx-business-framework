# packages (legacy, removed)

The PHP sources in `packages/core` and the type stubs in `packages/types` were
removed on 2026-11-01 under ADR-0005 (removal R4, approved by Umesh Wani).
They duplicated code that is owned by the runtime repositories:

- Laravel runtime: https://github.com/waysnx/business-framework-laravel
- Node runtime: https://github.com/waysnx/business-framework-node

History is preserved in git (`git log -- packages/core packages/types`).

## Tests that used to live here (R3)

| Former file | Disposition |
|---|---|
| `packages/core/tests/BaseModelAbstractTest.php` | Moved unchanged (double split into its own file) to Laravel `tests/Core/BaseModelAbstractTest.php`. 27 tests pass |
| `packages/core/tests/BusinessFunctionAbstractTest.php` | **Retired** in Core and **rewritten** in Laravel as `tests/Core/BusinessFunctionAbstractTest.php` (29 tests), covering only behavior no other Laravel test covered. Rationale: `docs/audit/REPOSITORY_CLEANUP_REPORT.md` §3.1 |

Core keeps no Laravel- or Node-specific implementation tests. Technology-independent
expectations live in `tests/conformance/`.

## What remains

| Path | Status |
| --- | --- |
| `packages/cli/.gitkeep` | Placeholder. No code. Not covered by R2–R4 |
| `packages/validator/.gitkeep` | Placeholder. No code. Not covered by R2–R4 |

Nothing in this directory is built, tested or published.
