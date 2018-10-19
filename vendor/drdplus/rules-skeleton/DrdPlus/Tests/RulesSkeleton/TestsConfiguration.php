<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\Tests\RulesSkeleton\Exceptions\InvalidUrl;
use DrdPlus\Tests\RulesSkeleton\Partials\TestsConfigurationReader;
use Granam\Scalar\Tools\ToString;
use Granam\Strict\Object\StrictObject;

class TestsConfiguration extends StrictObject implements TestsConfigurationReader
{
    public const LICENCE_BY_ACCESS = '*by access*';
    public const LICENCE_MIT = 'MIT';
    public const LICENCE_PROPRIETARY = 'proprietary';

    // every setting SHOULD be strict (expecting instead of ignoring)

    private const DEFAULT_ALLOWED_CALCULATION_ID_PREFIXES = ['Hod proti', 'Hod na', 'Výpočet'];

    /** @var string */
    private $localUrl;
    /** @var bool */
    private $hasTables = true;
    /** @var array|string[] */
    private $someExpectedTableIds = [];
    /** @var bool */
    private $hasExternalAnchorsWithHashes = true;
    /** @var bool */
    private $hasMoreVersions = true;
    /** @var bool */
    private $hasCustomBodyContent = true;
    /** @var bool */
    private $hasNotes = true;
    /** @var bool */
    private $hasIds = true;
    /** @var bool */
    private $hasLocalLinks = true;
    /** @var bool */
    private $hasLinksToAltar = true;
    /** @var string */
    private $expectedWebName = 'HTML kostra pro DrD+ webový obsah';
    /** @var string */
    private $expectedPageTitle = '🔱 HTML kostra pro DrD+ webový obsah';
    /** @var string */
    private $expectedGoogleAnalyticsId = 'UA-121206931-1';
    /** @var array|string[] */
    private $allowedCalculationIdPrefixes = self::DEFAULT_ALLOWED_CALCULATION_ID_PREFIXES;
    /** @var string */
    private $expectedLastVersion = '1.0';
    /** @var string */
    private $expectedLastUnstableVersion = 'master';
    /** @var bool */
    private $hasHeadings = true;
    /** @var string */
    private $publicUrl;
    /** @var bool */
    private $hasProtectedAccess = true;
    /** @var bool */
    private $canBeBoughtOnEshop = true;
    /** @var bool */
    private $hasCharacterSheet = true;
    /** @var bool */
    private $hasLinksToJournals = true;
    /** @var bool */
    private $hasLinkToSingleJournal = true;
    /** @var bool */
    private $hasDebugContacts = true;
    /** @var bool */
    private $hasAuthors = true;
    /** @var array|string[] */
    private $blockNamesToExpectedContent = ['just-some-block' => <<<HTML
<div class="block-just-some-block">
    First part of some block
</div>

<div class="block-just-some-block">
    Second part of some block
</div>

<div class="block-just-some-block">
    Last part of some block
</div>
HTML
        ,
    ];
    /** @var string */
    private $expectedLicence = '*by access*';
    /** @var array|string[] */
    private $tooShortFailureNames = ['nevšiml si'];
    /** @var array|string[] */
    private $tooShortSuccessNames = ['všiml si'];
    /** @var array|string[] */
    private $tooShortResultNames = ['Bonus', 'Postih'];
    /** @var bool */
    private $hasTableOfContents = true;

    /**
     * @param string $publicUrl
     * @throws \DrdPlus\Tests\RulesSkeleton\Exceptions\InvalidLocalUrl
     * @throws \DrdPlus\Tests\RulesSkeleton\Exceptions\InvalidPublicUrl
     * @throws \DrdPlus\Tests\RulesSkeleton\Exceptions\PublicUrlShouldUseHttps
     */
    public function __construct(string $publicUrl)
    {
        try {
            $this->guardValidUrl($publicUrl);
        } catch (InvalidUrl $invalidUrl) {
            throw new Exceptions\InvalidPublicUrl("Given public URL is not valid: '$publicUrl'", $invalidUrl->getCode(), $invalidUrl);
        }
        if (\strpos($publicUrl, 'https://') !== 0) {
            throw new Exceptions\PublicUrlShouldUseHttps("Given public URL should use HTTPS: '$publicUrl'");
        }
        $this->publicUrl = $publicUrl;
        $localUrl = HtmlHelper::turnToLocalLink($publicUrl);
        if (!$this->isLocalLinkAccessible($localUrl)) {
            throw new Exceptions\InvalidLocalUrl("Given local URL can not be reached or is not local: '$localUrl'");
        }
        $localUrl = $this->addPortToLocalUrl($localUrl);
        $this->guardValidUrl($localUrl);
        $this->localUrl = $localUrl;
    }

    private function addPortToLocalUrl(string $localUrl)
    {
        if (\preg_match('~:\d+$~', $localUrl)) {
            return $localUrl; // already with port
        }

        return $localUrl . ':88';
    }

    /**
     * @param string $url
     * @throws \DrdPlus\Tests\RulesSkeleton\Exceptions\InvalidUrl
     */
    private function guardValidUrl(string $url): void
    {
        if (!\filter_var($url, \FILTER_VALIDATE_URL)) {
            throw new Exceptions\InvalidUrl("Given URL is not valid: '$url'");
        }
    }

    /**
     * @return string
     */
    public function getLocalUrl(): string
    {
        return $this->localUrl;
    }

    /**
     * @return bool
     */
    public function hasTables(): bool
    {
        return $this->hasTables;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasTables(): TestsConfiguration
    {
        $this->hasTables = false;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getSomeExpectedTableIds(): array
    {
        return $this->someExpectedTableIds;
    }

    /**
     * @param array|string[] $someExpectedTableIds
     * @return TestsConfiguration
     */
    public function setSomeExpectedTableIds(array $someExpectedTableIds): TestsConfiguration
    {
        $this->someExpectedTableIds = [];
        foreach ($someExpectedTableIds as $someExpectedTableId) {
            $this->someExpectedTableIds[] = ToString::toString($someExpectedTableId);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasExternalAnchorsWithHashes(): bool
    {
        return $this->hasExternalAnchorsWithHashes;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasExternalAnchorsWithHashes(): TestsConfiguration
    {
        $this->hasExternalAnchorsWithHashes = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasMoreVersions(): bool
    {
        return $this->hasMoreVersions;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasMoreVersions(): TestsConfiguration
    {
        $this->hasMoreVersions = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCustomBodyContent(): bool
    {
        return $this->hasCustomBodyContent;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasCustomBodyContent(): TestsConfiguration
    {
        $this->hasCustomBodyContent = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasNotes(): bool
    {
        return $this->hasNotes;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasNotes(): TestsConfiguration
    {
        $this->hasNotes = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasIds(): bool
    {
        return $this->hasIds;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasIds(): TestsConfiguration
    {
        $this->hasIds = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLocalLinks(): bool
    {
        return $this->hasLocalLinks;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasLocalLinks(): TestsConfiguration
    {
        $this->hasLocalLinks = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLinksToAltar(): bool
    {
        return $this->hasLinksToAltar;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasLinksToAltar(): TestsConfiguration
    {
        $this->hasLinksToAltar = false;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpectedWebName(): string
    {
        return $this->expectedWebName;
    }

    /**
     * @param string $expectedWebName
     * @return TestsConfiguration
     */
    public function setExpectedWebName(string $expectedWebName): TestsConfiguration
    {
        $this->expectedWebName = $expectedWebName;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpectedPageTitle(): string
    {
        return $this->expectedPageTitle;
    }

    /**
     * @param string $expectedPageTitle
     * @return TestsConfiguration
     */
    public function setExpectedPageTitle(string $expectedPageTitle): TestsConfiguration
    {
        $this->expectedPageTitle = $expectedPageTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpectedGoogleAnalyticsId(): string
    {
        return $this->expectedGoogleAnalyticsId;
    }

    /**
     * @param string $expectedGoogleAnalyticsId
     * @return TestsConfiguration
     */
    public function setExpectedGoogleAnalyticsId(string $expectedGoogleAnalyticsId): TestsConfiguration
    {
        $this->expectedGoogleAnalyticsId = $expectedGoogleAnalyticsId;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getAllowedCalculationIdPrefixes(): array
    {
        return $this->allowedCalculationIdPrefixes;
    }

    /**
     * @param array|string[] $allowedCalculationIdPrefixes
     * @return TestsConfiguration
     * @throws \DrdPlus\Tests\RulesSkeleton\Exceptions\AllowedCalculationPrefixShouldStartByUpperLetter
     */
    public function setAllowedCalculationIdPrefixes(array $allowedCalculationIdPrefixes): TestsConfiguration
    {
        $this->allowedCalculationIdPrefixes = [];
        foreach ($allowedCalculationIdPrefixes as $allowedCalculationIdPrefix) {
            $this->addAllowedCalculationIdPrefix($allowedCalculationIdPrefix);
        }

        return $this;
    }

    /**
     * @param string $allowedCalculationIdPrefix
     * @return TestsConfiguration
     * @throws \DrdPlus\Tests\RulesSkeleton\Exceptions\AllowedCalculationPrefixShouldStartByUpperLetter
     */
    public function addAllowedCalculationIdPrefix(string $allowedCalculationIdPrefix): TestsConfiguration
    {
        if (!\preg_match('~^[[:upper:]]~u', $allowedCalculationIdPrefix)) {
            throw new Exceptions\AllowedCalculationPrefixShouldStartByUpperLetter(
                "First letter of allowed calculation prefix should be uppercase, got '$allowedCalculationIdPrefix'"
            );
        }
        $this->allowedCalculationIdPrefixes[] = $allowedCalculationIdPrefix;

        return $this;
    }

    /**
     * Latest stable version if available, master if not
     * @return string
     */
    public function getExpectedLastVersion(): string
    {
        return $this->expectedLastVersion;
    }

    public function setExpectedLastVersion(string $expectedLastVersion): TestsConfiguration
    {
        $this->expectedLastVersion = $expectedLastVersion;

        return $this;
    }

    public function getExpectedLastUnstableVersion(): string
    {
        return $this->expectedLastUnstableVersion;
    }

    public function setExpectedLastUnstableVersion(string $expectedLastUnstableVersion): TestsConfiguration
    {
        $this->expectedLastUnstableVersion = $expectedLastUnstableVersion;

        return $this;
    }

    public function hasHeadings(): bool
    {
        return $this->hasHeadings;
    }

    public function disableHasHeadings(): TestsConfiguration
    {
        $this->hasHeadings = false;

        return $this;
    }

    private function isLocalLinkAccessible(string $localUrl): bool
    {
        $host = \parse_url($localUrl, \PHP_URL_HOST);

        return $host !== false
            && !\filter_var($host, \FILTER_VALIDATE_IP)
            && \gethostbyname($host) === '127.0.0.1';
    }

    /**
     * @return string
     */
    public function getPublicUrl(): string
    {
        return $this->publicUrl;
    }

    /**
     * @return bool
     */
    public function hasProtectedAccess(): bool
    {
        return $this->hasProtectedAccess;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasProtectedAccess(): TestsConfiguration
    {
        $this->hasProtectedAccess = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function canBeBoughtOnEshop(): bool
    {
        return $this->canBeBoughtOnEshop;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableCanBeBoughtOnEshop(): TestsConfiguration
    {
        $this->canBeBoughtOnEshop = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCharacterSheet(): bool
    {
        return $this->hasCharacterSheet;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasCharacterSheet(): TestsConfiguration
    {
        $this->hasCharacterSheet = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLinksToJournals(): bool
    {
        return $this->hasLinksToJournals;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasLinksToJournals(): TestsConfiguration
    {
        $this->hasLinksToJournals = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLinkToSingleJournal(): bool
    {
        return $this->hasLinkToSingleJournal;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasLinkToSingleJournal(): TestsConfiguration
    {
        $this->hasLinkToSingleJournal = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDebugContacts(): bool
    {
        return $this->hasDebugContacts;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasDebugContacts(): TestsConfiguration
    {
        $this->hasDebugContacts = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAuthors(): bool
    {
        return $this->hasAuthors;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasAuthors(): TestsConfiguration
    {
        $this->hasAuthors = false;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getBlockNamesToExpectedContent(): array
    {
        return $this->blockNamesToExpectedContent;
    }

    /**
     * @param array $blockNamesToExpectedContent
     * @return TestsConfiguration
     */
    public function setBlockNamesToExpectedContent(array $blockNamesToExpectedContent): TestsConfiguration
    {
        $this->blockNamesToExpectedContent = $blockNamesToExpectedContent;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpectedLicence(): string
    {
        if ($this->expectedLicence !== self::LICENCE_BY_ACCESS) {
            return $this->expectedLicence;
        }

        return $this->hasProtectedAccess()
            ? self::LICENCE_PROPRIETARY
            : self::LICENCE_MIT;
    }

    /**
     * @param string $expectedLicence
     * @return TestsConfiguration
     */
    public function setExpectedLicence(string $expectedLicence): TestsConfiguration
    {
        $this->expectedLicence = $expectedLicence;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getTooShortFailureNames(): array
    {
        return $this->tooShortFailureNames;
    }

    /**
     * @param array|string[] $tooShortFailureNames
     * @return TestsConfiguration
     */
    public function setTooShortFailureNames(array $tooShortFailureNames): TestsConfiguration
    {
        $this->tooShortFailureNames = $tooShortFailureNames;

        return $this;
    }

    public function addTooShortFailureName(string $tooShortFailureName): TestsConfiguration
    {
        if (!\in_array($tooShortFailureName, $this->tooShortFailureNames, true)) {
            $this->tooShortFailureNames[] = $tooShortFailureName;
        }

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getTooShortSuccessNames(): array
    {
        return $this->tooShortSuccessNames;
    }

    /**
     * @param array|string[] $tooShortSuccessNames
     * @return TestsConfiguration
     */
    public function setTooShortSuccessNames(array $tooShortSuccessNames): TestsConfiguration
    {
        $this->tooShortSuccessNames = $tooShortSuccessNames;

        return $this;
    }

    public function addTooShortSuccessName(string $tooShortSuccessName): TestsConfiguration
    {
        if (!\in_array($tooShortSuccessName, $this->tooShortSuccessNames, true)) {
            $this->tooShortSuccessNames[] = $tooShortSuccessName;
        }

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getTooShortResultNames(): array
    {
        return $this->tooShortResultNames;
    }

    /**
     * @param array|string[] $tooShortResultNames
     * @return TestsConfiguration
     */
    public function setTooShortResultNames(array $tooShortResultNames): TestsConfiguration
    {
        $this->tooShortResultNames = $tooShortResultNames;

        return $this;
    }

    public function addTooShortResultName(string $tooShortResultName): TestsConfiguration
    {
        if (!\in_array($tooShortResultName, $this->tooShortResultNames, true)) {
            $this->tooShortResultNames[] = $tooShortResultName;
        }

        return $this;
    }

    public function hasTableOfContents(): bool
    {
        return $this->hasTableOfContents;
    }

    public function disableHasTableOfContents(): TestsConfiguration
    {
        $this->hasTableOfContents = false;

        return $this;
    }
}