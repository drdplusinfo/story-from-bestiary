<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\AssetsVersion;

class AssetsVersionTest extends \DrdPlus\Tests\FrontendSkeleton\AssetsVersionTest
{
    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        return AssetsVersion::class;
    }

}