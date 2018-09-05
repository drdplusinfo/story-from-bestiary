<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class Dirs extends \DrdPlus\FrontendSkeleton\Dirs
{
    public function getPdfRoot(): string
    {
        return $this->getDocumentRoot() . '/pdf';
    }
}