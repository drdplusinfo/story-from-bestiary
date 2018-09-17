<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\Tests\FrontendSkeleton\Exceptions\InvalidUrl;
use DrdPlus\Tests\RulesSkeleton\Partials\TestsConfigurationReader;

class TestsConfiguration extends \DrdPlus\Tests\FrontendSkeleton\TestsConfiguration implements TestsConfigurationReader
{
    public const LICENCE_BY_ACCESS = '*by access*';
    public const LICENCE_MIT = 'MIT';
    public const LICENCE_PROPRIETARY = 'proprietary';

    // every setting SHOULD be strict (expecting instead of ignoring)

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
        parent::__construct($localUrl);
    }

    protected function isLocalLinkAccessible(string $localUrl): bool
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