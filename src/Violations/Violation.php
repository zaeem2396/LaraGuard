<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Violations;

/**
 * @immutable
 */
final readonly class Violation
{
    public function __construct(
        public Severity $severity,
        public string $file,
        public int $line,
        public string $message,
        public ?string $suggestion = null,
        public ?string $ruleId = null,
    ) {}

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'severity' => $this->severity->value,
            'file' => $this->file,
            'line' => $this->line,
            'message' => $this->message,
            'suggestion' => $this->suggestion,
            'rule_id' => $this->ruleId,
        ];
    }
}
