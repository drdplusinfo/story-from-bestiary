<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\FrontendSkeleton\Web\WebFiles;

class PassBody extends \DrdPlus\FrontendSkeleton\Web\Body
{
    /** @var Pass */
    private $pass;

    public function __construct(WebFiles $webFiles, Pass $pass)
    {
        parent::__construct($webFiles);
        $this->pass = $pass;
    }

    public function getBodyString(): string
    {
        return <<<HTML
<div class="main pass">
  <div class="background-image"></div>
  {$this->pass->getPassString()}
</div>
HTML;
    }
}