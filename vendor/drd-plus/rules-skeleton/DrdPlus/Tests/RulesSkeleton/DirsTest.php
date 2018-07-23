<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\Dirs;
use DrdPlus\Tests\RulesSkeleton\Partials\DirsForTestsTrait;

class DirsTest extends \DrdPlus\Tests\FrontendSkeleton\DirsTest
{
    use DirsForTestsTrait;

    /**
     * @test
     */
    public function I_will_get_current_skeleton_root_as_default_document_root(): void
    {
        $expectedDocumentRoot = \realpath($this->getDocumentRoot());
        self::assertFileExists($expectedDocumentRoot, 'No real path found from document root ' . $this->getDocumentRoot());
        self::assertSame($expectedDocumentRoot, \realpath((new Dirs($this->getMasterDocumentRoot(), $this->getDocumentRoot()))->getDocumentRoot()));
    }

    /**
     * @test
     */
    public function I_will_get_current_document_root_related_passed_web_root_as_default(): void
    {
        $expectedPassedWebRoot = \realpath($this->getDocumentRoot() . '/web/passed');
        self::assertFileExists($expectedPassedWebRoot, 'No real path found from passed web root ' . $this->getDocumentRoot());
        $dirs = new Dirs($this->getMasterDocumentRoot(), $this->getDocumentRoot());
        self::assertSame($expectedPassedWebRoot, \realpath($dirs->getWebRoot()), "Unexpected {$dirs->getWebRoot()}");
    }

    /**
     * @test
     */
    public function I_will_get_current_skeleton_generic_parts_root_as_default(): void
    {
        $expectedGenericPartsRoot = __DIR__ . '/../../../parts/rules-skeleton';
        $expectedGenericPartsRootRealPath = \realpath(__DIR__ . '/../../../parts/rules-skeleton');
        self::assertFileExists($expectedGenericPartsRootRealPath, 'No real path found from rules skeleton parts dir ' . $expectedGenericPartsRoot);
        self::assertSame($expectedGenericPartsRootRealPath, \realpath((new Dirs($this->getMasterDocumentRoot(), $this->getDocumentRoot()))->getGenericPartsRoot()));
    }

    /**
     * @test
     */
    public function I_can_change_web_root(): void
    {
        $dirs = new Dirs($this->getMasterDocumentRoot(), $this->getDocumentRoot());
        self::assertSame(\realpath($this->getWebRoot()), \realpath($dirs->getWebRoot()));
        $dirs->setWebRoot('foo/bar/baz');
        self::assertSame('foo/bar/baz', $dirs->getWebRoot());
    }
}