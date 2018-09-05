<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\FrontendSkeleton\WebCache;
use DrdPlus\RulesSkeleton\Web\EmptyHead;
use DrdPlus\RulesSkeleton\Web\EmptyMenu;
use DrdPlus\RulesSkeleton\Web\HeadForTables;
use DrdPlus\RulesSkeleton\Web\Pass;
use DrdPlus\RulesSkeleton\Web\PassBody;
use DrdPlus\RulesSkeleton\Web\PdfBody;
use DrdPlus\RulesSkeleton\Web\TablesBody;
use Granam\String\StringTools;

/**
 * @method Configuration getConfiguration()
 * @method HtmlHelper getHtmlHelper()
 * @method Dirs getDirs()
 */
class ServicesContainer extends \DrdPlus\FrontendSkeleton\ServicesContainer
{
    /** @var TablesWebCache */
    private $tablesWebCache;
    /** @var TablesBody */
    private $tablesBody;
    /** @var WebCache */
    private $passWebCache;
    /** @var WebCache */
    private $passedWebCache;
    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var Pass */
    private $pass;
    /** @var PassBody */
    private $passBody;
    /** @var PdfBody */
    private $pdfBody;

    public function __construct(Configuration $configuration, HtmlHelper $htmlHelper)
    {
        parent::__construct($configuration, $htmlHelper);
    }

    public function getHeadForTables(): HeadForTables
    {
        return new HeadForTables(
            $this->getConfiguration(),
            $this->getHtmlHelper(),
            $this->getCssFiles(),
            $this->getJsFiles()
        );
    }

    public function getTablesWebCache(): TablesWebCache
    {
        if ($this->tablesWebCache === null) {
            $this->tablesWebCache = new TablesWebCache(
                $this->getWebVersions(),
                $this->getConfiguration()->getDirs(),
                $this->getHtmlHelper()->isInProduction(),
                'pass'
            );
        }

        return $this->tablesWebCache;
    }

    public function getTablesBody(): TablesBody
    {
        if ($this->tablesBody === null) {
            $this->tablesBody = new TablesBody($this->getWebFiles(), $this->getHtmlHelper(), $this->getRequest());
        }

        return $this->tablesBody;
    }

    public function getPassWebCache(): WebCache
    {
        if ($this->passWebCache === null) {
            $this->passWebCache = new WebCache(
                $this->getWebVersions(),
                $this->getConfiguration()->getDirs(),
                $this->getHtmlHelper()->isInProduction(),
                'pass'
            );
        }

        return $this->passWebCache;
    }

    public function getPassedWebCache(): WebCache
    {
        if ($this->passedWebCache === null) {
            $this->passedWebCache = new WebCache(
                $this->getWebVersions(),
                $this->getConfiguration()->getDirs(),
                $this->getHtmlHelper()->isInProduction(),
                'passed'
            );
        }

        return $this->passedWebCache;
    }

    /**
     * @return PassBody
     */
    public function getPassBody(): PassBody
    {
        if ($this->passBody === null) {
            $this->passBody = new PassBody($this->getWebFiles(), $this->getPass());
        }

        return $this->passBody;
    }

    public function getPass(): Pass
    {
        if ($this->pass === null) {
            $this->pass = new Pass($this->getConfiguration(), $this->getUsagePolicy());
        }

        return $this->pass;
    }

    public function getUsagePolicy(): UsagePolicy
    {
        if ($this->usagePolicy === null) {
            $this->usagePolicy = new UsagePolicy(
                StringTools::toVariableName($this->getConfiguration()->getWebName()),
                $this->getRequest(),
                $this->getCookiesService()
            );
        }

        return $this->usagePolicy;
    }

    /**
     * @return \DrdPlus\FrontendSkeleton\Request|Request
     */
    public function getRequest(): \DrdPlus\FrontendSkeleton\Request
    {
        if ($this->request === null) {
            $this->request = new Request(new Bot());
        }

        return $this->request;
    }

    public function getPdfBody(): PdfBody
    {
        if ($this->pdfBody === null) {
            $this->pdfBody = new PdfBody($this->getWebFiles(), $this->getDirs());
        }

        return $this->pdfBody;
    }

    public function getEmptyHead(): EmptyHead
    {
        return new EmptyHead($this->getConfiguration(), $this->getHtmlHelper(), $this->getCssFiles(), $this->getJsFiles());
    }

    public function getEmptyMenu(): EmptyMenu
    {
        return new EmptyMenu($this->getConfiguration(), $this->getWebVersions(), $this->getRequest());
    }

    public function getEmptyWebCache(): EmptyWebCache
    {
        return new EmptyWebCache($this->getWebVersions(), $this->getDirs(), $this->getHtmlHelper()->isInProduction(), 'empty');
    }

    public function getNow(): \DateTime
    {
        return new \DateTime();
    }

}