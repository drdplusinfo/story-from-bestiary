<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\Dirs;
use DrdPlus\Tests\FrontendSkeleton\Partials\AbstractContentTest;

class PartsTest extends AbstractContentTest
{
    /**
     * @test
     * @backupGlobals enabled
     */
    public function All_parts_from_frontend_skeleton_are_accessible_in_current_generic_parts_dir(): void
    {
        $dirs = null;
        $indexPath = $this->getDocumentRoot() . '/index.php';
        self::assertFileExists($indexPath, 'Index is missing');
        \ob_start();
        /** @noinspection PhpIncludeInspection */
        include $indexPath;
        \ob_end_clean();
        self::assertNotEmpty($dirs, 'Dirs variable stays empty after including index');
        self::assertInstanceOf(Dirs::class, $dirs);
        /** @var Dirs $dirs */
        self::assertDirectoryExists($dirs->getGenericPartsRoot(), 'Generic parts root does not exist');
        $genericParts = $this->getDirFiles($dirs->getGenericPartsRoot());
        self::assertNotEmpty($genericParts, "NO generic parts found in {$dirs->getGenericPartsRoot()}");
        $missingGenericParts = \array_diff($this->getFrontendSkeletonGenericParts(), $genericParts);
        self::assertEmpty(
            $missingGenericParts,
            "Some frontend skeleton generic parts are not included in {$dirs->getGenericPartsRoot()}: "
            . \print_r($missingGenericParts, true)
        );
    }

    private function getFrontendSkeletonGenericParts(): array
    {
        $expectedGenericPartsDir = $this->getDocumentRoot() . '/vendor/drdplus/frontend-skeleton/parts/frontend-skeleton';
        self::assertDirectoryExists($expectedGenericPartsDir, 'Can not find frontend skeleton parts dir');
        $expectedGenericParts = $this->getDirFiles($expectedGenericPartsDir);
        self::assertNotEmpty($expectedGenericParts, "No frontend skeleton generic parts found in {$expectedGenericPartsDir}");

        return $expectedGenericParts;
    }

    private function getDirFiles(string $dir): array
    {
        $folders = [];
        foreach (\scandir($dir, \SCANDIR_SORT_NONE) as $folder) {
            if ($folder === '.' || $folder === '..') {
                continue;
            }
            $folders[] = $folder;
        }

        return $folders;
    }
}