<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\AssetsVersion;

class AssetsVersionTest extends \DrdPlus\Tests\FrontendSkeleton\AssetsVersionTest
{
    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        return AssetsVersion::class;
    }

}