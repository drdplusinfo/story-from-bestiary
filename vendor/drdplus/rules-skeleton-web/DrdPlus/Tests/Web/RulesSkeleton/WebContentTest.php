<?php
declare(strict_types=1);

namespace DrdPlus\Tests\Web\RulesSkeleton;

use PHPUnit\Framework\TestCase;

class WebContentTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_get_web_files_from_expected_directory(): void
    {
        $websDir = $this->getDocumentRoot() . '/web';
        self::assertDirectoryExists($websDir, 'Can not find dir with web files');
    }

    protected function getDocumentRoot(): string
    {
        return \DRD_PLUS_DOCUMENT_ROOT;
    }
}