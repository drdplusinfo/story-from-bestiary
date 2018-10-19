<?php
namespace DrdPlus\RulesSkeleton\Partials;

interface CurrentMinorVersionProvider
{
    public function getCurrentMinorVersion(): string;
}