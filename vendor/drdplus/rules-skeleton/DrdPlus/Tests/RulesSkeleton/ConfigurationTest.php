<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\Configuration;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;

class ConfigurationTest extends AbstractContentTest
{
    use YamlFileTestTrait;

    /**
     * @test
     */
    public function I_can_use_both_config_distribution_as_well_as_local_yaml_files(): void
    {
        if ($this->isSkeletonChecked()) {
            self::assertFileExists(
                $this->getDocumentRoot() . '/' . Configuration::CONFIG_LOCAL_YML,
                'Local configuration expected on skeleton for testing purpose'
            );
        }
        self::assertFileExists($this->getDocumentRoot() . '/' . Configuration::CONFIG_DISTRIBUTION_YML);
    }

    /**
     * @test
     * @dataProvider provideCompleteLocalAndDistributionYamlContent
     * @param array $localYamlContent
     * @param array $distributionYamlContent
     * @param array $expectedYamlContent
     */
    public function I_can_create_it_from_yaml_files(array $localYamlContent, array $distributionYamlContent, array $expectedYamlContent): void
    {
        $yamlTestingDir = $this->getYamlTestingDir();
        $this->createYamlLocalConfig($localYamlContent, $yamlTestingDir);
        $this->createYamlDistributionConfig($distributionYamlContent, $yamlTestingDir);
        $configuration = Configuration::createFromYml($dirs = $this->createDirs($yamlTestingDir));
        self::assertSame($expectedYamlContent, $configuration->getSettings());
        self::assertSame($expectedYamlContent[Configuration::WEB][Configuration::LAST_STABLE_VERSION], $configuration->getWebLastStableMinorVersion());
        self::assertSame($expectedYamlContent[Configuration::WEB][Configuration::REPOSITORY_URL], $configuration->getWebRepositoryUrl());
        self::assertSame($expectedYamlContent[Configuration::GOOGLE][Configuration::ANALYTICS_ID], $configuration->getGoogleAnalyticsId());
        self::assertSame($dirs, $configuration->getDirs());
    }

    public function provideCompleteLocalAndDistributionYamlContent(): array
    {
        $completeYamlContent = $this->getSomeCompleteSettings();
        $limitedWebSection = $completeYamlContent;
        $limitedWebSection[Configuration::WEB] = [Configuration::LAST_STABLE_VERSION => '456.789'];
        $changedCompleteYamlContent = $completeYamlContent;
        $changedCompleteYamlContent[Configuration::WEB][Configuration::LAST_STABLE_VERSION] = '456.789';

        return [
            [$completeYamlContent, [], $completeYamlContent],
            [$limitedWebSection, $completeYamlContent, $changedCompleteYamlContent],
            [$completeYamlContent, $limitedWebSection, $completeYamlContent],
        ];
    }

    protected function getSomeCompleteSettings(): array
    {
        return [
            Configuration::WEB => [
                Configuration::LAST_STABLE_VERSION => '123.456',
                Configuration::REPOSITORY_URL => \sys_get_temp_dir(),
                Configuration::MENU_POSITION_FIXED => false,
                Configuration::SHOW_HOME_BUTTON => true,
                Configuration::NAME => 'Foo',
                Configuration::TITLE_SMILEY => '',
                Configuration::PROTECTED_ACCESS => true,
                Configuration::ESHOP_URL => 'https://example.com',
                Configuration::HIDE_HOME_BUTTON => false,
            ],
            Configuration::GOOGLE => [Configuration::ANALYTICS_ID => 'UA-121206931-999'],
        ];
    }

    /**
     * @test
     */
    public function I_can_create_it_with_master_as_last_stable_version(): void
    {
        $completeSettings = $this->getSomeCompleteSettings();
        $completeSettings[Configuration::WEB][Configuration::LAST_STABLE_VERSION] = 'master';
        $configuration = new Configuration($this->createDirs(), $completeSettings);
        self::assertSame('master', $configuration->getWebLastStableMinorVersion());
    }

    /**
     * @test
     * @expectedException \DrdPlus\RulesSkeleton\Exceptions\InvalidMinorVersion
     * @expectedExceptionMessageRegExp ~public enemy~
     */
    public function I_can_not_create_it_with_invalid_last_stable_version(): void
    {
        $completeSettings = $this->getSomeCompleteSettings();
        $completeSettings[Configuration::WEB][Configuration::LAST_STABLE_VERSION] = 'public enemy';
        new Configuration($this->createDirs(), $completeSettings);
    }

    /**
     * @test
     * @expectedException \DrdPlus\RulesSkeleton\Exceptions\InvalidWebRepositoryUrl
     * @expectedExceptionMessageRegExp ~/somewhere://over[.]the\?rainbow=GPS~
     */
    public function I_can_not_create_it_with_invalid_web_repository_url(): void
    {
        $completeSettings = $this->getSomeCompleteSettings();
        $completeSettings[Configuration::WEB][Configuration::REPOSITORY_URL] = '/somewhere://over.the?rainbow=GPS';
        new Configuration($this->createDirs(), $completeSettings);
    }

    /**
     * @test
     * @expectedException \DrdPlus\RulesSkeleton\Exceptions\InvalidGoogleAnalyticsId
     * @expectedExceptionMessageRegExp ~GoogleItself~
     */
    public function I_can_not_create_it_with_invalid_google_analytics_id(): void
    {
        $completeSettings = $this->getSomeCompleteSettings();
        $completeSettings[Configuration::GOOGLE][Configuration::ANALYTICS_ID] = 'GoogleItself';
        new Configuration($this->createDirs(), $completeSettings);
    }

    /**
     * @test
     * @expectedException \DrdPlus\RulesSkeleton\Exceptions\InvalidMenuPosition
     */
    public function I_can_not_create_it_without_defining_if_menu_should_be_fixed(): void
    {
        $completeSettings = $this->getSomeCompleteSettings();
        unset($completeSettings[Configuration::WEB][Configuration::MENU_POSITION_FIXED]);
        new Configuration($this->createDirs(), $completeSettings);
    }

    /**
     * @test
     * @expectedException \DrdPlus\RulesSkeleton\Exceptions\InvalidShowOfHomeButton
     */
    public function I_can_not_create_it_without_defining_if_show_home_button(): void
    {
        $completeSettings = $this->getSomeCompleteSettings();
        unset($completeSettings[Configuration::WEB][Configuration::SHOW_HOME_BUTTON]);
        new Configuration($this->createDirs(), $completeSettings);
    }

    /**
     * @test
     * @expectedException \DrdPlus\RulesSkeleton\Exceptions\MissingWebName
     */
    public function I_can_not_create_it_without_web_name(): void
    {
        $completeSettings = $this->getSomeCompleteSettings();
        $completeSettings[Configuration::WEB][Configuration::NAME] = '';
        new Configuration($this->createDirs(), $completeSettings);
    }

    /**
     * @test
     * @expectedException \DrdPlus\RulesSkeleton\Exceptions\TitleSmileyIsNotSet
     */
    public function I_can_not_create_it_without_set_title_smiley(): void
    {
        $completeSettings = $this->getSomeCompleteSettings();
        unset($completeSettings[Configuration::WEB][Configuration::TITLE_SMILEY]);
        new Configuration($this->createDirs(), $completeSettings);
    }

    /**
     * @test
     */
    public function I_can_create_it_with_title_smiley_as_null(): void
    {
        $completeSettings = $this->getSomeCompleteSettings();
        $completeSettings[Configuration::WEB][Configuration::TITLE_SMILEY] = null;
        $configuration = new Configuration($this->createDirs(), $completeSettings);
        self::assertSame('', $configuration->getTitleSmiley());
    }

    /**
     * @test
     */
    public function Web_repository_is_changed_from_skeleton(): void
    {
        if ($this->isSkeletonChecked()) {
            self::assertFalse(false, 'We are still in skeleton, nothing to test here');

            return;
        }
        $skeletonConfiguration = $this->getSkeletonConfiguration();
        $currentConfiguration = $this->getConfiguration();
        self::assertNotSame(
            $skeletonConfiguration->getWebRepositoryUrl(),
            $currentConfiguration->getWebRepositoryUrl(),
            'Current web repository seems to be forgotten from skeleton copy'
        );
    }

    protected function getSkeletonConfiguration(): Configuration
    {
        $configurationClass = $this->getConfigurationClass();

        return $configurationClass::createFromYml($this->createDirs($this->getSkeletonDocumentRoot()));
    }
}