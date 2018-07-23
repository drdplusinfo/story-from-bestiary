<?php
namespace DrdPlus\Tests\RulesSkeleton;

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
    public function Libraries_git_dirs_are_removed(): void
    {
        $preAutoloadDumpScripts = static::$composerConfig['scripts']['pre-autoload-dump'] ?? [];
        self::assertNotEmpty($preAutoloadDumpScripts, 'Missing pre-autoload-dump scripts');
        if ($this->isSkeletonChecked()) {
            self::assertNotContains(
                'find ./vendor -type d -name .git -exec rm -fr {} +',
                $preAutoloadDumpScripts,
                'There is no reason to remove vendors .git dir in skeleton as vendor dir is not versioned'
            );
        } else {
            self::assertContains(
                'find ./vendor -type d -name .git -exec rm -fr {} +',
                $preAutoloadDumpScripts,
                'Missing vendors .git dir removal, there are configs '
                . \preg_replace('~^Array\n\((.+)\)~', '$1', \var_export($preAutoloadDumpScripts, true))
            );
        }
    }

    /**
     * @test
     */
    public function PHPUnit_config_is_copied_from_skeleton(): void
    {
        $preAutoloadDumpScripts = static::$composerConfig['scripts']['pre-autoload-dump'] ?? [];
        self::assertNotEmpty($preAutoloadDumpScripts, 'Missing pre-autoload-dump scripts');
        $sourceSkeleton = $this->isSkeletonChecked(__DIR__ . '/../../..') ? 'frontend-skeleton' : 'rules-skeleton';
        $fileCopyScript = "cp ./vendor/drd-plus/$sourceSkeleton/phpunit.xml.dist .";
        self::assertContains(
            $fileCopyScript,
            $preAutoloadDumpScripts,
            'Missing script to copy file with PHPUnit config, there are configs '
            . \preg_replace('~^Array\n\((.+)\)~', '$1', \var_export($preAutoloadDumpScripts, true))
        );
    }

    /**
     * @test
     */
    public function File_for_for_google_search_console_verification_is_copied_from_skeleton(): void
    {
        $preAutoloadDumpScripts = static::$composerConfig['scripts']['pre-autoload-dump'] ?? [];
        self::assertNotEmpty($preAutoloadDumpScripts, 'Missing pre-autoload-dump scripts');
        $sourceSkeleton = $this->isSkeletonChecked(__DIR__ . '/../../..') ? 'frontend-skeleton' : 'rules-skeleton';
        $fileCopyScript = "cp ./vendor/drd-plus/$sourceSkeleton/google8d8724e0c2818dfc.html .";
        self::assertContains(
            $fileCopyScript,
            $preAutoloadDumpScripts,
            'Missing script to copy file for Google search console verification, there are configs '
            . \preg_replace('~^Array\n\((.+)\)~', '$1', \var_export($preAutoloadDumpScripts, true))
        );
    }
}