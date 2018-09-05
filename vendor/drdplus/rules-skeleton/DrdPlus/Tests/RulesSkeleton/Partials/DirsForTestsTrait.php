<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Partials;

use DrdPlus\RulesSkeleton\Dirs;

trait DirsForTestsTrait
{
    use \DrdPlus\Tests\FrontendSkeleton\Partials\DirsForTestsTrait;

    /**
     * @param string|null $documentRoot
     * @return \DrdPlus\FrontendSkeleton\Dirs|Dirs
     */
    protected function createDirs(string $documentRoot = null): \DrdPlus\FrontendSkeleton\Dirs
    {
        return new Dirs($documentRoot ?? $this->getDocumentRoot());
    }

    protected function getSkeletonDocumentRoot(): string
    {
        if ($this->isSkeletonChecked()) {
            return $this->getDocumentRoot();
        }

        return $this->createDirs()->getVendorRoot() . '/drdplus/rules-skeleton';
    }
}