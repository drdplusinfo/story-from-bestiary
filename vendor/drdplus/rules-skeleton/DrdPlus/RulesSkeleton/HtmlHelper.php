<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\FrontendSkeleton\HtmlDocument;

/**
 * @method static HtmlHelper createFromGlobals(Dirs $dirs)
 */
class HtmlHelper extends \DrdPlus\FrontendSkeleton\HtmlHelper
{
    public const AUTHORS_ID = 'autori';
    public const GOOGLE_ANALYTICS_ID = 'google_analytics_id';
    public const MENU_ID = 'menu';
    public const META_REDIRECT_ID = 'meta_redirect';

    public const CONTENT_CLASS = 'content';
    public const AUTHORS_CLASS = 'rules-authors';
    public const RULES_ORIGIN_CLASS = 'rules-origin';
    public const INVISIBLE_ID_CLASS = 'invisible-id';
    public const CALCULATION_CLASS = 'calculation';
    public const EXTERNAL_URL_CLASS = 'external-url';
    public const INTERNAL_URL_CLASS = 'internal-url';
    public const COVERED_BY_CODE_CLASS = 'covered-by-code';
    public const QUOTE_CLASS = 'quote';
    public const BACKGROUND_IMAGE_CLASS = 'background-image';
    public const GENERIC_CLASS = 'generic';
    public const NOTE_CLASS = 'note';
    public const EXCLUDED_CLASS = 'excluded';
    public const RULES_AUTHORS_CLASS = 'rules-authors';
    public const HIDDEN_CLASS = 'hidden';
    public const INVISIBLE_CLASS = 'invisible';
    public const DELIMITER_CLASS = 'delimiter';

    public const DATA_ORIGINAL_ID = 'data-original-id';
    public const DATA_CACHE_STAMP = 'data-cache-stamp';
    public const DATA_CACHED_AT = 'data-cached-at';

    /**
     * @param string $blockName
     * @param HtmlDocument $document
     * @return HtmlDocument
     */
    public function getDocumentWithBlock(string $blockName, HtmlDocument $document): HtmlDocument
    {
        $blockParts = $document->getElementsByClassName('block-' . $blockName);
        $block = '';
        foreach ($blockParts as $blockPart) {
            $block .= $blockPart->outerHTML;
        }
        $documentWithBlock = clone $document;
        $documentWithBlock->body->innerHTML = $block;

        return $documentWithBlock;
    }
}