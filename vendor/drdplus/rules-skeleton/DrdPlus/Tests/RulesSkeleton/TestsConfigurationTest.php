<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

/**
 * @method string|TestsConfiguration getSutClass
 */
class TestsConfigurationTest extends \DrdPlus\Tests\FrontendSkeleton\TestsConfigurationTest
{
    /**
     * @param string $publicUrl
     * @return \DrdPlus\Tests\FrontendSkeleton\TestsConfiguration|TestsConfiguration
     */
    protected function createSut(string $publicUrl = 'https://drdplus.info'): \DrdPlus\Tests\FrontendSkeleton\TestsConfiguration
    {
        $sutClass = $this->getSutClass();

        return new $sutClass($publicUrl);
    }

    protected function getNonExistingSettersToSkip(): array
    {
        return \array_merge(parent::getNonExistingSettersToSkip(), ['setPublicUrl']); // this has to set via constructor
    }

    /**
     * @test
     */
    public function I_can_set_and_get_local_and_public_url(): void
    {
        $testsConfiguration = $this->createSut('https://drdplus.info');
        self::assertSame('http://drdplus.loc:88', $testsConfiguration->getLocalUrl());
        self::assertSame('https://drdplus.info', $testsConfiguration->getPublicUrl());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Tests\RulesSkeleton\Exceptions\InvalidPublicUrl
     * @expectedExceptionMessageRegExp ~not valid~
     */
    public function I_can_not_create_it_with_invalid_public_url(): void
    {
        $this->createSut('example.com'); // missing protocol
    }

    /**
     * @test
     * @expectedException \DrdPlus\Tests\RulesSkeleton\Exceptions\PublicUrlShouldUseHttps
     * @expectedExceptionMessageRegExp ~HTTPS~
     */
    public function I_can_not_create_it_with_public_url_without_https(): void
    {
        $this->createSut('http://example.com');
    }

    /**
     * @test
     */
    public function I_will_get_expected_licence_by_access_by_default(): void
    {
        $testsConfiguration = $this->createSut();
        self::assertTrue($testsConfiguration->hasProtectedAccess());
        self::assertSame('proprietary', $testsConfiguration->getExpectedLicence(), 'Expected proprietary licence for protected access');
        $testsConfiguration->disableHasProtectedAccess();
        self::assertFalse($testsConfiguration->hasProtectedAccess());
        self::assertSame('MIT', $testsConfiguration->getExpectedLicence(), 'Expected MIT licence for free access');
        $testsConfiguration->setExpectedLicence('foo');
        self::assertSame('foo', $testsConfiguration->getExpectedLicence());
    }

    /**
     * @test
     */
    public function I_can_add_too_short_failure_names(): void
    {
        $testsConfiguration = $this->createSut();
        self::assertCount(1, $testsConfiguration->getTooShortFailureNames());
        $testsConfiguration->addTooShortFailureName('foo');
        self::assertSame(['nevšiml si', 'foo'], $testsConfiguration->getTooShortFailureNames());
        $testsConfiguration->addTooShortFailureName('bar');
        self::assertSame(['nevšiml si', 'foo', 'bar'], $testsConfiguration->getTooShortFailureNames());
        $testsConfiguration->setTooShortFailureNames(['baz', 'qux']);
        self::assertSame(['baz', 'qux'], $testsConfiguration->getTooShortFailureNames());
    }

    /**
     * @test
     */
    public function I_can_add_every_too_short_failure_name_just_once(): void
    {
        $testsConfiguration = $this->createSut();
        $testsConfiguration->setTooShortFailureNames(['foo', 'bar']);
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortFailureNames());
        $testsConfiguration->addTooShortFailureName('foo');
        $testsConfiguration->addTooShortFailureName('bar');
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortFailureNames());
    }

    /**
     * @test
     */
    public function I_can_add_too_short_success_names(): void
    {
        $testsConfiguration = $this->createSut();
        self::assertCount(1, $testsConfiguration->getTooShortSuccessNames());
        $testsConfiguration->addTooShortSuccessName('foo');
        self::assertSame(['všiml si', 'foo'], $testsConfiguration->getTooShortSuccessNames());
        $testsConfiguration->addTooShortSuccessName('bar');
        self::assertSame(['všiml si', 'foo', 'bar'], $testsConfiguration->getTooShortSuccessNames());
        $testsConfiguration->setTooShortSuccessNames(['baz', 'qux']);
        self::assertSame(['baz', 'qux'], $testsConfiguration->getTooShortSuccessNames());
    }

    /**
     * @test
     */
    public function I_can_add_every_too_short_success_name_just_once(): void
    {
        $testsConfiguration = $this->createSut();
        $testsConfiguration->setTooShortSuccessNames(['foo', 'bar']);
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortSuccessNames());
        $testsConfiguration->addTooShortSuccessName('foo');
        $testsConfiguration->addTooShortSuccessName('bar');
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortSuccessNames());
    }

    /**
     * @test
     */
    public function I_can_add_too_short_result_names(): void
    {
        $testsConfiguration = $this->createSut();
        self::assertCount(2, $testsConfiguration->getTooShortResultNames());
        $testsConfiguration->addTooShortResultName('foo');
        self::assertSame(['Bonus', 'Postih', 'foo'], $testsConfiguration->getTooShortResultNames());
        $testsConfiguration->addTooShortResultName('bar');
        self::assertSame(['Bonus', 'Postih', 'foo', 'bar'], $testsConfiguration->getTooShortResultNames());
        $testsConfiguration->setTooShortResultNames(['baz', 'qux']);
        self::assertSame(['baz', 'qux'], $testsConfiguration->getTooShortResultNames());
    }

    /**
     * @test
     */
    public function I_can_add_every_too_short_result_name_just_once(): void
    {
        $testsConfiguration = $this->createSut();
        $testsConfiguration->setTooShortResultNames(['foo', 'bar']);
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortResultNames());
        $testsConfiguration->addTooShortResultName('foo');
        $testsConfiguration->addTooShortResultName('bar');
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortResultNames());
    }

}