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
        self::assertSame('foo/parts', $dirs->getPartsRoot());
        self::assertSame($this->unifyPath($this->getGenericPartsRoot()), $this->unifyPath($dirs->getGenericPartsRoot()));
        self::assertSame('foo/versions', $dirs->getDirForVersions());
        self::assertSame('foo/cache/' . \PHP_SAPI, $dirs->getCacheRoot());
        self::assertSame('foo/versions/1.2', $dirs->getVersionRoot('1.2'));
        self::assertSame(
            $this->unifyPath($this->getDocumentRoot() . '/web/pass'),
            $this->unifyPath($dirs->getVersionWebRoot('1.2'))
        );
        $dirs->allowAccessToWebFiles();
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

    /**
     * @test
     */
    public function I_can_disable_web_files_restriction(): void
    {
        $expectedPassWebRoot = $this->unifyPath($this->getDocumentRoot() . '/web/pass');
        $expectedPassedWebRoot = $this->unifyPath($this->getDocumentRoot() . '/versions/foo/web');
        $dirs = $this->createDirs();
        self::assertFalse($dirs->isAllowedAccessToWebFiles(), 'Web files should be restricted by default');
        self::assertSame($expectedPassWebRoot, $this->unifyPath($dirs->getVersionWebRoot('foo')));
        $dirs->allowAccessToWebFiles();
        self::assertTrue($dirs->isAllowedAccessToWebFiles(), 'Web files should be no more restricted');
        self::assertSame($expectedPassedWebRoot, $this->unifyPath($dirs->getVersionWebRoot('foo')));
    }

    /**
     * @test
     */
    public function I_will_get_current_skeleton_generic_parts_root_as_default(): void
    {
        $expectedGenericPartsRoot = __DIR__ . '/../../../parts/rules-skeleton';
        $expectedGenericPartsRootRealPath = \realpath(__DIR__ . '/../../../parts/rules-skeleton');
        self::assertFileExists($expectedGenericPartsRootRealPath, 'No real path found from rules skeleton parts dir ' . $expectedGenericPartsRoot);
        self::assertSame($expectedGenericPartsRootRealPath, \realpath($this->createDirs()->getGenericPartsRoot()));
    }
}