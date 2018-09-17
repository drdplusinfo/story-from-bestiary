<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\Tests\FrontendSkeleton\Partials\AbstractContentTest;

class TableOfContentTest extends AbstractContentTest
{

    /**
     * @test
     */
    public function I_can_navigate_to_chapter_with_same_name_as_table_of_contents_mentions(): void
    {
        $contents = $this->getHtmlDocument()->getElementsByClassName('content');
        if (!$this->getTestsConfiguration()->hasTableOfContents()) {
            self::assertCount(0, $contents, 'No items of table of contents expected due to tests configuration');

            return;
        }
        self::assertNotEmpty($contents->count(), 'Expected some ".content" elements as items of a table of contents');
        foreach ($contents as $content) {
            $anchors = $content->getElementsByTagName('a');
            foreach ($anchors as $anchor) {
                $link = $anchor->getAttribute('href');
                if (\strpos($link, '#') !== 0) {
                    continue;
                }
                $name = $anchor->textContent;
                self::assertSame($link, '#' . HtmlHelper::toId($name));
            }
        }
    }
}