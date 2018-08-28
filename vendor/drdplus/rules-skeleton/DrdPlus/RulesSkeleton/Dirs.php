<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class Dirs extends \DrdPlus\FrontendSkeleton\Dirs
{

    /** @var bool */
    protected $allowAccessToWebFiles;

    protected function populateSubRoots(string $documentRoot): void
    {
        parent::populateSubRoots($documentRoot);
        $this->genericPartsRoot = __DIR__ . '/../../parts/rules-skeleton';
        $this->allowAccessToWebFiles = false;
    }

    public function isAllowedAccessToWebFiles(): bool
    {
        return $this->allowAccessToWebFiles;
    }

    public function allowAccessToWebFiles(): void
    {
        $this->allowAccessToWebFiles = true;
    }

    public function getVersionWebRoot(string $forVersion): string
    {
        if ($this->isAllowedAccessToWebFiles()) {
            return parent::getVersionWebRoot($forVersion);
        }

        return \file_exists($this->getVendorRoot() . '/drdplus/rules-skeleton/web/pass')
            ? $this->getVendorRoot() . '/drdplus/rules-skeleton/web/pass'
            : __DIR__ . '/../../web/pass';
    }
}