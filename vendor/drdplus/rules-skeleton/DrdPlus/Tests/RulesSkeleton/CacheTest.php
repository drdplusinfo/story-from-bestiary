<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\Cache;

class CacheTest extends \DrdPlus\Tests\FrontendSkeleton\CacheTest
{
    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        if ($sutTestClass === null) {
            return Cache::class;
        }

        return parent::getSutClass($sutTestClass, $regexp);
    }
}