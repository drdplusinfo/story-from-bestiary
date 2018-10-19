<?php
namespace DrdPlus\RulesSkeleton\Partials;

interface CurrentPatchVersionProvider
{
    public function getCurrentPatchVersion(): string;
}