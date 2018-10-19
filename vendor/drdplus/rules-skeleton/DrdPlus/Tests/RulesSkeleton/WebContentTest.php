<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;
use Gt\Dom\Element;
use Gt\Dom\Node;

class WebContentTest extends AbstractContentTest
{

    /**
     * @test
     */
    public function Every_plus_after_2d6_is_upper_indexed(): void
    {
        self::assertSame(
            0,
            \preg_match(
                '~.{0,10}2k6\s*(?!<span class="upper-index">\+</span>).{0,20}\+~',
                $this->getContentWithoutIds(),
                $matches
            ),
            \var_export($matches, true)
        );
    }

    private function getContentWithoutIds(): string
    {
        $document = clone $this->getHtmlDocument();
        /** @var Element $body */
        $body = $document->getElementsByTagName('body')[0];
        $this->removeIds($body);

        return $document->saveHTML();
    }

    private function removeIds(Element $element): void
    {
        if ($element->hasAttribute('id')) {
            $element->removeAttribute('id');
        }
        foreach ($element->children as $child) {
            $this->removeIds($child);
        }
    }

    /**
     * @test
     */
    public function Every_registered_trademark_and_trademark_symbols_are_upper_indexed(): void
    {
        self::assertSame(
            0,
            \preg_match(
                '~.{0,10}(?:(?<!<span class="upper-index">)\s*[®™]|[®™]\s*(?!</span>).{0,10})~u',
                $this->getContent(),
                $matches
            ),
            \var_export($matches, true)
        );
    }

    /**
     * @test
     */
    public function I_can_navigate_to_every_heading_by_expected_anchor(): void
    {
        $htmlDocument = $this->getHtmlDocument();
        $totalHeadingsCount = 0;
        for ($tagLevel = 1; $tagLevel <= 6; $tagLevel++) {
            $headings = $htmlDocument->getElementsByTagName('h' . $tagLevel);
            $totalHeadingsCount += \count($headings);
            foreach ($headings as $heading) {
                $id = $heading->id;
                self::assertNotEmpty($id, 'Expected some ID for ' . $heading->outerHTML);
                $anchors = $heading->getElementsByTagName('a');
                self::assertCount(1, $anchors, 'Expected single anchor in ' . $heading->outerHTML);
                $anchor = $anchors->current();
                $href = $anchor->getAttribute('href');
                self::assertNotEmpty($href, 'Expected some href of anchor in ' . $heading->outerHTML);
                self::assertSame('#' . $id, $href, 'Expected anchor pointing to the heading ID');
                $headingText = '';
                foreach ($anchor->childNodes as $childNode) {
                    /** @var Node $childNode */
                    if ($childNode->nodeType === \XML_TEXT_NODE) {
                        $headingText = $childNode->textContent;
                        break;
                    }
                }
                self::assertNotEmpty($headingText, 'Expected some human name for heading ' . $heading->outerHTML);
                $idFromText = HtmlHelper::toId($headingText);
                self::assertSame($id, $idFromText, "Expected different ID as created from '$headingText' heading");
            }
        }
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasHeadings()) {
            self::assertSame(0, $totalHeadingsCount, 'No headings expected due to tests configurationF');
        } else {
            self::assertGreaterThan(0, $totalHeadingsCount, 'Expected some headings');
        }
    }

    /**
     * @test
     */
    public function Authors_got_heading(): void
    {
        $authorsHeading = $this->getHtmlDocument()->getElementById(HtmlHelper::AUTHORS_ID);
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasAuthors()) {
            self::assertEmpty($authorsHeading, 'Authors are not expected');

            return;
        }
        self::assertNotEmpty($authorsHeading, 'Authors should have h3 heading');
        self::assertSame(
            'h3',
            $authorsHeading->nodeName,
            'Authors heading should be h3, but is ' . $authorsHeading->nodeName
        );
    }

    /**
     * @test
     */
    public function Authors_are_mentioned(): void
    {
        $body = $this->getHtmlDocument()->body;
        $rulesAuthors = $body->getElementsByClassName(HtmlHelper::AUTHORS_CLASS);
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasAuthors()) {
            self::assertCount(0, $rulesAuthors, 'No rules authors expected due to tests configuration');

            return;
        }
        self::assertCount(
            1,
            $rulesAuthors,
            "Expected one '" . HtmlHelper::AUTHORS_CLASS . "' HTML class in rules content, got {$rulesAuthors->count()} of them"
        );
        $rulesAuthors = $rulesAuthors->current();
        self::assertNotEmpty(\trim($rulesAuthors->textContent), 'Expected some content of rules authors');
    }

    /**
     * @test
     */
    public function Page_has_title(): void
    {
        $expectedPageTitle = $this->getTestsConfiguration()->getExpectedPageTitle();
        $currentPageTitle = $this->getCurrentPageTitle();
        self::assertNotEmpty($currentPageTitle, 'Page title is missing on page');
        self::assertSame($expectedPageTitle, $currentPageTitle, 'Current page title differs from expected one');
        $rulesTitle = $this->getCurrentPageTitle($this->getHtmlDocument());
        self::assertNotEmpty($rulesTitle, 'Rules title is missing');
        $passTitle = $this->getCurrentPageTitle($this->getPassDocument());
        self::assertNotEmpty($passTitle, 'Pass title is missing');
        self::assertSame($rulesTitle, $passTitle, 'Rules and pass titles should be the same');
    }

}