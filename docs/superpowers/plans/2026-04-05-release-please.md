# Release-Please Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Automate changelog management, version bumping, and tag creation via release-please, while keeping the existing `release.yml` quality gate intact.

**Architecture:** A new `release-please.yml` workflow fires on every push to `master` and maintains a Release PR. When the Release PR is merged, release-please creates the git tag (`skip-github-release: true`) and the existing `release.yml` triggers to run `composer check` and publish the GitHub Release.

**Tech Stack:** `googleapis/release-please-action@v4`, GitHub Actions, Keep a Changelog format

---

## File Map

| Action | Path |
|--------|------|
| Create | `.github/workflows/release-please.yml` |
| Create | `.release-please-config.json` |
| Create | `.release-please-manifest.json` |
| No change | `.github/workflows/release.yml` |

---

### Task 1: Create release-please manifest

**Files:**
- Create: `.release-please-manifest.json`

Bootstraps release-please with the current released version so it doesn't try to re-release old commits.

- [ ] **Step 1: Create the manifest**

```json
{
  ".": "1.1.3"
}
```

Save to `.release-please-manifest.json` at repo root.

- [ ] **Step 2: Verify JSON is valid**

```bash
cat .release-please-manifest.json | python3 -m json.tool
```

Expected output:
```json
{
    ".": "1.1.3"
}
```

- [ ] **Step 3: Commit**

```bash
git add .release-please-manifest.json
git commit -m "chore: bootstrap release-please manifest at v1.1.3"
```

---

### Task 2: Create release-please config

**Files:**
- Create: `.release-please-config.json`

Configures release type, disables GitHub Release creation (handled by `release.yml`), and maps conventional commit types to Keep a Changelog sections.

- [ ] **Step 1: Create the config**

```json
{
  "release-type": "simple",
  "skip-github-release": true,
  "packages": {
    ".": {}
  },
  "changelog-sections": [
    {"type": "feat", "section": "### Added"},
    {"type": "fix", "section": "### Fixed"},
    {"type": "refactor", "section": "### Changed"},
    {"type": "perf", "section": "### Changed"},
    {"type": "docs", "section": "### Changed", "hidden": true},
    {"type": "chore", "section": "### Changed", "hidden": true},
    {"type": "test", "section": "### Changed", "hidden": true}
  ]
}
```

Save to `.release-please-config.json` at repo root.

- [ ] **Step 2: Verify JSON is valid**

```bash
cat .release-please-config.json | python3 -m json.tool
```

Expected: clean JSON output, no errors.

- [ ] **Step 3: Commit**

```bash
git add .release-please-config.json
git commit -m "chore: add release-please config"
```

---

### Task 3: Create release-please workflow

**Files:**
- Create: `.github/workflows/release-please.yml`

- [ ] **Step 1: Create the workflow**

```yaml
name: Release Please

on:
  push:
    branches:
      - master

permissions:
  contents: write
  pull-requests: write

jobs:
  release-please:
    runs-on: ubuntu-latest
    steps:
      - uses: googleapis/release-please-action@v4
        with:
          token: ${{ secrets.GITHUB_TOKEN }}
```

Save to `.github/workflows/release-please.yml`.

- [ ] **Step 2: Verify YAML syntax**

```bash
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/release-please.yml'))" && echo "OK"
```

Expected: `OK`

- [ ] **Step 3: Commit**

```bash
git add .github/workflows/release-please.yml
git commit -m "ci: add release-please workflow"
```

---

### Task 4: Push and open PR

- [ ] **Step 1: Check which branch you're on**

```bash
git branch --show-current
```

If on `docs/update-changelog` — the changelog backfill is already on this branch, so these changes can go into the same PR #7. If on a fresh branch, push and open a new PR.

- [ ] **Step 2: Push**

```bash
git push
```

- [ ] **Step 3: Verify PR #7 (or create new)**

If on `docs/update-changelog`:
```bash
gh pr view 7
```

Check that all three new files appear in the diff:
```bash
gh pr diff 7 | grep "^diff --git" | grep -E "release-please|\.release-please"
```

Expected — three lines:
```
diff --git a/.github/workflows/release-please.yml ...
diff --git a/.release-please-config.json ...
diff --git a/.release-please-manifest.json ...
```

- [ ] **Step 4: Wait for CI to pass**

```bash
gh pr checks 7 --watch
```

Expected: all checks green.

- [ ] **Step 5: Merge PR**

```bash
gh pr merge 7 --squash --subject "ci: add release-please automation"
```

After merge, release-please fires on master for the first time. It will open a Release PR titled `chore(main): release 1.1.4` (or similar, depending on commit types since v1.1.3).

- [ ] **Step 6: Verify Release PR was created**

```bash
gh pr list --label "autorelease: pending"
```

Expected: one PR from `release-please--branches--master--components--`.
