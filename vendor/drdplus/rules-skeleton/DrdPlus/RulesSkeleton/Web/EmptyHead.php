<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\FrontendSkeleton\Web\Head;

class EmptyHead extends Head
{
    public function getHeadString(): string
    {
        return '';
    }

}