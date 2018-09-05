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
     * @dataProvider provideTablesIdsParameterName
     * @param string $parameterName
     */
    public function I_can_get_wanted_tables_ids(string $parameterName): void
    {
        self::assertSame([], (new Request(new Bot()))->getWantedTablesIds());
        $_GET[$parameterName] = '    ';
        self::assertSame([], (new Request(new Bot()))->getWantedTablesIds());
        $_GET[$parameterName] = 'foo';
        self::assertSame(['foo'], (new Request(new Bot()))->getWantedTablesIds());
        $_GET[$parameterName] .= ',bar,baz';
        self::assertSame(['foo', 'bar', 'baz'], (new Request(new Bot()))->getWantedTablesIds());
        unset($_GET[$parameterName]); // to avoid using this in next iteration as @backupGlobals does not work
    }

    public function provideTablesIdsParameterName(): array
    {
        return [
            [Request::TABLES],
            [Request::TABULKY],
        ];
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_can_get_current_request_path(): void
    {
        self::assertSame('', (new Request(new Bot()))->getPath());
        $_SERVER['PATH_INFO'] = '/foo/bar/baz-qux';
        self::assertSame('/foo/bar/baz-qux', (new Request(new Bot()))->getPath());
    }
}