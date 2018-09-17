<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;

class WebContentTest extends \DrdPlus\Tests\FrontendSkeleton\WebContentTest
{
    use Partials\AbstractContentTestTrait;

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
        parent::Page_has_title();
        $rulesTitle = $this->getCurrentPageTitle($this->getHtmlDocument());
        self::assertNotEmpty($rulesTitle, 'Rules title is missing');
        $passTitle = $this->getCurrentPageTitle($this->getPassDocument());
        self::assertNotEmpty($passTitle, 'Pass title is missing');
        self::assertSame($rulesTitle, $passTitle, 'Rules and pass titles should be the same');
    }

}