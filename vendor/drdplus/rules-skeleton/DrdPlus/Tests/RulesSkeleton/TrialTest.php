<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\FrontendController;
use DrdPlus\RulesSkeleton\RulesController;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTestTrait;

class TrialTest extends \DrdPlus\Tests\FrontendSkeleton\TrialTest
{
    use AbstractContentTestTrait;

    protected function createController(): FrontendController
    {
        return new RulesController($this->createConfiguration(), $this->createHtmlHelper());
    }
}