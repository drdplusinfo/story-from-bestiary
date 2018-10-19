<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

class PassBody extends Body
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