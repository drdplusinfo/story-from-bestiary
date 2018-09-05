<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\FrontendSkeleton\Web\Menu;

class EmptyMenu extends Menu
{
    public function getMenuString(): string
    {
        return '';
    }

}