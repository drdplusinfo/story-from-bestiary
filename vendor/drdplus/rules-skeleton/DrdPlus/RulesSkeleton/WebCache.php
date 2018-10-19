<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class WebCache extends Cache
{
    public function __construct(WebVersions $webVersions, Dirs $dirs, Request $request, Git $git, bool $isInProduction, string $cachePrefix = null)
    {
        parent::__construct($webVersions, $dirs, $request, $git, $isInProduction, $cachePrefix ?? 'page-' . \md5($dirs->getCacheRoot()));
    }
}