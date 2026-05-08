# Repository Guidelines

## Project Structure & Module Organization

This repository hosts a PHP Composer library around `google/gax` `TransportInterface`, targeting FrankenPHP Go gRPC and `php-grpc-lite`. Keep production code under `src/`, tests under `tests/`, and design documents under `docs/`. Composer metadata, autoloading, and scripts belong in `composer.json`. Use `docs/issues/open/` for active findings and move resolved items to `docs/issues/closed/`. Prefer one Markdown file per issue, named like `transport-error-mapping.md`.

## Build, Test, and Development Commands

Use the Dev Container when the host lacks PHP or Composer; it provides PHP 8.4+ and Composer.

Use Composer scripts as stable entry points:

- `composer install`: install dependencies.
- `composer test`: run tests.
- `composer lint`: run static analysis and style checks.
- `composer validate`: verify package metadata.

Prefer Composer scripts over ad hoc one-off commands.

## Coding Style & Naming Conventions

Use `declare(strict_types=1);`, typed properties, parameters, and returns. Follow PSR-4 autoloading and PSR-12 formatting. Keep public Composer APIs stable and minimal. Name interfaces by behavior, for example `UnaryBackend`. Keep GAX transport, FrankenPHP bridge, and `php-grpc-lite` mapping code in separate namespaces.

## Testing Guidelines

Use PHPUnit or Pest consistently once chosen. Test files should mirror `src/` paths and end with `Test.php`. Cover request conversion, response streaming, error/status mapping, deadlines, metadata, and cancellation. Add regression tests for closed issues when feasible.

## Design Docs & Issue Tracking

Keep architecture notes, API sketches, and tradeoff records in `docs/design.md`. Update it to the latest current design only; avoid stale historical alternatives. Every user instruction, work unit, and reviewer finding must have its own Markdown issue file; do not collapse multiple findings into one issue. Create active items under `docs/issues/open/`; after implementation and verification, move the file to `docs/issues/closed/` and update its state. Each issue should include state, source, context, impact, proposed fix, fix summary, and verification.

## Review Workflow

After implementation, run focused reviews before closing work. At minimum, include a domain-model review for naming, responsibilities, invariants, lifecycle, and boundaries. Add reviewers for transport behavior, tests, and maintainability as needed. Repeat fix and review cycles until no actionable findings remain.

## Commit & Pull Request Guidelines

Use concise imperative commits, optionally with Conventional Commit prefixes such as `feat:`, `fix:`, `test:`, and `docs:`. Pull requests should summarize behavior changes, list verification commands, link related `docs/issues` files, and call out any public API or compatibility impact.
