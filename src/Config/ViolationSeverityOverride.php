<?php

declare(strict_types=1);

namespace LaravelGuard\Guard\Config;

use LaravelGuard\Guard\Violations\Violation;

final class ViolationSeverityOverride
{
    public static function apply(Violation $violation, GuardConfig $config): Violation
    {
        $ruleId = $violation->ruleId;

        if ($ruleId === null) {
            return $violation;
        }

        $override = $config->ruleOption($ruleId)?->severity;

        if ($override === null) {
            return $violation;
        }

        return new Violation(
            severity: $override,
            file: $violation->file,
            line: $violation->line,
            message: $violation->message,
            suggestion: $violation->suggestion,
            ruleId: $violation->ruleId,
        );
    }
}
