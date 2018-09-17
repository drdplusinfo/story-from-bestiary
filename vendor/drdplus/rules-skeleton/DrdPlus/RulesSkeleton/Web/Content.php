<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\FrontendSkeleton\Cache;
use DrdPlus\FrontendSkeleton\HtmlDocument;
use DrdPlus\FrontendSkeleton\Web\Body;
use DrdPlus\FrontendSkeleton\Web\Head;
use DrdPlus\FrontendSkeleton\Web\Menu;
use DrdPlus\FrontendSkeleton\WebVersions;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Redirect;
use DrdPlus\RulesSkeleton\ServicesContainer;

/**
 * @method ServicesContainer getServicesContainer
 */
class Content extends \DrdPlus\FrontendSkeleton\Web\Content
{
    public const PDF = 'pdf';
    public const PASS = 'pass';

    /** @var Redirect|null */
    private $redirect;

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
        parent::__construct($htmlHelper, $webVersions, $head, $menu, $body, $cache, $contentType);
        $this->redirect = $redirect;
    }

    public function getStringContent(): string
    {
        if ($this->containsPdf()) {
            return $this->getBody()->getBodyString();
        }
        $content = parent::getStringContent();

        // has to be AFTER cache as we do not want to cache it
        return $this->injectRedirectIfAny($content);
    }

    protected function injectRedirectIfAny(string $content): string
    {
        if (!$this->getRedirect()) {
            return $content;
        }
        $cachedDocument = new HtmlDocument($content);
        $meta = $cachedDocument->createElement('meta');
        $meta->setAttribute('http-equiv', 'Refresh');
        $meta->setAttribute('content', $this->getRedirect()->getAfterSeconds() . '; url=' . $this->getRedirect()->getTarget());
        $meta->setAttribute('id', 'meta_redirect');
        $cachedDocument->head->appendChild($meta);

        return $cachedDocument->saveHTML();
    }

    protected function getRedirect(): ?Redirect
    {
        return $this->redirect;
    }

    public function containsPdf(): bool
    {
        return $this->getContentType() === self::PDF;
    }

    public function containsPass(): bool
    {
        return $this->getContentType() === self::PASS;
    }

}