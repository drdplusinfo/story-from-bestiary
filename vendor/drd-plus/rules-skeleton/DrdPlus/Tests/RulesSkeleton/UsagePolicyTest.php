<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\FrontendSkeleton\CookiesService;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\UsagePolicy;
use PHPUnit\Framework\TestCase;

class UsagePolicyTest extends TestCase
{
    /**
     * @test
     * @expectedException \DrdPlus\RulesSkeleton\Exceptions\ArticleNameCanNotBeEmptyForUsagePolicy
     */
    public function I_can_not_create_it_without_article_name(): void
    {
        new UsagePolicy('', new Request(new Bot()), new CookiesService());
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_can_confirm_ownership_of_visitor(): void
    {
        $_COOKIE = [];
        $usagePolicy = new UsagePolicy('foo', new Request(new Bot()), new CookiesService());
        self::assertNotEmpty($_COOKIE);
        self::assertSame('confirmedOwnershipOfFoo', $_COOKIE['ownershipCookieName']);
        self::assertSame('trialOfFoo', $_COOKIE['trialCookieName']);
        self::assertSame('trialExpiredAt', $_COOKIE['trialExpiredAtName']);
        self::assertArrayNotHasKey('confirmedOwnershipOfFoo', $_COOKIE);
        $usagePolicy->confirmOwnershipOfVisitor($expiresAt = new \DateTime());
        self::assertSame((string)$expiresAt->getTimestamp(), $_COOKIE['confirmedOwnershipOfFoo']);
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_can_find_out_if_trial_expired(): void
    {
        $usagePolicy = new UsagePolicy('foo', new Request(new Bot()), new CookiesService());
        self::assertFalse($usagePolicy->trialJustExpired(), 'Did not expects trial expiration yet');
        $_GET[UsagePolicy::TRIAL_EXPIRED_AT] = \time();
        self::assertTrue($usagePolicy->trialJustExpired(), 'Expected trial expiration');
        $_GET[UsagePolicy::TRIAL_EXPIRED_AT] = \time() + 2;
        self::assertFalse($usagePolicy->trialJustExpired(), 'Did not expects trial expiration as its time is in future');
        $_GET[UsagePolicy::TRIAL_EXPIRED_AT] = 0;
        self::assertFalse($usagePolicy->trialJustExpired(), 'Did not expects trial expiration now');
    }
}
