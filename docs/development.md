# Local development and session handoff

Read [AGENTS.md](../AGENTS.md) first for scope and invariants, [README](../README.md) for usage and limitations, [CHANGELOG](../CHANGELOG.md) for release history, and [architecture](architecture.md) before changing behavior. This guide covers the local workflow; site installation and release publication are separate tasks.

## Resume an existing checkout

Run from the repository root:

```sh
pwd
git status --short --branch
git remote -v
git branch -vv
git log -1 --format='%H %s'
git tag --points-at HEAD
git ls-remote origin HEAD refs/heads/main 'refs/tags/*'
```

The expected origin is `https://github.com/enuzzo/wpml-elementor-component-fix.git`. If this is already the correct checkout, reuse it. Clone into the current directory only when it is empty; never initialize another repository over existing files or create a second GitHub repository. Review local changes before switching branches or updating from upstream. Do not reset, clean, overwrite or stash someone else's work automatically.

For new work, create a descriptive branch from the agreed starting commit, for example `git switch -c codex/documentation-update`. Resume the existing branch when continuing the same task. Compare remote refs before deciding whether an upstream update is needed; do not assume local `origin/main` is current.

## Local prerequisites and checks

The harness needs PHP CLI and Python 3, with no Composer or npm dependencies. It does not install WordPress, WPML or Elementor. Record the versions used:

```sh
php --version
python3 --version
find plugin tests -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/run.php
python3 scripts/build.py
git diff --check
```

The 1.0.2 candidate runs 20 isolated scenarios (15 in published 1.0.1). Failures stop the runner. These are synthetic contract tests, including extraction/import behavior, source immutability, identities, links, native component support and conservative handling of unknown contracts. The additional form scenarios model field registration semantics; they are not a live CMS test.

Inspect [.github/workflows/tests.yml](../.github/workflows/tests.yml) when changing prerequisites or checks. The current CI runs syntax checks, the synthetic suite and packaging on PHP 7.4, 8.1, 8.3 and 8.5. If GitHub CLI is available, inspect results with:

```sh
gh run list --workflow tests.yml --limit 5
gh release view v1.0.1
```

Check the run's commit and individual jobs before claiming CI success for a change. An upstream run cannot validate uncommitted local documentation. Local use of PHP 8.5 does not by itself establish PHP 7.4 compatibility.

## Inspect the generated package

The builder reads the version from the PHP header, checks the WordPress stable tag, builds from an explicit allowlist, and verifies archive integrity and byte-for-byte payload equality. It uses fixed ZIP timestamps and permissions. `dist/` is generated and excluded from Git.

For the current 1.0.2 candidate, inspect the archive and verify its separate checksum:

```sh
python3 -m zipfile -l dist/netmilk-wpml-component-compat-1.0.2.zip
(cd dist && shasum -a 256 -c SHA256SUMS)
git check-ignore dist/netmilk-wpml-component-compat-1.0.2.zip dist/SHA256SUMS
```

The exact ZIP payload is:

```text
netmilk-wpml-component-compat/LICENSE
netmilk-wpml-component-compat/netmilk-wpml-component-compat.php
netmilk-wpml-component-compat/readme.txt
```

The root license is copied into the package by the builder. No tests, scripts, repository docs, artwork, `.git` or vendor sources belong in the ZIP. When the version changes, update versioned commands and documentation together. Building locally does not publish a release or install anything on a site.

## Evidence levels and future integration work

| Evidence | What it establishes |
| --- | --- |
| PHP lint and synthetic contract suite | Behavior under the original public doubles on the tested interpreter |
| Build, payload and checksum checks | Integrity and expected structure of the generated installable package |
| WordPress installation and activation | Whether that package loads on one recorded stack |
| Fresh WPML export, native import and rendered-page inspection | End-to-end behavior for the tested fields on that recorded stack |

For a separately authorized site test, read that site's instructions and backup requirements first. Follow [Install and verify](../README.md#install-and-verify), use fresh jobs and record exact PHP, WordPress, WPML, String Translation, Elementor and Elementor Pro versions. Check multiple explicit overrides, markup, field identities, links and source immutability. Inherited defaults remain outside scope. Keep client evidence outside this public repository; share only a sanitized summary and invented reproduction here.

For removal after an official update, follow [Remove it when native support works](../README.md#remove-it-when-native-support-works). The in-memory probe cannot substitute for testing the actual fields with the adapter deactivated.

## Verified starting point — 2026-10-02

This is a historical baseline, not proof of future checkout or dependency state:

- The existing checkout was clean on `main`; local HEAD, remote `main` and tag `v1.0.1` all pointed to `a1bd9a6e6a866fc61c9707055507d4d62405a9a4`.
- [Release v1.0.1](https://github.com/enuzzo/wpml-elementor-component-fix/releases/tag/v1.0.1) was published with the plugin ZIP and `SHA256SUMS`; it was neither a draft nor a prerelease.
- The latest completed [CI run for that commit](https://github.com/enuzzo/wpml-elementor-component-fix/actions/runs/36980603102) succeeded.
- Local setup checks passed with PHP 8.5.11 and Python 3.14.7: all six PHP files passed lint, all 15 synthetic scenarios passed, and the build verified its allowlisted payload. The generated ZIP's SHA-256 was `b8b1366e57b3a281baca601ec48e67ca3867442beba20fb281df03a6eb0432b5`, matching the published release asset's digest.
- The plugin declares PHP 7.4+ and WordPress 6.5+. No WordPress stack was installed for this setup; live export/import/render certification remains open.

## End-of-session handoff

The subsequent 1.0.2 candidate adds the narrowly scoped [form-name registration correction](form-name-compatibility.md). Keep its status separate from the historical 1.0.1 baseline above: the candidate needs its own CI run and real-stack acceptance before publication. Do not overwrite the published 1.0.1 tag or release assets.

Report the local path, origin, branch and full HEAD commit, then list changed files and whether they are uncommitted, committed, pushed or published. Include commands run, runtime versions, outcomes, artifact path/checksum and any remaining evidence gaps. Record pending documentation work under Unreleased without changing the plugin version. Do not report installation or a release unless it was separately requested and actually completed.
