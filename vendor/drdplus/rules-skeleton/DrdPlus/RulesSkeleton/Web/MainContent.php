<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\HtmlHelper;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\BodyInterface;
use Granam\WebContentBuilder\Web\Content;
use Granam\WebContentBuilder\Web\HeadInterface;

abstract class MainContent extends Content
{
    /** @var HtmlHelper */
    protected $htmlHelper;

    public function __construct(HtmlHelper $htmlHelper, HeadInterface $head, BodyInterface $body)
    {
        parent::__construct($htmlHelper, $head, $body);
        $this->htmlHelper = $htmlHelper;
    }

    protected function buildHtmlDocument(string $content): HtmlDocument
    {
        $htmlDocument = new HtmlDocument($content);
        $htmlDocument->body->classList->add('container');
        $this->solveIds($htmlDocument);
        $this->solveLinks($htmlDocument);
        $this->htmlHelper->injectIframesWithRemoteTables($htmlDocument);
        $this->htmlHelper->resolveDisplayMode($htmlDocument);

        return $htmlDocument;
    }

    private function solveIds(HtmlDocument $htmlDocument): void
    {
        $this->htmlHelper->addIdsToHeadings($htmlDocument);
        $this->htmlHelper->addIdsToTables($htmlDocument);
        $this->htmlHelper->replaceDiacriticsFromIds($htmlDocument);
        $this->htmlHelper->addAnchorsToIds($htmlDocument);
        $this->htmlHelper->replaceDiacriticsFromAnchorHashes($htmlDocument);
    }

    private function solveLinks(HtmlDocument $htmlDocument): void
    {
        $this->htmlHelper->externalLinksTargetToBlank($htmlDocument);
        $this->htmlHelper->prepareSourceCodeLinks($htmlDocument);
        $this->htmlHelper->markExternalLinksByClass($htmlDocument);
        $this->htmlHelper->addVersionHashToAssets($htmlDocument);
        if (!$this->htmlHelper->isInProduction()) {
            $this->htmlHelper->makeExternalDrdPlusLinksLocal($htmlDocument);
        }
    }
}