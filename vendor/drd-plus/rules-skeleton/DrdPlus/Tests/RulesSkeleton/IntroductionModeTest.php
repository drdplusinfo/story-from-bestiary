<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\FrontendSkeleton\Partials\AbstractContentTest;
use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;

class IntroductionModeTest extends AbstractContentTest
{
    use Partials\AbstractContentTestTrait;

    /**
     * @test
     */
    public function I_can_get_introduction_only(): void
    {
        $documents = [
            'standard' => $this->getHtmlDocument(['show' => 'introduction']),
            'dev' => $this->getRulesForDevHtmlDocument('introduction')
        ];
        /**
         * @var string $mode
         * @var HTMLDocument $document
         */
        foreach ($documents as $mode => $document) {
            self::assertGreaterThan(0, $document->children->count());
            $body = $document->body;
            $bodyChildren = [];
            foreach ($body->children as $bodyChild) {
                if (\trim($bodyChild->innerHTML) !== '') {
                    $bodyChildren[] = $bodyChild;
                }
            }
            if (!$this->getTestsConfiguration()->hasIntroduction()) {
                self::assertEmpty($bodyChildren, 'No introduction expected according to tests config');
                continue;
            }
            self::assertNotEmpty($bodyChildren, 'No introduction found');
            foreach ($bodyChildren as $bodyChild) {
                self::assertTrue(
                    $bodyChild->classList->contains('introduction')
                    || $bodyChild->classList->contains('background-image')
                    || $bodyChild->classList->contains('quote')
                    || $bodyChild->classList->contains('hidden')
                    || $bodyChild->nodeName === 'img',
                    "Only an element with classes 'introduction', 'background-image' and 'quote' or the <img> element is expected in {$mode} mode, got : " . $bodyChild->outerHTML
                );
            }
            self::assertCount(
                0,
                $body->getElementsByClassName('generic'),
                "Class 'generic' would be already hidden id mode='$mode' and show='introduction'."
            );
            if ($mode === 'standard') {
                self::assertGreaterThan(
                    0,
                    $body->getElementsByTagName('img')->count(),
                    "Expected some image in mode='$mode' and show='introduction'"
                );
            } else {
                $imagesList = $body->getElementsByTagName('img');
                $images = [];
                foreach ($imagesList as $image) {
                    $images[] = $image;
                }
                self::assertCount(
                    0,
                    $images,
                    "No image expected in mode='$mode' and show='introduction', got images\n"
                    . \implode(
                        "\n",
                        \array_map(
                            function (Element $image) {
                                return $image->outerHTML;
                            },
                            $images
                        )
                    )
                );
            }
            self::assertGreaterThan(
                0,
                $body->getElementsByClassName('background-image')->count(),
                "Background image should not be removed in mode='$mode' and show='introduction'"
            );
        }
    }

    /**
     * @test
     */
    public function Every_introduction_is_direct_child_of_body(): void
    {
        $html = $this->getHtmlDocument(['show' => 'introduction']);
        self::assertGreaterThan(0, $html->children->count());
        $body = $html->body;
        self::assertGreaterThan(0, $body->children->length, 'No introduction found');
        foreach ($body->children as $child) {
            $this->guardNoChildIntroduction($child);
        }
    }

    public function guardNoChildIntroduction(Element $child): void
    {
        foreach ($child->children as $grandChild) {
            self::assertFalse(
                $grandChild->classList->contains('introduction'),
                'A grand-child should NOT have "introduction" class: ' . $grandChild->outerHTML
            );
            $this->guardNoChildIntroduction($grandChild);
        }
    }

    /**
     * @test
     */
    public function I_see_only_single_delimiter_of_blocks(): void
    {
        $content = $this->getContent(['show' => 'introduction']);
        self::assertNotRegExp(
            '~(\s*<img [^>]*class="delimiter"[^>]*>){2,}~',
            $content,
            'Only single delimiter expected "in a row"'
        );
    }
}