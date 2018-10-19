<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class Dirs extends StrictObject
{
    /** @var string */
    private $documentRoot;
    /** @var string */
    private $vendorRoot;
    /** @var string */
    private $partsRoot;
    /** @var string */
    private $cssRoot;
    /** @var string */
    private $jsRoot;
    /** @var string */
    private $dirForVersions;
    /** @var string */
    private $cacheRoot;
    /** @var string */
    private $pdfRoot;

    public function __construct(string $documentRoot)
    {
        $this->documentRoot = $documentRoot;
        $this->populateSubRoots($documentRoot);
    }

    private function populateSubRoots(string $documentRoot): void
    {
        $this->vendorRoot = $documentRoot . '/vendor';
        $this->partsRoot = $documentRoot . '/parts';
        $this->cssRoot = $documentRoot . '/css';
        $this->jsRoot = $documentRoot . '/js';
        $this->dirForVersions = $documentRoot . '/versions';
        $this->cacheRoot = $documentRoot . '/cache/' . \PHP_SAPI;
        $this->pdfRoot = $documentRoot . '/pdf';
    }

    /**
     * @return string
     */
    public function getDocumentRoot(): string
    {
        return $this->documentRoot;
    }

    /**
     * @return string
     */
    public function getVendorRoot(): string
    {
        return $this->vendorRoot;
    }

    /**
     * @return string
     */
    public function getCssRoot(): string
    {
        return $this->cssRoot;
    }

    /**
     * @return string
     */
    public function getJsRoot(): string
    {
        return $this->jsRoot;
    }

    /**
     * @return string
     */
    public function getDirForVersions(): string
    {
        return $this->dirForVersions;
    }

    /**
     * @return string
     */
    public function getCacheRoot(): string
    {
        return $this->cacheRoot;
    }

    public function getVersionRoot(string $forVersion): string
    {
        return $this->getDirForVersions() . '/' . $forVersion;
    }

    public function getVersionWebRoot(string $forVersion): string
    {
        return $this->getVersionRoot($forVersion) . '/web';
    }

    public function getPdfRoot(): string
    {
        return $this->pdfRoot;
    }
}