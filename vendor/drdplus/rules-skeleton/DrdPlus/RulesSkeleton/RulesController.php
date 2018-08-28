<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\FrontendSkeleton\HtmlHelper;
use DrdPlus\FrontendSkeleton\PageCache;
use Granam\String\StringTools;

/**
 * @method Configuration getConfiguration() : Configuration
 * @method HtmlHelper getHtmlHelper() : HtmlHelper
 */
class RulesController extends \DrdPlus\FrontendSkeleton\FrontendController
{
    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var Request */
    private $rulesSkeletonRequest;

    public function __construct(Configuration $configuration, HtmlHelper $htmlHelper, array $bodyClasses = [])
    {
        parent::__construct($configuration, $htmlHelper, $bodyClasses);
    }

    public function getPageCache(): PageCache
    {
        if ($this->pageCache === null) {
            $this->pageCache = new PageCache(
                $this->getWebVersions(),
                $this->getConfiguration()->getDirs(),
                $this->getHtmlHelper()->isInProduction(),
                $this->isAccessAllowed()
                    ? 'passed'
                    : 'pass'
            );
        }

        return $this->pageCache;
    }

    public function getUsagePolicy(): UsagePolicy
    {
        if ($this->usagePolicy === null) {
            $this->usagePolicy = new UsagePolicy(
                StringTools::toVariableName($this->getWebName()),
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
        if ($this->rulesSkeletonRequest === null) {
            $this->rulesSkeletonRequest = new Request(new Bot());
        }

        return $this->rulesSkeletonRequest;
    }

    public function activateTrial(\DateTime $now): bool
    {
        $trialExpiration = (clone $now)->modify('+4 minutes');
        $visitorCanAccessContent = $this->getUsagePolicy()->activateTrial($trialExpiration);
        if ($visitorCanAccessContent) {
            $at = $trialExpiration->getTimestamp() + 1; // one second "insurance" overlap
            $afterSeconds = $at - $now->getTimestamp();
            $this->setRedirect(
                new \DrdPlus\FrontendSkeleton\Redirect(
                    "/?{$this->getUsagePolicy()->getTrialExpiredAtName()}={$at}",
                    $afterSeconds
                )
            );
        }

        return $visitorCanAccessContent;
    }

    public function isAccessAllowed(): bool
    {
        return $this->getConfiguration()->getDirs()->isAllowedAccessToWebFiles();
    }

    public function allowAccess(): RulesController
    {
        $this->getConfiguration()->getDirs()->allowAccessToWebFiles();

        return $this;
    }
}