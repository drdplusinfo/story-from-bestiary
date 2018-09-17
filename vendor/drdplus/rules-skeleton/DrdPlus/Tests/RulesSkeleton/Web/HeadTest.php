<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Web;

use DrdPlus\FrontendSkeleton\Web\Head;

class HeadTest extends \DrdPlus\Tests\FrontendSkeleton\Web\HeadTest
{
    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        return Head::class;
    }
}