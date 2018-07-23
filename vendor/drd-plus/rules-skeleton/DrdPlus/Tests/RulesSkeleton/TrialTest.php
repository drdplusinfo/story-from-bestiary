<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\FrontendController;
use DrdPlus\RulesSkeleton\Dirs;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\RulesController;

class TrialTest extends \DrdPlus\Tests\FrontendSkeleton\TrialTest
{
    protected function createController(): FrontendController
    {
        $dirs = new Dirs($this->getMasterDocumentRoot(), $this->getDocumentRoot());

        return new RulesController('Google analytics ID foo', new HtmlHelper($dirs, true, false, false, false), $dirs);
    }
}