<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

class EmptyHead extends Head
{
    public function getHeadString(): string
    {
        return '';
    }

}