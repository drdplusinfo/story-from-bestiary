<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Partials;

use DeviceDetector\Parser\Bot;
use DrdPlus\RulesSkeleton\Configuration;
use DrdPlus\RulesSkeleton\CookiesService;
use DrdPlus\RulesSkeleton\Dirs;
use DrdPlus\RulesSkeleton\Git;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\RulesController;
use DrdPlus\RulesSkeleton\ServicesContainer;
use DrdPlus\RulesSkeleton\UsagePolicy;
use Granam\String\StringTools;
use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;
use Mockery\MockInterface;

abstract class AbstractContentTest extends SkeletonTestCase
{
    use ClassesTrait;

    private static $contents = [];
    private static $htmlDocuments = [];
    private static $rulesContentForDev = [];
    private static $rulesForDevHtmlDocument = [];
    private static $rulesSkeletonChecked;
    protected $needPassIn = true;
    protected $needPassOut = false;
    /** @var Configuration */
    private $configuration;
    private $frontendSkeletonChecked;

    protected function setUp(): void
    {
        if (!\defined('DRD_PLUS_INDEX_FILE_NAME_TO_TEST')) {
            self::markTestSkipped("Missing constant 'DRD_PLUS_INDEX_FILE_NAME_TO_TEST'");
        }
        if ($this->getTestsConfiguration()->hasProtectedAccess()) {
            $this->passIn();
        }
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
     * @param array $get = []
     * @param array $post = []
     * @param array $cookies = []
     * @return string
     */
    protected function getContent(array $get = [], array $post = [], array $cookies = []): string
    {
        $key = $this->createKey($get, $post, $cookies);
        if ((self::$contents[$key] ?? null) === null) {
            $originalGet = $_GET;
            $originalPost = $_POST;
            $originalCookies = $_COOKIE;
            if ($get) {
                $_GET = \array_merge($_GET, $get);
            }
            if ($post) {
                $_POST = \array_merge($_POST, $post);
            }
            if ($cookies) {
                $_COOKIE = \array_merge($_COOKIE, $cookies);
            }
            if (empty($_GET[Request::VERSION]) && empty($_COOKIE[CookiesService::VERSION])) {
                $_GET[Request::VERSION] = $this->getTestsConfiguration()->getExpectedLastUnstableVersion();
            }
            if ($this->needPassIn()) {
                $this->passIn();
            } elseif ($this->needPassOut()) {
                $this->passOut();
            }
            \ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_INDEX_FILE_NAME_TO_TEST;
            self::$contents[$key] = \ob_get_clean();
            $_POST = $originalPost;
            $_GET = $originalGet;
            $_COOKIE = $originalCookies;
            self::assertNotEmpty(
                self::$contents[$key],
                'Nothing has been fetched with GET ' . \var_export($get, true) . ', POST ' . \var_export($post, true)
                . ' and COOKIE ' . \var_export($cookies, true)
                . ' from ' . DRD_PLUS_INDEX_FILE_NAME_TO_TEST
            );
        }

        return self::$contents[$key];
    }

    protected function createKey(array $get, array $post, array $cookies): string
    {
        return \json_encode($get) . '-' . \json_encode($post) . '-' . \json_encode($cookies) . '-' . (int)$this->needPassIn() . (int)$this->needPassOut();
    }

    protected function needPassIn(): bool
    {
        return $this->needPassIn;
    }

    protected function needPassOut(): bool
    {
        return $this->needPassOut;
    }

    /**
     * @param array $get
     * @param array $post
     * @param array $cookies
     * @return \DrdPlus\RulesSkeleton\HtmlDocument
     */
    protected function getHtmlDocument(array $get = [], array $post = [], array $cookies = []): \DrdPlus\RulesSkeleton\HtmlDocument
    {
        $key = $this->createKey($get, $post, $cookies);
        if (empty(self::$htmlDocuments[$key])) {
            self::$htmlDocuments[$key] = new \DrdPlus\RulesSkeleton\HtmlDocument($this->getContent($get, $post, $cookies));
        }

        return self::$htmlDocuments[$key];
    }

    protected function getCurrentPageTitle(HTMLDocument $document = null): string
    {
        $head = ($document ?? $this->getHtmlDocument())->head;
        if (!$head) {
            return '';
        }
        $titles = $head->getElementsByTagName('title');
        if ($titles->count() === 0) {
            return '';
        }
        $titles->rewind();

        return $titles->current()->nodeValue;
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
    ): HtmlHelper
    {
        return new HtmlHelper($dirs ?? $this->createDirs(), $inDevMode, $inForcedProductionMode, $shouldHideCovered);
    }

    protected function fetchNonCachedContent(RulesController $controller = null, bool $backupGlobals = true): string
    {
        $originalGet = $_GET;
        $originalPost = $_POST;
        $originalCookies = $_COOKIE;
        /** @noinspection PhpUnusedLocalVariableInspection */
        $controller = $controller ?? null;
        $_GET[Request::CACHE] = Request::DISABLE;
        \ob_start();
        /** @noinspection PhpIncludeInspection */
        include $this->getDocumentRoot() . '/index.php';
        $content = \ob_get_clean();
        if ($backupGlobals) {
            $_GET = $originalGet;
            $_POST = $originalPost;
            $_COOKIE = $originalCookies;
        }

        return $content;
    }

    protected function fetchContentFromLink(string $link, bool $withBody, array $post = [], array $cookies = [], array $headers = []): array
    {
        $curl = \curl_init($link);
        \curl_setopt($curl, \CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($curl, \CURLOPT_CONNECTTIMEOUT, 7);
        if (!$withBody) {
            // to get headers only
            \curl_setopt($curl, \CURLOPT_HEADER, 1);
            \curl_setopt($curl, \CURLOPT_NOBODY, 1);
        }
        \curl_setopt($curl, \CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:58.0) Gecko/20100101 Firefox/58.0'); // to get headers only
        if ($post) {
            \curl_setopt($curl, \CURLOPT_POSTFIELDS, $post);
        }
        if ($cookies) {
            $cookieData = [];
            foreach ($cookies as $name => $value) {
                $cookieData[] = "$name=$value";
            }
            \curl_setopt($curl, \CURLOPT_COOKIE, \implode('; ', $cookieData));
        }
        foreach ($headers as $headerName => $headerValue) {
            \curl_setopt($curl, \CURLOPT_HEADER, "$headerName=$headerValue");
        }
        $content = \curl_exec($curl);
        $responseHttpCode = \curl_getinfo($curl, \CURLINFO_HTTP_CODE);
        $redirectUrl = \curl_getinfo($curl, \CURLINFO_REDIRECT_URL);
        $curlError = \curl_error($curl);
        \curl_close($curl);

        return [
            'responseHttpCode' => $responseHttpCode,
            'redirectUrl' => $redirectUrl,
            'content' => $content,
            'error' => $curlError,
        ];
    }

    protected function runCommand(string $command): array
    {
        \exec("$command 2>&1", $output, $returnCode);
        self::assertSame(0, $returnCode, "Failed command '$command', got output " . \var_export($output, true));

        return $output;
    }

    protected function executeCommand(string $command): string
    {
        $output = $this->runCommand($command);

        return \end($output) ?: '';
    }

    /**
     * @param HtmlDocument $document
     * @return array|Element[]
     */
    protected function getMetaRefreshes(HtmlDocument $document): array
    {
        $metaElements = $document->head->getElementsByTagName('meta');
        $metaRefreshes = [];
        foreach ($metaElements as $metaElement) {
            if ($metaElement->getAttribute('http-equiv') === 'Refresh') {
                $metaRefreshes[] = $metaElement;
            }
        }

        return $metaRefreshes;
    }

    protected function getGitFolderIgnoring(string $dirToCheck): array
    {
        $documentRootEscaped = \escapeshellarg($this->getDocumentRoot());
        $dirToCheckEscaped = \escapeshellarg($dirToCheck);
        $command = "git -C $documentRootEscaped check-ignore $dirToCheckEscaped 2>&1";
        \exec($command, $output, $result);
        if ($result > 1) { // both 0 and 1 are valid success return codes
            throw new \RuntimeException(
                "Can not find out if is vendor dir versioned or not by command '{$command}'"
                . ", got return code '{$result}' and output\n"
                . \implode("\n", $output)
            );
        }

        return ['output' => $output, 'result' => $result];
    }

    protected function getConfiguration(Dirs $dirs = null): Configuration
    {
        if ($this->configuration === null) {
            $configurationClass = $this->getConfigurationClass();
            $this->configuration = $configurationClass::createFromYml($dirs ?? $this->createDirs());
        }

        return $this->configuration;
    }

    protected function createRequest(string $currentVersion = null): Request
    {
        $request = $this->mockery($this->getRequestClass());
        $request->allows('getValue')
            ->with(Request::VERSION)
            ->andReturn($currentVersion);
        $request->makePartial();

        /** @var Request $request */
        return $request;
    }

    protected function createGit(): Git
    {
        return new Git();
    }

    /**
     * @param array $customSettings
     * @return Configuration|MockInterface
     */
    protected function createCustomConfiguration(array $customSettings): Configuration
    {
        $originalConfiguration = $this->getConfiguration();
        $configurationClass = \get_class($originalConfiguration);
        $customConfiguration = new $configurationClass(
            $originalConfiguration->getDirs(),
            \array_replace_recursive($originalConfiguration->getSettings(), $customSettings)
        );

        return $customConfiguration;
    }

    protected function createController(
        string $documentRoot = null,
        Configuration $configuration = null,
        HtmlHelper $htmlHelper = null
    ): RulesController
    {
        $controllerClass = $this->getControllerClass();

        return new $controllerClass($this->createServicesContainer($documentRoot, $configuration, $htmlHelper));
    }

    protected function createServicesContainer(
        string $documentRoot = null,
        Configuration $configuration = null,
        HtmlHelper $htmlHelper = null
    ): ServicesContainer
    {
        $dirs = $this->createDirs($documentRoot);

        return new ServicesContainer(
            $configuration ?? $this->getConfiguration(),
            $htmlHelper ?? $this->createHtmlHelper($dirs, false, false, false)
        );
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
        if (self::$rulesSkeletonChecked === null) {
            $documentRootRealPath = \realpath($this->getDocumentRoot());
            self::assertNotEmpty($documentRootRealPath, 'Can not find out real path of document root ' . \var_export($this->getDocumentRoot(), true));
            $skeletonRootRealPath = \realpath($skeletonDocumentRoot ?? __DIR__ . '/../../../..');
            self::assertNotEmpty($skeletonRootRealPath, 'Can not find out real path of skeleton root ' . \var_export($skeletonRootRealPath, true));
            self::assertSame('rules.skeleton', \basename($skeletonRootRealPath), 'Expected different trailing dir of skeleton document root');

            self::$rulesSkeletonChecked = $documentRootRealPath === $skeletonRootRealPath;
        }

        return self::$rulesSkeletonChecked;
    }

    protected function getPassDocument(bool $notCached = false): \DrdPlus\RulesSkeleton\HtmlDocument
    {
        if ($notCached) {
            return new \DrdPlus\RulesSkeleton\HtmlDocument($this->getPassContent($notCached));
        }
        static $passDocument;
        if ($passDocument === null) {
            $this->removeOwnerShipConfirmation();
            $passDocument = new \DrdPlus\RulesSkeleton\HtmlDocument($this->getPassContent($notCached));
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

    protected function createDirs(string $documentRoot = null): Dirs
    {
        return new Dirs($documentRoot ?? $this->getDocumentRoot());
    }

    protected function getDocumentRoot(): string
    {
        static $masterDocumentRoot;
        if ($masterDocumentRoot === null) {
            $masterDocumentRoot = \dirname(\DRD_PLUS_INDEX_FILE_NAME_TO_TEST);
        }

        return $masterDocumentRoot;
    }

    protected function getDirForVersions(): string
    {
        return $this->getDocumentRoot() . '/versions';
    }

    protected function getVendorRoot(): string
    {
        return $this->getDocumentRoot() . '/vendor';
    }

    protected function unifyPath(string $path): string
    {
        $path = \str_replace('\\', '/', $path);
        $path = \preg_replace('~/\.(?:/|$)~', '/', $path);

        return $this->squashTwoDots($path);
    }

    private function squashTwoDots(string $path): string
    {
        $originalPath = $path;
        $path = \preg_replace('~/[^/.]+/\.\.~', '', $path);
        if ($originalPath === $path) {
            return $originalPath; // nothing has been squashed
        }

        return $this->squashTwoDots($path);
    }

    protected function getSkeletonDocumentRoot(): string
    {
        if ($this->isSkeletonChecked()) {
            return $this->getDocumentRoot();
        }

        return $this->createDirs()->getVendorRoot() . '/drdplus/rules-skeleton';
    }
}