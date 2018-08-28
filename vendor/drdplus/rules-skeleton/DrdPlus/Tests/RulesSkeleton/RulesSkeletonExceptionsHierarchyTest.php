<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\FrontendSkeleton\FrontendSkeletonExceptionsHierarchyTest;

class RulesSkeletonExceptionsHierarchyTest extends FrontendSkeletonExceptionsHierarchyTest
{
    protected function getRootNamespace(): string
    {
        return \str_replace('Tests\\', '', __NAMESPACE__);
    }

}