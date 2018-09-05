<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\WebVersions;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTestTrait;

class WebVersionsTest extends \DrdPlus\Tests\FrontendSkeleton\WebVersionsTest
{
    use AbstractContentTestTrait;

    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        return WebVersions::class;
    }
}