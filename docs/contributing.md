# Contributing

Thanks for helping improve Laravel Guard! This project optimizes for **clear architecture**, **predictable extension points**, and **developer ergonomics**—please keep changes aligned with those goals.

## Tooling

| Command | Purpose |
| ------- | ------- |
| `composer lint` | Laravel Pint (`pint.json`) |
| `composer analyse` | PHPStan + Larastan (`phpstan.neon.dist`) |
| `composer test` | Pest + Orchestra Testbench suite |
| `composer security-audit` | Composer security audit |
| `composer validate-package` | Strict `composer.json` validation |

## Workflow

1. Fork and branch from `main`.
2. Add focused commits with descriptive messages.
3. Ensure Pint, PHPStan, and tests stay green locally.
4. Open a pull request explaining the problem, the solution, and any trade-offs.

## Code expectations

- `declare(strict_types=1);` on every new PHP file.
- Prefer `readonly` classes / properties when mutation is unnecessary.
- Keep rules and scanners cohesive; reach for new contracts only when multiple implementations are imminent.
- AST analysis should remain the default—avoid regex-based “parsing” unless there is a compelling, documented reason.

## Questions?

Open a discussion or issue on GitHub with reproduction steps for bugs or concrete sketches for feature ideas.
