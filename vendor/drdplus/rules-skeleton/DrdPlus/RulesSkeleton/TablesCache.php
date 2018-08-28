<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\FrontendSkeleton\Cache;

class TablesCache extends Cache
{
    protected function getCachePrefix(): string
    {
        return 'tables';
    }

}