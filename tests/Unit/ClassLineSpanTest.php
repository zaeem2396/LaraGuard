<?php

declare(strict_types=1);

use LaravelGuard\Guard\Support\ClassLineSpan;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

it('counts inclusive lines for a class node', function (): void {
    $code = <<<'PHP'
<?php

namespace App\Services;

class Example
{
    public function one(): void
    {
    }

    public function two(): void
    {
    }
}
PHP;

    $statements = (new ParserFactory)->createForHostVersion()->parse($code);
    $class = (new NodeFinder)->findFirstInstanceOf($statements ?? [], Class_::class);

    expect($class)->toBeInstanceOf(Class_::class);
    expect(ClassLineSpan::inclusiveLineCount($class))->toBeGreaterThan(5);
});
