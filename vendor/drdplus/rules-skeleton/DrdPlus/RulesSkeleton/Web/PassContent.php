<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use Granam\WebContentBuilder\HtmlHelper;
use Granam\WebContentBuilder\Web\HeadInterface;

class PassContent extends MainContent
{
    public function __construct(HtmlHelper $htmlHelper, HeadInterface $head, PassBody $passBody)
    {
        parent::__construct($htmlHelper, $head, $passBody);
    }

}