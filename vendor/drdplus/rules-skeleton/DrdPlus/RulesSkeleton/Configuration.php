<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

/**
 * @method static Configuration createFromYml(Dirs $dirs)
 * @method Dirs getDirs
 */
class Configuration extends \DrdPlus\FrontendSkeleton\Configuration
{
    public const PROTECTED_ACCESS = 'protected_access';
    public const HIDE_HOME_BUTTON = 'hide_home_button';
    public const ESHOP_URL = 'eshop_url';

    public function __construct(Dirs $dirs, array $settings)
    {
        $this->guardValidEshopUrl($settings);
        $this->guardSetIfHasProtectedAccess($settings);
        $this->guardSetIfHasShownHomeButton($settings);
        parent::__construct($dirs, $settings);
    }

    /**
     * @param array $settings
     * @throws \DrdPlus\RulesSkeleton\Exceptions\InvalidEshopUrl
     */
    protected function guardValidEshopUrl(array $settings): void
    {
        if (!\filter_var($settings[static::WEB][static::ESHOP_URL] ?? '', FILTER_VALIDATE_URL)) {
            throw new Exceptions\InvalidEshopUrl(
                'Given e-shop URL is not valid, expected some URL in configuration '
                . static::WEB . ': ' . static::ESHOP_URL . ', got ' . ($settings[static::WEB][static::ESHOP_URL] ?? 'nothing')
            );
        }
    }

    protected function guardSetIfHasProtectedAccess(array $settings): void
    {
        if (($settings[static::WEB][static::PROTECTED_ACCESS] ?? null) === null) {
            throw new Exceptions\MissingProtectedAccessConfiguration(
                'Configuration if web has protected access is missing in configuration '
                . static::WEB . ': ' . static::PROTECTED_ACCESS
            );
        }
    }

    protected function guardSetIfHasShownHomeButton(array $settings): void
    {
        if (($settings[static::WEB][static::SHOW_HOME_BUTTON] ?? null) === null) {
            throw new Exceptions\MissingShownHomeButtonConfiguration(
                'Configuration if home button should be shown is missing in configuration '
                . static::WEB . ': ' . static::SHOW_HOME_BUTTON
            );
        }
    }

    public function hasProtectedAccess(): bool
    {
        return (bool)$this->getSettings()[self::WEB][self::PROTECTED_ACCESS];
    }

    public function shouldHideHomeButton(): bool
    {
        return (bool)$this->getSettings()[self::WEB][self::HIDE_HOME_BUTTON];
    }

    public function getEshopUrl(): string
    {
        return $this->getSettings()[self::WEB][self::ESHOP_URL];
    }
}