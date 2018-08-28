<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\CookiesService;

class CookiesServiceTest extends \DrdPlus\Tests\FrontendSkeleton\CookiesServiceTest
{
    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        return CookiesService::class;
    }

}