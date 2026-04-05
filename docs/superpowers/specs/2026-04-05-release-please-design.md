# Release-Please Integration Design

**Date:** 2026-04-05
**Status:** Approved

## Goal

Automate changelog management, version bumping, and tag creation using `release-please`, while preserving the existing `release.yml` quality gate (`composer check` before GitHub Release).

## Approach

**Approach A:** release-please handles Release PR + tag creation; `release.yml` handles quality checks + GitHub Release (triggered by tag push).

## Components

### 1. `.github/workflows/release-please.yml` (new)

- Triggers on push to `master`
- Uses `googleapis/release-please-action`
- Creates/updates a Release PR that includes changelog updates and bumped version label
- When Release PR is merged: creates git tag only (`skip-github-release: true`)
- Does NOT create GitHub Release — that responsibility stays with `release.yml`

### 2. `.release-please-config.json` (new)

- Release type: `simple` (manages CHANGELOG.md and tags only)
- No `composer.json` version field — Packagist reads version from git tags (adding version to composer.json is an anti-pattern for PHP packages)
- Changelog section mapping:

| Commit type | Changelog section | Visible |
|-------------|-------------------|---------|
| `feat:` | Added | yes |
| `fix:` | Fixed | yes |
| `refactor:` | Changed | yes |
| `perf:` | Changed | yes |
| `docs:` | — | hidden |
| `chore:` | — | hidden |
| `test:` | — | hidden |

### 3. `.release-please-manifest.json` (new)

- Bootstraps release-please with current version `1.1.3`
- Content: `{ ".": "1.1.3" }`

### 4. `.github/workflows/release.yml` (unchanged)

- Continues to trigger on tag push (`v*`)
- Runs `composer check` as quality gate
- Creates GitHub Release via `softprops/action-gh-release`

## Process Flow

```
Before:
  merge PR → git tag vX.Y.Z (manual) → git push --tags → release.yml fires → GitHub Release

After:
  merge PR(s) → Release PR auto-created/updated by release-please
  merge Release PR → tag vX.Y.Z created automatically → release.yml fires → GitHub Release
```

## What Does NOT Change

- `release.yml` — untouched
- `composer.json` — no `version` field added
- `CHANGELOG.md` format — release-please uses Keep a Changelog format by default
- Required permissions: `release-please.yml` needs `contents: write` and `pull-requests: write`

## Out of Scope

- Auto-merging Release PRs
- Packagist webhook or publish automation
- Semantic-release or any other release tooling
