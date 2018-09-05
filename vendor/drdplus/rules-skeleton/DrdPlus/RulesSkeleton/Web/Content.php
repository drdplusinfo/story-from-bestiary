<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\FrontendSkeleton\Cache;
use DrdPlus\FrontendSkeleton\Redirect;
use DrdPlus\FrontendSkeleton\Web\Body;
use DrdPlus\FrontendSkeleton\Web\Head;
use DrdPlus\FrontendSkeleton\Web\Menu;
use DrdPlus\FrontendSkeleton\WebVersions;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\ServicesContainer;

/**
 * @method ServicesContainer getServicesContainer
 */
class Content extends \DrdPlus\FrontendSkeleton\Web\Content
{
    public const PDF = 'pdf';
    public const TABLES = 'tables';
    public const PASS = 'pass';
    public const PASSED = 'passed';

    /** @var string */
    private $contentType;

    public function __construct(
        HtmlHelper $htmlHelper,
        WebVersions $webVersions,
        Head $head,
        Menu $menu,
        Body $body,
        Cache $cache,
        string $contentType,
        ?Redirect $redirect
    )
    {
        parent::__construct($htmlHelper, $webVersions, $head, $menu, $body, $cache, $redirect);
        $this->contentType = $contentType;
    }

    public function getStringContent(): string
    {
        if ($this->containsPdf()) {
            return $this->getBody()->getBodyString();
        }

        return parent::getStringContent();
    }

    public function containsPdf(): bool
    {
        return $this->contentType === self::PDF;
    }

    public function containsTables(): bool
    {
        return $this->contentType === self::TABLES;
    }

    public function containsPass(): bool
    {
        return $this->contentType === self::PASS;
    }

    public function containsPassed(): bool
    {
        return $this->contentType === self::PASSED;
    }

}