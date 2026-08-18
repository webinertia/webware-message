# Webware Tools Alignment — webware-message CI/CD

## Purpose

Align `webware/webware-message` CI/CD pipeline and dev-tooling configuration with
`webware/webware-mailer`, the reference implementation attached to this workspace.
webware-mailer already completed the same alignment against `webware/webware-log`
(see mailer's `docs/webware-tools-alignment.md`); mailer is now the canonical
consumer shape, and webware-message adopts the same tooling with message-specific
inputs. Both packages are tooled by the `webware-tools` reusable workflow
(`webinertia/webware-tools@0.1.x`).

**Scope:** CI/CD pipeline, tooling configs, composer metadata, baseline files,
test scaffolding, and the dependency audit fixes surfaced while doing so.

**Explicitly out of scope (per user):**
- PHPStan — no `phpstan.neon.dist`, no `stubs/`, no PHPStan composer dependency.
  (The reusable workflow runs no PHPStan job either.)
- Local Docker dev tooling: `bin/install-deps.sh`, `compose.yml`, `docker/`.
- `README.md` / `CONTRIBUTING.md` authoring — webware-message currently ships
  neither (mailer ships both). Noted, not part of this alignment.
- Functional changes to `src/` beyond what mago findings force.

## Current state vs. target

webware-message today (branch `align-with-webware-tools`, created from `0.1.x`):
tracked `LICENSE`, `composer.json`, `src/` (17 classes). No `.github/`, no
`.gitattributes`, no `.gitignore`, no `docs/`, no `phpunit.xml.dist`, no tests,
no baselines, no `composer.lock`, no CI.

| Artifact | webware-mailer (reference) | webware-message (current) | Action |
|---|---|---|---|
| `.github/workflows/continuous-integration.yml` | wrapper calling webware-tools reusable workflow | absent | create (message inputs) |
| `.github/copilot-instructions.md` | present | absent | port verbatim |
| `phpunit.xml.dist` | PHPUnit 13.1 schema, strict flags, no extensions/env | absent | copy verbatim |
| `mago.toml` + baselines | extends webware-tools, baselines populated | absent | create; baselines start empty |
| `infection.json5.dist` | present | absent | copy |
| `codecov.yml` | present | absent | copy |
| `renovate.json` | `local>webinertia/.github:renovate-config` | absent | copy |
| `phpbench.json.dist` | present, config only (no `benchmarks/` dir) | absent | copy (config only) |
| `.gitattributes` / `.gitignore` | present | absent | copy verbatim |
| `composer.lock` | committed | absent | generate + commit |
| `composer.json` scripts | `test`, `test-coverage`, `test-integration`, `mutation-test`, `test-all` | `test-all`, `test`, `test-coverage` (partial) | align |
| PHPUnit version | `^13.3.0` | `^13.3` | normalize to `^13.3.0` |
| `config.platform.php` | `8.4.99` | `8.4.99` | none (already aligned) |
| `autoload-dev` PSR-4 | `WebwareTest\Mailer\` → `test/unit/`, `WebwareTestIntegration\Mailer\` → `test/integration/` | `WebwareTest\Message\` → `test/` | split into unit + integration |
| test suites (`unit test`, `integration test`) | both defined + populated | no `test/` dir | create |
| `infection/infection`, `phpbench/phpbench`, `roave/backward-compatibility-check`, `webware/webware-tools`, `laminas/laminas-diactoros`, `laminas/laminas-view` in require-dev + `suggest` for laminas-view | present | absent | add |
| `php-db/phpdb-mysql`, `webware/webware-mailer` in require-dev | absent | present | drop (unused by src and tests) |
| `infection/extension-installer` allow-plugin | present | absent | add |

## Reference mechanics (what the reusable workflow expects)

`webinertia/webware-tools/.github/workflows/continuous-integration.yml@0.1.x`
exposes `workflow_call`:

- **Inputs:** `php-versions` (JSON array), `run-integration` (bool),
  `composer-options`, `db-image`, `db-env-json`, `db-port`, `db-health-cmd`,
  `db-health-retries`, `db-health-interval-seconds`, `enable-codecov` (bool),
  `enable-infection` (bool), `coverage-php-version`, `min-msi`,
  `min-covered-msi`, `test-env-json`.
- **Secrets:** `CODECOV_TOKEN` (optional), `INFECTION_DASHBOARD_API_KEY`
  (optional) — both forwarded via `secrets: inherit`.
- **Jobs:**
  1. **mago** — matrix over `php-versions`; `composer install`; runs
     `mago format --check`, `mago lint`, `mago analyze`, `mago guard`.
  2. **test** — matrix `php-versions` × `[lowest, locked, latest]`; runs
     `composer test` (non-canonical legs) or `composer test-coverage`
     (canonical leg: `coverage-php-version` + `locked`, pcov); runs
     `composer test-integration` when `run-integration`; uploads `clover.xml`.
  3. **codecov** — needs `test`; `codecov/codecov-action@v5`,
     `files: clover.xml`, `fail_ci_if_error: false`.
  4. **mutation-test** — needs `test`; Infection invokes Mago via
     `staticAnalysisTool` in `infection.json5.dist`.

Implications for this repo:

- `composer test` / `test-coverage` / `test-integration` / `mutation-test`
  scripts must exist.
- `phpunit.xml.dist` must define suites named `unit test` and
  `integration test`.
- `composer.lock` must be committed (the `locked` matrix leg).
- `mago.toml` must be present (format/lint/analyze/guard all run against it).
- A DB container is **not** used (`db-image` omitted ⇒ both DB steps skipped).
- Pipeline is red until tests exist — PHPUnit 13 errors on zero executed
  tests, and Infection cannot score an empty suite. With `min-msi` set,
  suite coverage must actually reach it (see work item 11).

## Differences from the webware-mailer alignment

1. **Reference changes.** Reference is webware-mailer itself (post-alignment),
   not webware-log. Mailer's `docs/webware-tools-alignment.md` remains the
   canonical recipe; only differences are listed here.
2. **Integration shape.** Mailer integration sends SMTP against an in-process
   fake server. Message integration exercises `MessageMiddleware` +
   `mezzio-session-ext` entirely in-process — no SMTP, no containers, no
   `db-image` inputs. `run-integration: true`, DB inputs omitted.
3. **No version bumps needed.** PHPUnit already `^13.3` and
   `config.platform.php` already `8.4.99` — normalization only, unlike mailer
   which bumped from PHPUnit 12 and `8.4.1`.
4. **Composer dependency audit** (work item 12) surfaces message-specific
   gaps mailer does not have (dangling `SystemMessage` import, laminas-view
   runtime reference, unused require-dev entries).
5. **MSI.** Same `95`/`95` as mailer (user decision) — implies the full unit
   suite is in scope, same as mailer's end state.
6. **Repo-level docs missing.** No `README.md` / `CONTRIBUTING.md` exist in
   webware-message. Out of scope here.

## Work items

### 1. `composer.json`

- `require.php`: keep `~8.4.1 || ~8.5.0`. Keep `mezzio/mezzio-session-ext`
  `^1.21.0` and `webware/message-bus` `^2.0.0-beta.1` as-is.
- `require-dev`:
  - normalize `phpunit/phpunit` `^13.3` → `^13.3.0`
  - add `infection/infection: ^0.34.1`
  - add `phpbench/phpbench: ^1.7`
  - add `roave/backward-compatibility-check: ^8.21.0`
  - add `webware/webware-tools: ^0.1.x-dev`
  - add `laminas/laminas-diactoros` (integration tests use real
    request/response instances)
  - add `laminas/laminas-view` (dev-only; `MessageMiddlewareFactory` resolves
    `HelperPluginManager` — see work item 12.1)
  - drop `php-db/phpdb-mysql` and `webware/webware-mailer` — neither is used
    by `src/` nor needed by the test suites
  - keep `psr/container`, `psr/http-message`, `psr/http-server-handler`,
    `psr/http-server-middleware`, `roave/security-advisories`,
    `webware/messagebus-event`.
- add a top-level `suggest` block: `"laminas/laminas-view"` (consuming
  Mezzio apps typically provide it via the laminas-view renderer; required
  only when the view-helper integration is used).
- `config.platform.php`: `8.4.99` already — no change.
- `config.allow-plugins`: add `"infection/extension-installer": true`.
- `autoload-dev`: change `WebwareTest\Message\` mapping from `test/` to
  `test/unit/`; add `WebwareTestIntegration\Message\` → `test/integration/`
  (mirrors mailer's `WebwareTest\Mailer\` / `WebwareTestIntegration\Mailer\`).
- `scripts` (align with mailer):
  - `test-all`: `["@test", "@test-integration"]`
  - `test`: `phpunit --no-coverage --colors=always --testsuite "unit test"`
  - `test-coverage`: `phpunit --colors=always --coverage-clover clover.xml
    --coverage-html coverage/html --coverage-text`
  - `test-integration`: `phpunit --no-coverage --colors=always --testsuite
    "integration test"`
  - `mutation-test`: `infection`
- No PHPStan package, no PHPStan script.

### 2. `phpunit.xml.dist` (new)

Copy webware-mailer's file verbatim — it already contains no mailer-specific
extensions or `<env>` vars, so nothing to strip:

- schema 13.1, `bootstrap="vendor/autoload.php"`, `colors="true"`,
  `cacheDirectory=".phpunit.cache"`.
- Strict flags: `requireCoverageMetadata="true"`, `failOnNotice="true"`,
  `failOnDeprecation="true"`, `failOnWarning="true"`.
- Testsuites: `unit test` → `test/unit`; `integration test` → `test/integration`.
- `<source restrictNotices="true" ignoreIndirectDeprecations="true">`
  including `src`.

### 3. Mago tooling

- `mago.toml` (new), same shape as mailer:
  - `extends = "vendor/webware/webware-tools/mago.toml"`
  - `php-version = "8.4.1"`
  - `[linter] baseline = "lint-baseline.toml"`;
    `[analyzer] baseline = "analysis-baseline.toml"`
  - `[source] paths = ["src", "test"]`, `includes = ["vendor"]`
- `lint-baseline.toml` + `analysis-baseline.toml` (new): start empty (mailer's
  are populated after its own fix pass); populate only with issues accepted as
  intentional after the fix pass. Suppressions require explicit user approval
  per issue — never auto-suppress.
- Fix pass: `mago format`, then `mago lint` + `mago analyze` + `mago guard`;
  fix all findings in `src/`; baseline only the approved remainder.

### 4. `infection.json5.dist` (new)

Copy webware-mailer's file: `source.directories = ["src"]`, `timeout = 10`,
`threads = "max"`, logs `text`/`summary`/`stryker` badge regex
`/^\d+\.\d+\.x$/`, `mutators: {"@default": true}`, `staticAnalysisTool: "mago"`.

### 5. `codecov.yml` (new)

Copy webware-mailer verbatim (project/patch targets `auto`, threshold `0%`,
comment layout `diff, flags, files`).

### 6. `renovate.json` (new)

Copy webware-mailer verbatim:
`"extends": ["local>webinertia/.github:renovate-config"]`.

### 7. `phpbench.json.dist` (new)

Copy webware-mailer (`runner.path: benchmarks`, `*Bench.php`). Config only —
no `benchmarks/` directory and no CI job; mailer ships it the same way.

### 8. `.github/workflows/continuous-integration.yml` (new wrapper)

Mirror webware-mailer's wrapper with message inputs:

- `on`: `pull_request` → branches `[0-9]+.[0-9]+.x`; `push` → same branches +
  tags `[0-9]+.[0-9]+.[0-9]+` (same branch-protection rationale as mailer).
- `uses: webinertia/webware-tools/.github/workflows/continuous-integration.yml@0.1.x`
- `secrets: inherit`
- `with`:
  - `php-versions: '["8.4", "8.5"]'`
  - `run-integration: true` — integration suite runs fully in-process
    (middleware + `mezzio-session-ext`); no external services or containers.
  - `enable-codecov: true`
  - `enable-infection: true`
  - `coverage-php-version: "8.5"` (canonical leg, highest supported PHP)
  - `min-msi: "95"`, `min-covered-msi: "95"` — same values as mailer /
    webware-log; the unit suite must actually reach them (work item 11).
  - omit `db-image`, `db-env-json`, `db-port`, `db-health-cmd`,
    `test-env-json` (defaults; no containers in this component's tests).

### 9. `.github/copilot-instructions.md` (new)

Port webware-mailer's file verbatim: PHPUnit 13 mock-vs-stub rules
(`createStub()` for value-returning doubles, `createMock()` only with
`expects()`) and `requireCoverageMetadata="true"` rules
(`#[CoversClass]` / `#[CoversMethod]` per test class).

### 10. `.gitattributes` / `.gitignore` (new)

Copy webware-mailer verbatim (export-ignores for tooling configs + test dir,
LF line endings; ignore `.phpunit.cache`, `clover.xml`, `coverage/`,
`infection.log`, `summary.log`, `vendor/`, etc.).

### 11. Test scaffolding (unit + integration)

Mirror mailer's `test/unit/**` layout, one file per source class — 17 classes
in `src/`:

- `AbstractMessage`, `ConfigProvider`, `MessageIcon`, `MessageLevel`,
  `MessageIconCapableTrait` / `MessageLevelCapableTrait` (via `#[CoversTrait]`),
  `SystemMessengerAwareTrait`, `SystemMessenger`, the three `Exception`
  classes, `Middleware\MessageMiddleware`,
  `Middleware\MessageMiddlewareFactory`, `View\Helper\SystemMessenger`,
  `View\Helper\SystemMessengerFactory`, plus the three `*CapableInterface`
  / `MessageInterface` / `SystemMessengerInterface` contracts via implementing
  classes where exerciseable.
- Coverage metadata per class: `#[CoversClass]` + `#[CoversMethod]` on every
  public/protected method; traits use `#[CoversTrait]`.
- Mock/stub discipline per copilot-instructions: `createStub()` for
  value-returning doubles, `createMock()` only with `expects()`.
- `test/integration/` — `MessageMiddleware` + `mezzio-session-ext` flow
  (session attribute → middleware → helper → next hop), fully in-process.
  **Decision (user):** pull `laminas/laminas-diactoros` as a dev dependency
  and build real request/response instances.
- **MSI:** `min-msi`/`min-covered-msi` stay `95`/`95` (user decision) — full
  unit coverage for all 17 classes is required, not a smoke test. Follow
  mailer's path and write the complete suite now.

### 12. Dependency audit (message-specific gaps)

Findings from inspecting `src/` against `composer.json` — each needs a
decision or fix:

1. `Middleware\MessageMiddlewareFactory` resolves
   `Laminas\View\HelperPluginManager` from the container, but
   `laminas/laminas-view` is nowhere in `composer.json`. **Decision (user):**
   `laminas/laminas-view` becomes a dev dependency with a `suggest` entry;
   keep the factory as written — sharing the helper instance with the view
   layer is deliberate, and `HelperPluginManager` must be loadable for the
   factory test.
2. `Mezzio\Session\SessionMiddleware` is referenced directly in
   `MessageMiddleware`, but `mezzio/mezzio-session` is only transitive via
   `mezzio-session-ext`. Best practice: declare it directly.
3. `php-db/phpdb-mysql: ^0.5.x-dev` in require-dev — zero references in `src/`,
   not needed by tests. **Decision (user):** drop.
4. `webware/webware-mailer: ^1.0.0-beta.1` in require-dev — zero references in
   `src/`, not needed by tests. **Decision (user):** drop.
5. `View\Helper\SystemMessenger` imports `Webware\Message\SystemMessage` at
   line 19 — pre-rename leftover. The class was renamed to `SystemMessenger`
   in an earlier implementation; the import is dead. **Decision (user):**
   remove the import, do not define a `SystemMessage` class.
   (Done — import already removed.)
6. `ConfigProvider` imports `CommandBusInterface` / `BusProvider` but uses
   neither — mago will flag unused imports; remove.
7. `ConfigProvider::getTemplates()` points at `src/../templates/`, which does
   not exist in the repo. Create `templates/` or adjust the path — decision.

(Item 5 resolved: the `SystemMessage` import was a pre-rename leftover from
when the class was renamed to `SystemMessenger` — import removed, no new
class added.)

### 13. Mago fix pass + baselines

Order: `mago format` → `mago lint` → `mago analyze` → `mago guard`. Fix all
findings in `src/` (including work-item-12 items 5 and 6). Anything that
remains intentional goes into the baselines only with explicit user approval
per issue (`@mago-expect` / baseline entry rules apply).

### 14. `composer.lock`

Run `composer update` locally (no `vendor/` exists yet), commit
`composer.lock`. Confirm `webware/webware-tools@0.1.x` and
`infection/infection@0.34` resolve cleanly under the `8.4.99` platform.

### 15. Local green check before PR

- `mago format --check` (0 issues), `mago lint`, `mago analyze`, `mago guard`
- `composer test` (unit suite green)
- `composer test-integration`
- `composer test-coverage` (clover.xml produced, MSI targets met)
- `composer mutation-test` (`--min-msi=95 --min-covered-msi=95`)

### 16. PR

Single PR from `align-with-webware-tools` against `0.1.x` (branch already
exists, based on current `0.1.x` HEAD).

## Status (2026-08-17 — implementation complete, pending user review)

Work items 1–16 executed. Final state:

- **composer**: `mezzio/mezzio-session: ^1.17.0` added as direct dep
  (session-ext 1.21 pins `^1.4`, not 2.x); `laminas/laminas-view: ^3.1.0`
  (2.x is not installable on PHP 8.4 — pins servicemanager 3.x which caps
  PHP <8.4; 3.x uses servicemanager ^4.4); `laminas/laminas-diactoros` dev;
  `php-db/phpdb-mysql` and `webware/webware-mailer` dev deps dropped;
  infection/phpbench/backward-compat/webware-tools added; suggest for
  laminas-view added; autoload-dev split; scripts aligned; lock generated.
- **Hops semantics** (user decision): `send` default hops `1` (next request),
  `sendNow` default `0` (current request only), level proxies default `0`
  with `$now = true` (proxy to `sendNow`).
- **mago**: format applied; lint clean with 12 issues baselined
  (cyclomatic-complexity, kan-defect, too-many-methods, and
  no-boolean-flag-parameter ×4 on `SystemMessenger`; too-many-methods +
  no-boolean-flag-parameter ×4 on `SystemMessengerInterface` — mailer-parity
  families plus the two class-complexity scores; the only code-side
  alternative is splitting the public API). Analyze 0 issues.
- **src fixes**: final classes, `#[Override]`, typed consts, precise
  docblocks, `prepareMessages`/`addHop` rebuilds, `getMessages` foreach,
  view helper `__invoke(): string` + null guard + `implode('<br>')`,
  `MessageMiddleware` session guard throwing `MissingSessionException`,
  factory `@throws`, exhaustive `MessageIcon` match with
  `UnexpectedValueException`, trait fluent setters return `static`,
  exception file renamed to `InvalidSystemMessengerImplementationException.php`,
  dangling `SystemMessage` import removed (pre-rename leftover).
- **Tests**: 62 unit + 1 integration (real ext-session persistence,
  in-process, no containers). Line coverage 98.36%, methods 95%.
- **Mutation**: MSI 97.71% / Covered MSI 97.71% against required 95/95
  (one equivalent mutant — `$hops ?? 2` in `sendNow` guard — remains escaped
  by nature; headroom 2.71 points).
- **Workflow**: wrapper at `webinertia/webware-tools@0.1.x` with
  `php-versions ["8.4","8.5"]`, `run-integration: true`, codecov + infection
  enabled, canonical coverage leg 8.5, `min-msi/min-covered-msi 95`.
- Local note: coverage/infection runs need `XDEBUG_MODE=coverage` locally;
  CI uses pcov on the canonical leg.
