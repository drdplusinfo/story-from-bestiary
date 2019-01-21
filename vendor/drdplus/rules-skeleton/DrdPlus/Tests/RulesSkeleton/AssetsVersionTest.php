<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\AssetsVersion;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;
use Gt\Dom\Element;

class AssetsVersionTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function All_css_files_have_versioned_assets(): void
    {
        $assetsVersionClass = static::getSutClass();
        /** @var AssetsVersion $assetsVersion */
        $assetsVersion = new $assetsVersionClass(true /* scan for CSS */);
        $changedFiles = $assetsVersion->addVersionsToAssetLinks(
            $this->getProjectRoot(),
            [$this->getProjectRoot() . '/css'],
            [],
            [],
            true // dry run
        );
        self::assertCount(
            0,
            $changedFiles,
            "Expected all CSS files already transpiled to have versioned links to assets, but those are not: \n"
            . \implode("\n", $changedFiles)
            . "\ntranspile them:\nphp ./vendor/bin/assets --css --dir=css"
        );
    }

    protected function getBinAssetsFile(): string
    {
        $assetsFile = $this->getVendorRoot() . '/bin/assets';
        if (!\file_exists($assetsFile)) {
            throw new \LogicException('Can not find bin/assets file');
        }

        return $assetsFile;
    }

    /**
     * @test
     */
    public function I_can_use_helper_script(): void
    {
        $binAssetsEscaped = \escapeshellarg($this->getBinAssetsFile());
        $output = $this->runCommand("php $binAssetsEscaped");
        self::assertNotEmpty($output);
        self::assertStringStartsWith('Options are', $output[0]);
    }

    /**
     * @test
     */
    public function I_can_run_script_for_cli_assets_control(): void
    {
        $filePermissions = \fileperms($this->getBinAssetsFile());
        $inOctal = \decoct($filePermissions & 0777);
        self::assertSame(
            '775',
            $inOctal,
            "Expected {$this->getBinAssetsFile()} to has executable permissions 0775 as Composer will do that anyway later on this library installation"
        );
    }

    /**
     * @test
     */
    public function Assets_have_valid_links(): void
    {
        $projectRoot = $this->getProjectRoot();
        $checkedCount = 0;
        $htmlDocument = $this->getHtmlDocument();
        foreach ($htmlDocument->getElementsByTagName('img') as $image) {
            $checkedCount += $this->Asset_file_exists($image, 'src', $projectRoot);
        }
        foreach ($htmlDocument->getElementsByTagName('link') as $link) {
            $checkedCount += $this->Asset_file_exists($link, 'href', $projectRoot);
        }
        foreach ($htmlDocument->getElementsByTagName('script') as $script) {
            $checkedCount += $this->Asset_file_exists($script, 'src', $projectRoot);
        }
        self::assertGreaterThan(0, $checkedCount, 'No assets has been checked');
    }

    private function Asset_file_exists(Element $element, string $parameterName, string $masterDocumentRoot): int
    {
        $urlParts = \parse_url($element->getAttribute($parameterName));
        if (!empty($urlParts['host'])) {
            return 0;
        }
        $path = $urlParts['path'];
        self::assertFileExists($masterDocumentRoot . '/' . \ltrim($path, '/'), $element->outerHTML);

        return 1;
    }
}