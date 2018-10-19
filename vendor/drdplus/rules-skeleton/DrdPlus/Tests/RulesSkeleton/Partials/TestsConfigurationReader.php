<?php
namespace DrdPlus\Tests\RulesSkeleton\Partials;

interface TestsConfigurationReader
{
    public function getLocalUrl(): string;

    public function hasTables(): bool;

    public function getSomeExpectedTableIds(): array;

    public function hasExternalAnchorsWithHashes(): bool;

    public function hasMoreVersions(): bool;

    public function hasCustomBodyContent(): bool;

    public function hasNotes(): bool;

    public function hasIds(): bool;

    public function hasLocalLinks(): bool;

    public function hasLinksToAltar();

    public function getExpectedWebName(): string;

    public function getExpectedPageTitle(): string;

    public function getExpectedGoogleAnalyticsId(): string;

    public function getAllowedCalculationIdPrefixes(): array;

    public function getExpectedLastVersion(): string;

    public function getExpectedLastUnstableVersion(): string;

    public function hasHeadings(): bool;

    public function getPublicUrl(): string;

    public function hasProtectedAccess(): bool;

    public function canBeBoughtOnEshop(): bool;

    public function hasCharacterSheet(): bool;

    public function hasLinksToJournals(): bool;

    public function hasLinkToSingleJournal(): bool;

    public function hasDebugContacts(): bool;

    public function hasAuthors(): bool;

    public function getBlockNamesToExpectedContent(): array;

    public function getExpectedLicence(): string;

    public function getTooShortFailureNames(): array;

    public function getTooShortSuccessNames(): array;

    public function getTooShortResultNames(): array;

    public function hasTableOfContents(): bool;
}