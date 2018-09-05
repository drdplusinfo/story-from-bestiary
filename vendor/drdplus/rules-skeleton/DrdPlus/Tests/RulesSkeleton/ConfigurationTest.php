<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\Configuration;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTestTrait;

class ConfigurationTest extends \DrdPlus\Tests\FrontendSkeleton\ConfigurationTest
{
    use AbstractContentTestTrait;

    /**
     * @return string|Configuration
     */
    protected function getConfigurationClass(): string
    {
        return Configuration::class;
    }

    protected function getSomeCompleteSettings(): array
    {
        $settings = parent::getSomeCompleteSettings();
        $settings[Configuration::WEB][Configuration::PROTECTED_ACCESS] = true;
        $settings[Configuration::WEB][Configuration::ESHOP_URL] = 'https://example.com';
        $settings[Configuration::WEB][Configuration::HIDE_HOME_BUTTON] = false;

        return $settings;
    }

}