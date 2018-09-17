<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\RulesSkeleton\Request;

class RequestTest extends \DrdPlus\Tests\FrontendSkeleton\RequestTest
{
    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_can_get_path_info(): void
    {
        $_SERVER['PATH_INFO'] = null;
        $request = new Request(new Bot());
        self::assertSame('', $request->getPathInfo());
        $_SERVER['PATH_INFO'] = 'foo/bar';
        self::assertSame('foo/bar', $request->getPathInfo());
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_can_get_query_string(): void
    {
        $_SERVER['QUERY_STRING'] = null;
        $request = new Request(new Bot());
        self::assertSame('', $request->getQueryString());
        $_SERVER['QUERY_STRING'] = 'foo=bar';
        self::assertSame('foo=bar', $request->getQueryString());
    }
}