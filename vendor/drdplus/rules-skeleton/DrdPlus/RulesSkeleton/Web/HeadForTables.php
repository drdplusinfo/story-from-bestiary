<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\FrontendSkeleton\Web\Head;

class HeadForTables extends Head
{
    protected function getPageTitle(): string
    {
        return 'Tabulky pro ' . $this->getConfiguration()->getWebName();
    }
}