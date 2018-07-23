<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\Dirs;
use DrdPlus\RulesSkeleton\HtmlHelper;

class HtmlHelperTest extends \DrdPlus\Tests\FrontendSkeleton\HtmlHelperTest
{
    use Partials\AbstractContentTestTrait;

    /**
     * @test
     * @dataProvider providePublicAndLocalLinks
     * @param string $publicLink
     * @param string $expectedLocalLink
     */
    public function I_can_turn_public_link_to_local(string $publicLink, string $expectedLocalLink): void
    {
        self::assertSame($expectedLocalLink, HtmlHelper::turnToLocalLink($publicLink));
    }

    public function providePublicAndLocalLinks(): array
    {
        return [
            ['https://www.drdplus.info', 'http://www.drdplus.loc'],
            ['https://hranicar.drdplus.info', 'http://hranicar.drdplus.loc'],
        ];
    }

    /**
     * @test
     */
    public function I_can_get_html_document_with_block(): void
    {
        $blockNamesToExpectedContent = $this->getTestsConfiguration()->getBlockNamesToExpectedContent();
        if (!$blockNamesToExpectedContent) {
            self::assertEmpty($blockNamesToExpectedContent, 'No blocks to test');
        }
        $document = $this->getHtmlDocument();
        $dirs = new Dirs($this->getMasterDocumentRoot(), $this->getDocumentRoot());
        $htmlHelper = HtmlHelper::createFromGlobals($dirs);
        foreach ($blockNamesToExpectedContent as $blockName => $expectedContent) {
            $documentWithBlock = $htmlHelper->getDocumentWithBlock($blockName, $document);
            self::assertSame($expectedContent, $documentWithBlock->body->innerHTML);
        }
    }
}