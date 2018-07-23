<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton\Partials;

trait DirsForTestsTrait
{
    use \DrdPlus\Tests\FrontendSkeleton\Partials\DirsForTestsTrait;

    public function getWebRoot(): string
    {
        return $this->getDocumentRoot() . '/web/passed';
    }

    protected function getGenericPartsRoot(): string
    {
        return __DIR__ . '/../../../../parts/rules-skeleton';
    }
}