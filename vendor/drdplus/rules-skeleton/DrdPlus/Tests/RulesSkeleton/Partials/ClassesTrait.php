<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Partials;

use DrdPlus\RulesSkeleton\Configuration;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\RulesController;

trait ClassesTrait
{
    use ClassesTrait;

    /**
     * @return string|Configuration
     */
    protected function getConfigurationClass(): string
    {
        return Configuration::class;
    }

    /**
     * @return string|Request
     */
    protected function getRequestClass(): string
    {
        return Request::class;
    }

    /**
     * @return string|RulesController
     */
    protected function getControllerClass(): string
    {
        return RulesController::class;
    }

}