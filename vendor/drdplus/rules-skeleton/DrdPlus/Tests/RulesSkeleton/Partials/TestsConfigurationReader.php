<?php
namespace DrdPlus\Tests\RulesSkeleton\Partials;

interface TestsConfigurationReader extends \DrdPlus\Tests\FrontendSkeleton\Partials\TestsConfigurationReader
{
    public function getPublicUrl(): string;

    public function hasProtectedAccess(): bool;

    public function canBeBoughtOnEshop(): bool;

    public function hasCharacterSheet(): bool;

    public function hasLinksToJournals(): bool;

    public function hasLinkToSingleJournal(): bool;

    public function hasDebugContacts(): bool;

    public function hasIntroduction(): bool;

    public function hasAuthors(): bool;

    public function getBlockNamesToExpectedContent(): array;

    public function getExpectedLicence(): string;

    public function getTooShortFailureNames(): array;

    public function getTooShortSuccessNames(): array;

    public function getTooShortResultNames(): array;
}