<?php
declare(strict_types=1);

namespace DrdPlus\FrontendSkeleton;

use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;
use Gt\Dom\Element;
use Gt\Dom\HTMLCollection;

class HtmlHelper extends StrictObject
{

    /**
     * Turn link into local version
     * @param string $link
     * @return string
     */
    public static function turnToLocalLink(string $link): string
    {
        return \preg_replace('~https?://((?:[^.]+[.])*)drdplus\.info~', 'http://$1drdplus.loc:88', $link);
    }

    public const INVISIBLE_ID_CLASS = 'invisible-id';
    public const CALCULATION_CLASS = 'calculation';
    public const DATA_ORIGINAL_ID = 'data-original-id';
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
    public const DELIMITER_CLASS = 'delimiter';

    /** @var Dirs */
    private $dirs;
    /** @var bool */
    private $inDevMode;
    /** @var bool */
    private $inForcedProductionMode;
    /** @var bool */
    private $shouldHideCovered;

    public static function createFromGlobals(Dirs $dirs): HtmlHelper
    {
        return new static(
            $dirs,
            !empty($_GET['mode']) && \strpos(\trim($_GET['mode']), 'dev') === 0,
            !empty($_GET['mode']) && \strpos(\trim($_GET['mode']), 'prod') === 0,
            !empty($_GET['hide']) && \strpos(\trim($_GET['hide']), 'cover') === 0
        );
    }

    /**
     * Turn link into local version
     * @param string $name
     * @return string
     * @throws \DrdPlus\FrontendSkeleton\Exceptions\NameToCreateHtmlIdFromIsEmpty
     */
    public static function toId(string $name): string
    {
        if ($name === '') {
            throw new Exceptions\NameToCreateHtmlIdFromIsEmpty('Expected some name to create HTML ID from');
        }

        return StringTools::toSnakeCaseId($name);
    }

    public function __construct(
        Dirs $dirs,
        bool $inDevMode,
        bool $inForcedProductionMode,
        bool $shouldHideCovered
    )
    {
        $this->dirs = $dirs;
        $this->inDevMode = $inDevMode;
        $this->inForcedProductionMode = $inForcedProductionMode;
        $this->shouldHideCovered = $shouldHideCovered;
    }

    /**
     * @param HtmlDocument $html
     */
    public function prepareSourceCodeLinks(HtmlDocument $html): void
    {
        if (!$this->inDevMode) {
            foreach ($html->getElementsByClassName('source-code-title') as $withSourceCode) {
                $withSourceCode->className = \str_replace('source-code-title', static::HIDDEN_CLASS, $withSourceCode->className);
                $withSourceCode->removeAttribute('data-source-code');
            }
        } else {
            foreach ($html->getElementsByClassName('source-code-title') as $withSourceCode) {
                $withSourceCode->appendChild($sourceCodeLink = new Element('a', 'source code'));
                $sourceCodeLink->setAttribute('class', 'source-code');
                $sourceCodeLink->setAttribute('href', $withSourceCode->getAttribute('data-source-code'));
            }
        }
    }

    /**
     * @param HtmlDocument $html
     */
    public function addIdsToTablesAndHeadings(HtmlDocument $html): void
    {
        $elementNames = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'th'];
        foreach ($elementNames as $elementName) {
            /** @var Element $headerCell */
            foreach ($html->getElementsByTagName($elementName) as $headerCell) {

                if ($headerCell->getAttribute('id')) {
                    continue;
                }
                if ($elementName === 'th' && \strpos(\trim($headerCell->textContent), 'Tabulka') === false) {
                    continue;
                }
                $id = false;
                /** @var \DOMNode $childNode */
                foreach ($headerCell->childNodes as $childNode) {
                    if ($childNode->nodeType === XML_TEXT_NODE) {
                        $id = \trim($childNode->nodeValue);
                        break;
                    }
                }
                if (!$id) {
                    continue;
                }
                $headerCell->setAttribute('id', $id);
            }
        }
    }

    public function replaceDiacriticsFromIds(HtmlDocument $html): void
    {
        $this->replaceDiacriticsFromChildrenIds($html->body->children);
    }

    private function replaceDiacriticsFromChildrenIds(HTMLCollection $children): void
    {
        foreach ($children as $child) {
            // recursion
            $this->replaceDiacriticsFromChildrenIds($child->children);
            $id = $child->getAttribute('id');
            if (!$id) {
                continue;
            }
            $idWithoutDiacritics = $this->unifyId($id);
            if ($idWithoutDiacritics === $id) {
                continue;
            }
            $child->setAttribute(self::DATA_ORIGINAL_ID, $id);
            $child->setAttribute('id', $this->sanitizeId($idWithoutDiacritics));
            $child->appendChild($invisibleId = new Element('span'));
            $invisibleId->setAttribute('id', $this->sanitizeId($id));
            $invisibleId->className = self::INVISIBLE_ID_CLASS;
        }
    }

    private function sanitizeId(string $id): string
    {
        return \str_replace('#', '_', $id);
    }

    private function unifyId(string $id): string
    {
        return StringTools::toSnakeCaseId($id);
    }

    public function replaceDiacriticsFromAnchorHashes(HtmlDocument $html): void
    {
        $this->replaceDiacriticsFromChildrenAnchorHashes($html->getElementsByTagName('a'));
    }

    private function replaceDiacriticsFromChildrenAnchorHashes(\Traversable $children): void
    {
        /** @var Element $child */
        foreach ($children as $child) {
            // recursion
            $this->replaceDiacriticsFromChildrenAnchorHashes($child->children);
            $href = $child->getAttribute('href');
            if (!$href) {
                continue;
            }
            $hashPosition = \strpos($href, '#');
            if ($hashPosition === false) {
                continue;
            }
            $hash = substr($href, $hashPosition + 1);
            if ($hash === '') {
                continue;
            }
            $hashWithoutDiacritics = $this->unifyId($hash);
            if ($hashWithoutDiacritics === $hash) {
                continue;
            }
            $hrefWithoutDiacritics = substr($href, 0, $hashPosition) . '#' . $hashWithoutDiacritics;
            $child->setAttribute('href', $hrefWithoutDiacritics);
        }
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @return HtmlDocument
     */
    public function addAnchorsToIds(HtmlDocument $htmlDocument): HtmlDocument
    {
        $this->addAnchorsToChildrenWithIds($htmlDocument->body->children);

        return $htmlDocument;
    }

    private function addAnchorsToChildrenWithIds(HTMLCollection $children): void
    {
        /** @var Element $child */
        foreach ($children as $child) {
            if (!\in_array($child->nodeName, ['a', 'button'], true)
                && $child->getAttribute('id')
                && $child->getElementsByTagName('a')->length === 0 // already have some anchors, skipp it to avoid wrapping them by another one
                && !$child->prop_get_classList()->contains(self::INVISIBLE_ID_CLASS)
            ) {
                $toMove = [];
                /** @var \DOMElement $grandChildNode */
                foreach ($child->childNodes as $grandChildNode) {
                    if (!\in_array($grandChildNode->nodeName, ['span', 'strong', 'b', 'i', '#text'], true)) {
                        break;
                    }
                    $toMove[] = $grandChildNode;
                }
                if (\count($toMove) > 0) {
                    $anchorToSelf = new Element('a');
                    $child->replaceChild($anchorToSelf, $toMove[0]); // pairs anchor with parent element
                    $anchorToSelf->setAttribute('href', '#' . $child->getAttribute('id'));
                    foreach ($toMove as $index => $item) {
                        $anchorToSelf->appendChild($item);
                    }
                }
            }
            // recursion
            $this->addAnchorsToChildrenWithIds($child->children);
        }
    }

    private function containsOnlyTextAndSpans(\DOMNode $element): bool
    {
        if (!$element->hasChildNodes()) {
            return true;
        }
        /** @var \DOMNode $childNode */
        foreach ($element->childNodes as $childNode) {
            if ($childNode->nodeName !== 'span' && $childNode->nodeType !== XML_TEXT_NODE) {
                return false;
            }
            if (!$this->containsOnlyTextAndSpans($childNode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param HtmlDocument $html
     */
    public function resolveDisplayMode(HtmlDocument $html): void
    {
        if ($this->inDevMode) {
            $this->removeImages($html->body);
        } else {
            $this->removeClassesAboutCodeCoverage($html->body);
        }
        if (!$this->inDevMode || !$this->shouldHideCovered) {
            return;
        }
        $classesToHide = [static::COVERED_BY_CODE_CLASS, static::QUOTE_CLASS, static::GENERIC_CLASS, static::NOTE_CLASS, static::EXCLUDED_CLASS, static::RULES_AUTHORS_CLASS];
        foreach ($classesToHide as $classToHide) {
            foreach ($html->getElementsByClassName($classToHide) as $nodeToHide) {
                $nodeToHide->className = \str_replace($classToHide, static::HIDDEN_CLASS, $nodeToHide->className);
            }
        }
    }

    private function removeImages(Element $html): void
    {
        do {
            $somethingRemoved = false;
            /** @var Element $image */
            foreach ($html->getElementsByTagName('img') as $image) {
                $image->remove();
                $somethingRemoved = true;
            }
        } while ($somethingRemoved); // do not know why, but some nodes are simply skipped on first removal so have to remove them again
    }

    private function removeClassesAboutCodeCoverage(Element $html): void
    {
        $classesToRemove = [static::COVERED_BY_CODE_CLASS, static::GENERIC_CLASS, static::EXCLUDED_CLASS];
        foreach ($html->children as $child) {
            foreach ($classesToRemove as $classToRemove) {
                $child->classList->remove($classToRemove);
            }
            // recursion
            $this->removeClassesAboutCodeCoverage($child);
        }
    }

    /**
     * @param HtmlDocument $html
     * @param array|string[] $requiredIds filter of required tables by their IDs
     * @return array|Element[]
     * @throws \DrdPlus\FrontendSkeleton\Exceptions\DuplicatedRequiredTableId
     */
    public function findTablesWithIds(HtmlDocument $html, array $requiredIds = []): array
    {
        $requiredIds = \array_unique($requiredIds);
        $lowerCasedRequiredIds = [];
        foreach ($requiredIds as $requiredId) {
            $unifiedRequiredId = $this->unifyId($requiredId);
            if (\array_key_exists($unifiedRequiredId, $lowerCasedRequiredIds)) {
                $requiredIdsAsString = \implode(',', $requiredIds);
                throw new Exceptions\DuplicatedRequiredTableId(
                    'IDs of tables are lower-cased and some required table IDs are same in lowercase: '
                    . "'{$requiredId}' => '{$unifiedRequiredId}' ($requiredIdsAsString)"
                );
            }
            $lowerCasedRequiredIds[$unifiedRequiredId] = $unifiedRequiredId;
        }
        $tablesWithIds = [];
        /** @var Element $table */
        foreach ($html->getElementsByTagName('table') as $table) {
            $unifiedExistingId = $this->unifyId($table->getAttribute('id') ?? '');
            if ($unifiedExistingId) {
                $tablesWithIds[$unifiedExistingId] = $table;
                continue;
            }
            $childId = $this->getChildId($table->children);
            if ($childId) {
                $tablesWithIds[$childId] = $table;
            }
        }
        if (!$requiredIds) {
            return $tablesWithIds; // all of them, no filter
        }

        return \array_intersect_key($tablesWithIds, $lowerCasedRequiredIds);
    }

    /**
     * @param HTMLCollection $children
     * @return string|bool
     */
    private function getChildId(HTMLCollection $children)
    {
        foreach ($children as $child) {
            if ($child->getAttribute('id')) {
                return $child->getAttribute('id');
            }
            $grandChildId = $this->getChildId($child->children);
            if ($grandChildId !== false) {
                return $grandChildId;
            }
        }

        return false;
    }

    public function markExternalLinksByClass(HtmlDocument $htmlDocument): HtmlDocument
    {
        /** @var Element $anchor */
        foreach ($htmlDocument->getElementsByTagName('a') as $anchor) {
            if (!$anchor->classList->contains(self::INTERNAL_URL_CLASS)
                && \preg_match('~^(https?:)?//[^#]~', $anchor->getAttribute('href') ?? '')
            ) {
                $anchor->classList->add(self::EXTERNAL_URL_CLASS);
            }
        }
        $htmlDocument->body->setAttribute('data-has-marked-external-urls', '1');

        return $htmlDocument;
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @throws \LogicException
     */
    public function externalLinksTargetToBlank(HtmlDocument $htmlDocument): void
    {
        if (!$this->hasMarkedExternalUrls($htmlDocument)) {
            throw new Exceptions\ExternalUrlsHaveToBeMarkedFirst(
                'External links have to be marked first, use markExternalLinksByClass method for that'
            );
        }
        /** @var Element $anchor */
        foreach ($htmlDocument->getElementsByClassName(self::EXTERNAL_URL_CLASS) as $anchor) {
            if (!$anchor->getAttribute('target')) {
                $anchor->setAttribute('target', '_blank');
            }
        }
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @return HtmlDocument
     * @throws \LogicException
     */
    public function injectIframesWithRemoteTables(HtmlDocument $htmlDocument): HtmlDocument
    {
        if (!$this->hasMarkedExternalUrls($htmlDocument)) {
            throw new Exceptions\ExternalUrlsHaveToBeMarkedFirst(
                'External links have to be marked first, use markExternalLinksByClass method for that'
            );
        }
        $remoteDrdPlusLinks = [];
        /** @var Element $anchor */
        foreach ($htmlDocument->getElementsByClassName(self::EXTERNAL_URL_CLASS) as $anchor) {
            if (!\preg_match('~(?:https?:)?//(?<host>[[:alpha:]]+\.drdplus\.info)/[^#]*#(?<tableId>tabulka_\w+)~', $anchor->getAttribute('href'), $matches)) {
                continue;
            }
            $remoteDrdPlusLinks[$matches['host']][] = $matches['tableId'];
        }
        if (\count($remoteDrdPlusLinks) === 0) {
            return $htmlDocument;
        }
        $body = $htmlDocument->body;
        foreach ($remoteDrdPlusLinks as $remoteDrdPlusHost => $tableIds) {
            $iFrame = $htmlDocument->createElement('iframe');
            $body->appendChild($iFrame);
            $iFrame->setAttribute('id', $remoteDrdPlusHost); // we will target that iframe via JS by remote host name
            $iFrame->setAttribute(
                'src',
                "https://{$remoteDrdPlusHost}/?tables=" . \htmlspecialchars(\implode(',', \array_unique($tableIds)))
            );
            $iFrame->setAttribute('style', 'display:none');
        }

        return $htmlDocument;
    }

    private function hasMarkedExternalUrls(HtmlDocument $htmlDocument): bool
    {
        return (bool)$htmlDocument->body->getAttribute('data-has-marked-external-urls');
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @return HtmlDocument
     */
    public function makeExternalDrdPlusLinksLocal(HtmlDocument $htmlDocument): HtmlDocument
    {
        if (!$this->hasMarkedExternalUrls($htmlDocument)) {
            throw new Exceptions\ExternalUrlsHaveToBeMarkedFirst(
                'External links have to be marked first, use markExternalLinksByClass method for that'
            );
        }
        foreach ($htmlDocument->getElementsByClassName(self::EXTERNAL_URL_CLASS) as $anchor) {
            $anchor->setAttribute('href', static::turnToLocalLink($anchor->getAttribute('href')));
        }
        foreach ($htmlDocument->getElementsByClassName(self::INTERNAL_URL_CLASS) as $anchor) {
            $anchor->setAttribute('href', static::turnToLocalLink($anchor->getAttribute('href')));
        }
        /** @var Element $iFrame */
        foreach ($htmlDocument->getElementsByTagName('iframe') as $iFrame) {
            $iFrame->setAttribute('src', static::turnToLocalLink($iFrame->getAttribute('src')));
            $iFrame->setAttribute('id', \str_replace('drdplus.info', 'drdplus.loc', $iFrame->getAttribute('id')));
        }

        return $htmlDocument;
    }

    public function addVersionHashToAssets(HtmlDocument $htmlDocument): void
    {
        $documentRoot = $this->dirs->getDocumentRoot();
        foreach ($htmlDocument->getElementsByTagName('img') as $image) {
            $this->addVersionToAsset($image, 'src', $documentRoot);
        }
        foreach ($htmlDocument->getElementsByTagName('link') as $link) {
            $this->addVersionToAsset($link, 'href', $documentRoot);
        }
        foreach ($htmlDocument->getElementsByTagName('script') as $script) {
            $this->addVersionToAsset($script, 'src', $documentRoot);
        }
    }

    private function addVersionToAsset(Element $element, string $attributeName, string $masterDocumentRoot): void
    {
        $link = $element->getAttribute($attributeName);
        if ($this->isLinkExternal($link)) {
            return;
        }
        $absolutePath = $this->getAbsolutePath($link, $masterDocumentRoot);
        $hash = $this->getFileHash($absolutePath);
        $element->setAttribute($attributeName, $link . '?version=' . \urlencode($hash));
    }

    private function isLinkExternal(string $link): bool
    {
        $urlParts = \parse_url($link);

        return !empty($urlParts['host']);
    }

    private function getAbsolutePath(string $relativePath, string $masterDocumentRoot): string
    {
        $relativePath = \ltrim($relativePath, '\\/');

        return $masterDocumentRoot . '/' . $relativePath;
    }

    private function getFileHash(string $fileName): string
    {
        return \md5_file($fileName) ?: (string)\time(); // time is a fallback
    }

    public function isInProduction(): bool
    {
        return $this->inForcedProductionMode || (\PHP_SAPI !== 'cli' && ($_SERVER['REMOTE_ADDR'] ?? null) !== '127.0.0.1');
    }
}