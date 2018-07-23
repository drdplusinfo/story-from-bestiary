<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton\Exceptions;

use DrdPlus\Tests\FrontendSkeleton\Exceptions\InvalidUrl;

class InvalidLocalUrl extends InvalidUrl implements Logic
{

}