<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;
use Granam\String\StringTools;
use Gt\Dom\Element;

/**
 * @method TestsConfiguration getTestsConfiguration
 */
class AnchorsTest extends \DrdPlus\Tests\FrontendSkeleton\AnchorsTest
{

    use Partials\AbstractContentTestTrait;

    protected function getPostDataToFetchContent(bool $isDrdPlus): array
    {
        return $isDrdPlus ? ['trial' => 1] : [];
    }

    protected function getExternalAnchors(): array
    {
        $externalAnchors = parent::getExternalAnchors();
        $externalAnchors[] = $this->getTestsConfiguration()->getPublicUrl();

        return $externalAnchors;
    }

    /**
     * @test
     */
    public function All_anchors_point_to_syntactically_valid_links(): void
    {
        foreach ($this->getLicenceSwitchers() as $licenceSwitcher) {
            $licenceSwitcher();
            parent::All_anchors_point_to_syntactically_valid_links();
            $invalidAnchors = $this->parseInvalidAnchors($this->getPassContent());
            self::assertCount(
                0,
                $invalidAnchors,
                'Some anchors from ownership confirmation points to invalid links ' . \implode(',', $invalidAnchors)
            );
        }
    }

    /**
     * @test
     */
    public function I_can_go_directly_to_eshop_item_page(): void
    {
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->canBeBoughtOnEshop()) {
            self::assertFalse(false);

            return;
        }
        $eshopUrl = $this->getConfiguration()->getEshopUrl();
        self::assertRegExp('~^https://obchod\.altar\.cz/[^/]+\.html$~', $eshopUrl);
        self::assertSame($eshopUrl, $this->getLinkToEshopFromLinkToOrigin()->getAttribute('href'), 'Expected different link to e-shop');
    }

    private function getLinkToEshopFromLinkToOrigin(): Element
    {
        $body = $this->getHtmlDocument()->body;
        $origins = $body->getElementsByClassName(HtmlHelper::RULES_ORIGIN_CLASS);
        self::assertCount(
            1,
            $origins,
            "Expected one '" . HtmlHelper::RULES_ORIGIN_CLASS . "' class, got {$origins->count()} of them"
        );
        $origin = $origins->current();
        $rulesLinks = $origin->getElementsByTagName('a');
        self::assertNotEmpty($rulesLinks, "Missing a link to rules in '" . HtmlHelper::RULES_ORIGIN_CLASS . "'");
        self::assertCount(1, $rulesLinks);

        return $rulesLinks->current();
    }

    /**
     * @test
     */
    public function Links_to_vukogvazd_uses_https(): void
    {
        $linksToVukogvazd = [];
        foreach ($this->getExternalAnchors() as $link) {
            if (\strpos($link, 'vukogvazd.cz')) {
                $linksToVukogvazd[] = $link;
            }
        }
        if (\count($linksToVukogvazd) === 0) {
            self::assertFalse(false, 'No links to Vukogvazd have been found');
        } else {
            foreach ($linksToVukogvazd as $linkToVukogvazd) {
                self::assertStringStartsWith('https', $linkToVukogvazd, "Every link to vukogvazd should be via https: '$linkToVukogvazd'");
            }
        }
    }

    /**
     * @test
     */
    public function Character_sheet_comes_from_drdplus_info(): void
    {
        $linksToCharacterSheet = [];
        foreach ($this->getExternalAnchors() as $link) {
            $link = HtmlHelper::turnToLocalLink($link);
            if (\strpos($link, 'charakternik.pdf')) {
                $linksToCharacterSheet[] = $link;
            }
        }
        if (!$this->getTestsConfiguration()->hasCharacterSheet()) {
            self::assertCount(0, $linksToCharacterSheet, 'No links to PDF character sheet expected');

            return;
        }
        self::assertGreaterThan(0, \count($linksToCharacterSheet), 'PDF character sheet is missing');
        $expectedOriginalLink = 'https://www.drdplus.info/pdf/charakternik.pdf';
        $expectedLink = HtmlHelper::turnToLocalLink($expectedOriginalLink);
        foreach ($linksToCharacterSheet as $linkToCharacterSheet) {
            self::assertSame(
                $expectedLink,
                $linkToCharacterSheet,
                "Every link to PDF character sheet should lead to $expectedOriginalLink"
            );
        }
    }

    /**
     * @test
     */
    public function Journal_comes_from_drdplus_info(): void
    {
        $linksToJournal = [];
        foreach ($this->getExternalAnchors() as $link) {
            $link = HtmlHelper::turnToLocalLink($link);
            if (\preg_match('~/denik_\w+\.pdf$~', $link)) {
                $linksToJournal[] = $link;
            }
        }
        if (!$this->getTestsConfiguration()->hasLinksToJournals() && !$this->getTestsConfiguration()->hasLinkToSingleJournal()) {
            self::assertCount(0, $linksToJournal, 'No links to PDF journal expected');

            return;
        }
        self::assertGreaterThan(0, \count($linksToJournal), 'PDF journals are missing');
        if ($this->isSkeletonChecked() || !$this->getTestsConfiguration()->hasLinkToSingleJournal()) {
            foreach ($linksToJournal as $linkToJournal) {
                self::assertRegExp(
                    '~^' . \preg_quote(HtmlHelper::turnToLocalLink('https://www.drdplus.info'), '~') . '/pdf/deniky/denik_\w+[.]pdf$~',
                    $linkToJournal,
                    'Every link to PDF journal should lead to https://www.drdplus.info/pdf/deniky/denik_foo.pdf'
                );
            }

            return;
        }
        self::assertTrue($this->getTestsConfiguration()->hasLinksToJournals());
        $expectedOriginalLink = $this->getExpectedLinkToJournal();
        $expectedLink = HtmlHelper::turnToLocalLink($expectedOriginalLink);
        foreach ($linksToJournal as $linkToJournal) {
            self::assertSame(
                $expectedLink,
                $linkToJournal,
                "Every link to PDF journal should lead to $expectedOriginalLink"
            );
        }
    }

    private function getExpectedLinkToJournal(): string
    {
        return 'https://www.drdplus.info/pdf/deniky/denik_' . StringTools::toConstantLikeValue($this->getProfessionName()) . '.pdf';
    }

    private function getProfessionName(): string
    {
        $currentPageTitle = $this->getCurrentPageTitle();
        self::assertSame(
            1,
            \preg_match('~\s(?<lastWord>\w+)$~u', $currentPageTitle, $matches),
            "No last word found in '$currentPageTitle'"
        );
        $lastWord = $matches['lastWord'];

        return \rtrim($lastWord, 'aeiouy');
    }

    /**
     * @test
     */
    public function Buttons_should_not_have_links_inside(): void
    {
        foreach ($this->getLicenceSwitchers() as $licenceSwitcher) {
            $licenceSwitcher();
            parent::Buttons_should_not_have_links_inside();
        }
    }
}