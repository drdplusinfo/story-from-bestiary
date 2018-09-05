<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\Dirs;
use DrdPlus\Tests\RulesSkeleton\Partials\DirsForTestsTrait;

class DirsTest extends \DrdPlus\Tests\FrontendSkeleton\DirsTest
{
    use DirsForTestsTrait;

    /**
     * @test
     */
    public function I_can_use_it(): void
    {
        $dirsClass = static::getSutClass();
        /** @var Dirs $dirs */
        $dirs = new $dirsClass('foo');
        self::assertSame('foo', $dirs->getDocumentRoot());
        self::assertSame('foo/vendor', $dirs->getVendorRoot());
        self::assertSame('foo/versions', $dirs->getDirForVersions());
        self::assertSame('foo/cache/' . \PHP_SAPI, $dirs->getCacheRoot());
        self::assertSame('foo/versions/1.2', $dirs->getVersionRoot('1.2'));
        self::assertSame('foo/versions/1.2/web', $dirs->getVersionWebRoot('1.2'));
    }

    /**
     * @test
     */
    public function I_will_get_current_skeleton_root_as_default_document_root(): void
    {
        $expectedDocumentRoot = \realpath($this->getDocumentRoot());
        self::assertFileExists($expectedDocumentRoot, 'No real path found from document root ' . $this->getDocumentRoot());
        self::assertSame($expectedDocumentRoot, \realpath($this->createDirs()->getDocumentRoot()));
    }
}