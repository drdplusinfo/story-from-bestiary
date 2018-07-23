<?php
namespace DrdPlus\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\FrontendSkeleton\CookiesService;
use DrdPlus\FrontendSkeleton\PageCache;
use Granam\String\StringTools;

/**
 * @method Dirs getDirs(): Dirs
 */
class RulesController extends \DrdPlus\FrontendSkeleton\FrontendController
{

    /** @var CookiesService */
    private $cookiesService;
    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var Request */
    private $rulesSkeletonRequest;
    /** @var string */
    private $eshopUrl;
    /** @var bool */
    private $freeAccess = false;

    public function __construct(string $googleAnalyticsId, HtmlHelper $htmlHelper, Dirs $dirs, array $bodyClasses = [])
    {
        parent::__construct($googleAnalyticsId, $htmlHelper, $dirs, $bodyClasses);
    }

    public function getPageCache(): PageCache
    {
        if ($this->pageCache === null) {
            $this->pageCache = new PageCache(
                $this->getWebVersions(),
                $this->getDirs(),
                $this->getHtmlHelper()->isInProduction(),
                \basename($this->getDirs()->getWebRoot()) // can vary in relation to pass
            );
        }

        return $this->pageCache;
    }

    public function setFreeAccess(): RulesController
    {
        $this->freeAccess = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFreeAccess(): bool
    {
        return $this->freeAccess;
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

    public function getCookiesService(): CookiesService
    {
        if ($this->cookiesService === null) {
            $this->cookiesService = new CookiesService();
        }

        return $this->cookiesService;
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

    /**
     * @return string
     */
    public function getEshopUrl(): string
    {
        if ($this->eshopUrl === null) {
            $eshopUrl = \trim(\file_get_contents($this->getDirs()->getDocumentRoot() . '/eshop_url.txt'));
            if (!\filter_var($eshopUrl, FILTER_VALIDATE_URL)) {
                throw new Exceptions\InvalidEshopUrl("Given e-shop URL from 'eshop_url.txt' is not valid: '$eshopUrl'");
            }
            $this->eshopUrl = $eshopUrl;
        }

        return $this->eshopUrl;
    }

    public function activateTrial(\DateTime $trialExpiration): bool
    {
        $visitorCanAccessContent = $this->getUsagePolicy()->activateTrial($trialExpiration);
        if ($visitorCanAccessContent) {
            $this->setRedirect(
                new \DrdPlus\FrontendSkeleton\Redirect(
                    "/?{$this->getUsagePolicy()->getTrialExpiredAtName()}={$trialExpiration->getTimestamp()}",
                    $trialExpiration->getTimestamp() - \time()
                )
            );
        }

        return $visitorCanAccessContent;
    }
}