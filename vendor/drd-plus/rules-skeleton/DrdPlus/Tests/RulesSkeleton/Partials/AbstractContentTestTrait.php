<?php
namespace DrdPlus\Tests\RulesSkeleton\Partials;

use DeviceDetector\Parser\Bot;
use DrdPlus\FrontendSkeleton\CookiesService;
use DrdPlus\FrontendSkeleton\Dirs;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\UsagePolicy;
use Granam\String\StringTools;
use Gt\Dom\HTMLDocument;

/**
 * @method string getDocumentRoot
 * @method TestsConfigurationReader getTestsConfiguration
 * @method static assertTrue($value, $message = '')
 * @method static assertFalse($value, $message = '')
 * @method static assertNotSame($expected, $actual, $message = '')
 * @method static fail($message)
 */
trait AbstractContentTestTrait
{
    private static $rulesContentForDev = [];
    private static $rulesForDevHtmlDocument = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->passIn();
    }

    protected function passIn(): bool
    {
        $_COOKIE[$this->getNameForLocalOwnershipConfirmation()] = true; // this cookie simulates confirmation of ownership
        $usagePolicy = new UsagePolicy($this->getVariablePartOfNameForPass(), new Request(new Bot()), new CookiesService());
        self::assertTrue(
            $usagePolicy->hasVisitorConfirmedOwnership(),
            "Ownership has not been confirmed by cookie '{$this->getNameForLocalOwnershipConfirmation()}'"
        );
        $this->needPassOut = false;
        $this->needPassIn = true;

        return true;
    }

    protected function passOut(): bool
    {
        unset($_COOKIE[$this->getNameForLocalOwnershipConfirmation()]);
        $usagePolicy = new UsagePolicy($this->getVariablePartOfNameForPass(), new Request(new Bot()), new CookiesService());
        self::assertFalse(
            $usagePolicy->hasVisitorConfirmedOwnership(),
            "Ownership is still confirmed by cookie '{$this->getNameForLocalOwnershipConfirmation()}'"
        );
        $this->needPassOut = true;
        $this->needPassIn = false;

        return true;
    }

    /**
     * @return array|\Closure[]
     */
    protected function getLicenceSwitchers(): array
    {
        return [[$this, 'passIn'], [$this, 'passOut']];
    }

    protected function isSkeletonChecked(string $skeletonDocumentRoot = null): bool
    {
        $documentRootRealPath = \realpath($this->getDocumentRoot());
        self::assertNotEmpty($documentRootRealPath, 'Can not find out real path of document root ' . \var_export($this->getDocumentRoot(), true));
        $skeletonRootRealPath = \realpath($skeletonDocumentRoot ?? __DIR__ . '/../../../..');
        self::assertNotEmpty($skeletonRootRealPath, 'Can not find out real path of skeleton root ' . \var_export($skeletonRootRealPath, true));
        self::assertSame('rules-skeleton', \basename($skeletonRootRealPath), 'Expected different trailing dir of skeleton document root');

        return $documentRootRealPath === $skeletonRootRealPath;
    }

    protected function getPassDocument(bool $notCached = false): \DrdPlus\FrontendSkeleton\HtmlDocument
    {
        if ($notCached) {
            return new \DrdPlus\FrontendSkeleton\HtmlDocument($this->getPassContent($notCached));
        }
        static $passDocument;
        if ($passDocument === null) {
            $this->removeOwnerShipConfirmation();
            $passDocument = new \DrdPlus\FrontendSkeleton\HtmlDocument($this->fetchRulesContent());
        }

        return $passDocument;
    }

    /**
     * @param bool $notCached
     * @return string
     */
    protected function getPassContent(bool $notCached = false): string
    {
        if ($notCached) {
            $this->removeOwnerShipConfirmation();

            return $this->fetchRulesContent();
        }
        static $passContent;
        if ($passContent === null) {
            $this->removeOwnerShipConfirmation();
            $passContent = $this->fetchRulesContent();
        }

        return $passContent;
    }

    private function fetchRulesContent(): string
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $latestVersion = $this->getTestsConfiguration()->getExpectedLastUnstableVersion();
        \ob_start();
        /** @noinspection PhpIncludeInspection */
        include DRD_PLUS_INDEX_FILE_NAME_TO_TEST;

        return \ob_get_clean();
    }

    private function getNameForLocalOwnershipConfirmation(): string
    {
        static $cookieName;
        if ($cookieName === null) {
            $cookieName = $this->getNameForOwnershipConfirmation();
        }

        return $cookieName;
    }

    protected function getNameForOwnershipConfirmation(): string
    {
        $usagePolicy = new UsagePolicy($this->getVariablePartOfNameForPass(), new Request(new Bot()), new CookiesService());
        try {
            $usagePolicyReflection = new \ReflectionClass(UsagePolicy::class);
        } catch (\ReflectionException $reflectionException) {
            self::fail($reflectionException->getMessage());
            exit;
        }
        $getName = $usagePolicyReflection->getMethod('getOwnershipName');
        $getName->setAccessible(true);

        return $getName->invoke($usagePolicy);
    }

    protected function getVariablePartOfNameForPass(): string
    {
        return StringTools::toVariableName($this->getTestsConfiguration()->getExpectedWebName());
    }

    private function getDirName(string $fileName): string
    {
        $dirName = $fileName;
        $upLevels = 0;
        while (\basename($dirName) === '.' || \basename($dirName) === '..' || !\is_dir($dirName)) {
            if (\basename($dirName) === '..') {
                $upLevels++;
            }
            $dirName = \dirname($dirName);
            if ($dirName === '/') {
                throw new \RuntimeException("Could not find name of dir by $fileName");
            }
        }
        for ($upLevel = 1; $upLevel <= $upLevels; $upLevel++) {
            $dirName = $this->getDirName(\dirname($dirName) /* up by a single level */);
        }

        return $dirName;
    }

    private function removeOwnerShipConfirmation(): void
    {
        unset($_COOKIE[$this->getNameForLocalOwnershipConfirmation()]);
    }

    /**
     * @param string $show = ''
     * @param string $hide = ''
     * @return string
     */
    protected function getRulesContentForDev(string $show = '', string $hide = ''): string
    {
        if (empty(self::$rulesContentForDev[$show][$hide])) {
            $originalGet = $_GET;
            $this->passIn();
            $_GET['mode'] = 'dev';
            if ($show !== '') {
                $_GET['show'] = $show;
            }
            if ($hide !== '') {
                $_GET['hide'] = $hide;
            }
            \ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_INDEX_FILE_NAME_TO_TEST;
            self::$rulesContentForDev[$show][$hide] = \ob_get_clean();
            $_GET = $originalGet;
            self::assertNotSame($this->getPassContent(), self::$rulesContentForDev[$show]);
        }

        return self::$rulesContentForDev[$show][$hide];
    }

    protected function getRulesForDevHtmlDocument(string $show = '', string $hide = ''): HTMLDocument
    {
        if (empty(self::$rulesForDevHtmlDocument[$show][$hide])) {
            self::$rulesForDevHtmlDocument[$show][$hide] = new HTMLDocument($this->getRulesContentForDev($show, $hide));
        }

        return self::$rulesForDevHtmlDocument[$show][$hide];
    }

    /**
     * @return string
     */
    protected function getRulesContentForDevWithHiddenCovered(): string
    {
        return $this->getRulesContentForDev('', 'covered');
    }

    protected function getEshopFileName(): string
    {
        return $this->getDocumentRoot() . '/eshop_url.txt';
    }

    protected function getGenericPartsRoot(): string
    {
        return \file_exists($this->getDocumentRoot() . '/parts/rules-skeleton')
            ? $this->getDocumentRoot() . '/parts/rules-skeleton'
            : $this->getVendorRoot() . '/drd-plus/rules-skeleton/parts/rules-skeleton';
    }

    protected function getVendorRoot(): string
    {
        return $this->getDocumentRoot() . '/vendor';
    }

    /**
     * @param Dirs $dirs
     * @param bool $inDevMode
     * @param bool $inForcedProductionMode
     * @param bool $shouldHideCovered
     * @param bool $showIntroductionOnly
     * @return HtmlHelper|\Mockery\MockInterface
     */
    protected function createHtmlHelper(
        Dirs $dirs = null,
        bool $inForcedProductionMode = false,
        bool $inDevMode = false,
        bool $shouldHideCovered = false,
        bool $showIntroductionOnly = false
    ): \DrdPlus\FrontendSkeleton\HtmlHelper
    {
        return new HtmlHelper($dirs ?? $this->createDirs(), $inDevMode, $inForcedProductionMode, $shouldHideCovered, $showIntroductionOnly);
    }
}