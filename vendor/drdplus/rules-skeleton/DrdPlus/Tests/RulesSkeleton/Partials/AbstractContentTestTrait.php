<?php
namespace DrdPlus\Tests\RulesSkeleton\Partials;

use DeviceDetector\Parser\Bot;
use DrdPlus\FrontendSkeleton\CookiesService;
use DrdPlus\FrontendSkeleton\FrontendController;
use DrdPlus\RulesSkeleton\Configuration;
use DrdPlus\FrontendSkeleton\Dirs;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\RulesController;
use DrdPlus\RulesSkeleton\ServicesContainer;
use DrdPlus\RulesSkeleton\UsagePolicy;
use Granam\String\StringTools;
use Gt\Dom\HTMLDocument;

/**
 * @method string protected function getContent(array $get = [], array $post = [], array $cookies = [])
 * @method string fetchNonCachedContent(FrontendController $controller = null, bool $backupGlobals = true)
 * @method string getDocumentRoot
 * @method TestsConfigurationReader getTestsConfiguration
 * @method static assertTrue($value, $message = '')
 * @method static assertFalse($value, $message = '')
 * @method static assertNotSame($expected, $actual, $message = '')
 * @method static fail($message)
 * @method RulesController createController(string $documentRoot = null, Configuration $configuration = null, HtmlHelper $htmlHelper = null)
 * @method Configuration getConfiguration(Dirs $dirs = null)
 */
trait AbstractContentTestTrait
{

    use DirsForTestsTrait;
    use ClassesTrait;

    private static $rulesContentForDev = [];
    private static $rulesForDevHtmlDocument = [];
    private static $rulesSkeletonChecked;

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
        if (static::$rulesSkeletonChecked === null) {
            $documentRootRealPath = \realpath($this->getDocumentRoot());
            self::assertNotEmpty($documentRootRealPath, 'Can not find out real path of document root ' . \var_export($this->getDocumentRoot(), true));
            $skeletonRootRealPath = \realpath($skeletonDocumentRoot ?? __DIR__ . '/../../../..');
            self::assertNotEmpty($skeletonRootRealPath, 'Can not find out real path of skeleton root ' . \var_export($skeletonRootRealPath, true));
            self::assertSame('rules-skeleton', \basename($skeletonRootRealPath), 'Expected different trailing dir of skeleton document root');

            static::$rulesSkeletonChecked = $documentRootRealPath === $skeletonRootRealPath;
        }

        return static::$rulesSkeletonChecked;
    }

    protected function getPassDocument(bool $notCached = false): \DrdPlus\FrontendSkeleton\HtmlDocument
    {
        if ($notCached) {
            return new \DrdPlus\FrontendSkeleton\HtmlDocument($this->getPassContent($notCached));
        }
        static $passDocument;
        if ($passDocument === null) {
            $this->removeOwnerShipConfirmation();
            $passDocument = new \DrdPlus\FrontendSkeleton\HtmlDocument($this->getPassContent($notCached));
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

            return $this->fetchNonCachedContent();
        }
        static $passContent;
        if ($passContent === null) {
            $this->removeOwnerShipConfirmation();
            $passContent = $this->fetchNonCachedContent();
        }

        return $passContent;
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
            $get['mode'] = 'dev';
            if ($show !== '') {
                $get['show'] = $show;
            }
            if ($hide !== '') {
                $get['hide'] = $hide;
            }
            $content = $this->getContent($get);
            self::$rulesContentForDev[$show][$hide] = $content;
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

    /**
     * @param Dirs $dirs
     * @param bool $inDevMode
     * @param bool $inForcedProductionMode
     * @param bool $shouldHideCovered
     * @return HtmlHelper|\Mockery\MockInterface
     */
    protected function createHtmlHelper(
        Dirs $dirs = null,
        bool $inForcedProductionMode = false,
        bool $inDevMode = false,
        bool $shouldHideCovered = false
    ): \DrdPlus\FrontendSkeleton\HtmlHelper
    {
        return new HtmlHelper($dirs ?? $this->createDirs(), $inDevMode, $inForcedProductionMode, $shouldHideCovered);
    }

    /**
     * @param string|null $documentRoot
     * @param \DrdPlus\FrontendSkeleton\Configuration|null $configuration
     * @param \DrdPlus\FrontendSkeleton\HtmlHelper|null $htmlHelper
     * @return \DrdPlus\FrontendSkeleton\ServicesContainer|ServicesContainer
     */
    protected function createServicesContainer(
        string $documentRoot = null,
        \DrdPlus\FrontendSkeleton\Configuration $configuration = null,
        \DrdPlus\FrontendSkeleton\HtmlHelper $htmlHelper = null
    ): \DrdPlus\FrontendSkeleton\ServicesContainer
    {
        $dirs = $this->createDirs($documentRoot);

        return new ServicesContainer(
            $configuration ?? $this->getConfiguration(),
            $htmlHelper ?? $this->createHtmlHelper($dirs, false, false, false, false)
        );
    }

    /**
     * @return string|Configuration
     */
    protected function getConfigurationClass(): string
    {
        return Configuration::class;
    }

    protected function getControllerClass(): string
    {
        return RulesController::class;
    }

}