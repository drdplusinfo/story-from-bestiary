<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class Request extends \DrdPlus\FrontendSkeleton\Request
{
    public const PDF = 'pdf';

    public function getPathInfo(): string
    {
        return $_SERVER['PATH_INFO'] ?? '';
    }

    public function getQueryString(): string
    {
        return $_SERVER['QUERY_STRING'] ?? '';
    }

    public function isRequestedPdf(): bool
    {
        return $this->getQueryString() === self::PDF;
    }
}