<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\FrontendSkeleton\FrontendSkeletonExceptionsHierarchyTest;

class RulesSkeletonExceptionsHierarchyTest extends FrontendSkeletonExceptionsHierarchyTest
{
    protected function getRootNamespace(): string
    {
        return \str_replace('Tests\\', '', __NAMESPACE__);
    }

}