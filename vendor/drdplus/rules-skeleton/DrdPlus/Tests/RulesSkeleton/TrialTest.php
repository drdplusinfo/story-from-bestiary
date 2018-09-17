<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\HtmlDocument;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Redirect;
use DrdPlus\RulesSkeleton\RulesController;
use DrdPlus\Tests\FrontendSkeleton\Partials\AbstractContentTest;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTestTrait;
use Gt\Dom\Element;

class TrialTest extends AbstractContentTest
{
    use AbstractContentTestTrait;

    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_will_get_cached_content_with_injected_trial_timeout(): void
    {
        $controller = $this->createController();
        $cacheStamp = $this->There_is_no_meta_redirect_if_licence_owning_has_been_confirmed($controller);
        $this->There_is_meta_redirect_in_passing_by_trial($controller, $cacheStamp);
    }

    private function There_is_no_meta_redirect_if_licence_owning_has_been_confirmed(RulesController $controller): string
    {
        $content = $controller->getContent()->getStringContent();
        $firstWithoutRedirect = new HtmlDocument($content);
        self::assertNull($firstWithoutRedirect->getElementById(HtmlHelper::META_REDIRECT_ID));
        $cacheStamp = $firstWithoutRedirect->documentElement->getAttribute(HtmlHelper::DATA_CACHE_STAMP);
        self::assertNotEmpty($cacheStamp);

        return $cacheStamp;
    }

    /**
     * @param RulesController $controller
     * @param string $previousCacheStamp
     * @throws \ReflectionException
     */
    private function There_is_meta_redirect_in_passing_by_trial(RulesController $controller, string $previousCacheStamp): void
    {
        $controllerReflection = new \ReflectionClass($controller);
        $setRedirect = $controllerReflection->getMethod('setRedirect');
        $setRedirect->setAccessible(true);
        $setRedirect->invoke($controller, new Redirect('/foo', 12345));
        $content = $controller->getContent()->getStringContent();
        $firstWithRedirect = new HtmlDocument($content);
        self::assertSame(
            $previousCacheStamp,
            $firstWithRedirect->documentElement->getAttribute(HtmlHelper::DATA_CACHE_STAMP),
            'Expected content from same cache'
        );
        /** @var Element $redirectElement */
        $redirectElement = $firstWithRedirect->getElementById(HtmlHelper::META_REDIRECT_ID);
        self::assertNotNull($redirectElement, 'Missing expected element with ID "' . HtmlHelper::META_REDIRECT_ID . '"');
        self::assertSame('Refresh', $redirectElement->getAttribute('http-equiv'));
        self::assertSame('12345; url=/foo', $redirectElement->getAttribute('content'));
    }
}