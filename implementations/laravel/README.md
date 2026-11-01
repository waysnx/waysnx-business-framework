# implementations/laravel (removed)

This directory used to hold a copy of the Laravel runtime. It was removed on
2026-11-01 under ADR-0005 (removal R2, approved by Umesh Wani).

The canonical Laravel runtime is maintained in its own repository:

- https://github.com/waysnx/business-framework-laravel

The removed copy was not the source of any release and had drifted from the
canonical repository. Its full history remains available in this repository's
git history (29 commits, first `6208744` on 2026-07-19, last `b7311a9` on
2026-08-31). To inspect it:

```bash
git log -- implementations/laravel
git show b7311a9 -- implementations/laravel
```

Do not add runtime code here. See `docs/architecture/REPOSITORY_OWNERSHIP.md`
and `docs/audit/REPOSITORY_CLEANUP_REPORT.md`.
