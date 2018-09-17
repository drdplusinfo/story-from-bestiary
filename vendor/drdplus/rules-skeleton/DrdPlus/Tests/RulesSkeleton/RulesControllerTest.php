<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\HtmlDocument;
use DrdPlus\RulesSkeleton\Configuration;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Redirect;
use DrdPlus\RulesSkeleton\RulesController;
use DrdPlus\RulesSkeleton\ServicesContainer;
use DrdPlus\RulesSkeleton\UsagePolicy;
use Mockery\MockInterface;

class RulesControllerTest extends \DrdPlus\Tests\FrontendSkeleton\FrontendControllerTest
{
    use Partials\AbstractContentTestTrait;

    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_activate_trial(): void
    {
        $now = new \DateTime();
        $trialExpectedExpiration = (clone $now)->modify('+4 minutes');
        $usagePolicy = $this->createUsagePolicy($trialExpectedExpiration);
        $controller = new RulesController($this->createServicesContainerWithUsagePolicy($usagePolicy));
        $controllerReflection = new \ReflectionClass($controller);
        $activateTrial = $controllerReflection->getMethod('activateTrial');
        $activateTrial->setAccessible(true);
        self::assertTrue($activateTrial->invoke($controller, $now));
        $getRedirect = $controllerReflection->getMethod('getRedirect');
        $getRedirect->setAccessible(true);
        $redirect = $getRedirect->invoke($controller);
        self::assertNotNull($redirect);
        $trialExpectedExpirationTimestamp = $trialExpectedExpiration->getTimestamp() + 1; // one second "insurance" overlap
        self::assertSame('/?bar=' . $trialExpectedExpirationTimestamp, $redirect->getTarget());
        self::assertSame($trialExpectedExpirationTimestamp - $now->getTimestamp(), $redirect->getAfterSeconds());
    }

    /**
     * @param \DateTime $trialExpectedExpiration
     * @return UsagePolicy|MockInterface
     */
    private function createUsagePolicy(\DateTime $trialExpectedExpiration): UsagePolicy
    {
        $usagePolicy = $this->mockery(UsagePolicy::class);
        $usagePolicy->expects('getTrialExpiredAtName')
            ->atLeast()->once()
            ->andReturn('bar');
        $usagePolicy->expects('activateTrial')
            ->with($this->type(\DateTime::class))
            ->andReturnUsing(function (\DateTime $expiresAt) use ($trialExpectedExpiration) {
                self::assertEquals($trialExpectedExpiration, $expiresAt);

                return true;
            });

        return $usagePolicy;
    }

    private function createServicesContainerWithUsagePolicy(UsagePolicy $usagePolicy)
    {
        $configuration = $this->getConfiguration();
        $htmlHelper = $this->createHtmlHelper();

        return new class($usagePolicy, $configuration, $htmlHelper) extends ServicesContainer
        {
            /** @var UsagePolicy */
            private $usagePolicy;

            public function __construct(usagePolicy $usagePolicy, Configuration $configuration, HtmlHelper $htmlHelper)
            {
                $this->usagePolicy = $usagePolicy;
                parent::__construct($configuration, $htmlHelper);
            }

            public function getUsagePolicy(): UsagePolicy
            {
                return $this->usagePolicy;
            }

        };
    }

    /**
     * @test
     * @backupGlobals enabled
     * @throws \ReflectionException
     */
    public function I_will_be_redirected_via_html_meta_on_trial(): void
    {
        self::assertCount(0, $this->getMetaRefreshes($this->getHtmlDocument()), 'No meta tag with refresh meaning expected so far');
        $this->passOut();
        $controller = null;
        $now = \time();
        $trialExpiredAt = $now + 240 + 1;
        $trialExpiredAtSecondAfter = $trialExpiredAt++;
        if ($this->isSkeletonChecked() || $this->getTestsConfiguration()->hasProtectedAccess()) {
            $_POST['trial'] = 1; // can be solved by POST
        } else { // just a little hack
            $controller = $this->createController();
            $controllerReflection = new \ReflectionClass($controller);
            $setRedirect = $controllerReflection->getMethod('setRedirect');
            $setRedirect->setAccessible(true);
            $setRedirect->invoke($controller, new Redirect('/?' . UsagePolicy::TRIAL_EXPIRED_AT . '=' . $trialExpiredAt, 241));
        }
        $trialContent = $this->fetchNonCachedContent($controller);
        $document = new HtmlDocument($trialContent);
        $metaRefreshes = $this->getMetaRefreshes($document);
        self::assertCount(1, $metaRefreshes, 'One meta tag with refresh meaning expected');
        $metaRefresh = \current($metaRefreshes);
        self::assertRegExp(
            '~241; url=/[?]' . UsagePolicy::TRIAL_EXPIRED_AT . "=($trialExpiredAt|$trialExpiredAtSecondAfter)~",
            $metaRefresh->getAttribute('content')
        );
    }
}