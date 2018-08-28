<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\SkeletonInjectorComposerPlugin;
use DrdPlus\Tests\RulesSkeleton\Partials\TestsConfigurationReader;

/**
 * @method TestsConfigurationReader getTestsConfiguration
 */
class ComposerConfigTest extends \DrdPlus\Tests\FrontendSkeleton\ComposerConfigTest
{
    use Partials\AbstractContentTestTrait;

    /**
     * @test
     */
    public function Has_licence_matching_to_access(): void
    {
        $expectedLicence = $this->getTestsConfiguration()->getExpectedLicence();
        self::assertSame($expectedLicence, static::$composerConfig['license'], "Expected licence '$expectedLicence'");
    }

    /**
     * @test
     */
    public function Package_is_injected(): void
    {
        if (!$this->isSkeletonChecked()) {
            self::assertFalse(false, 'Intended for skeleton only');

            return;
        }
        self::assertSame('composer-plugin', static::$composerConfig['type']);
        self::assertSame(SkeletonInjectorComposerPlugin::class, static::$composerConfig['extra']['class']);
    }
}