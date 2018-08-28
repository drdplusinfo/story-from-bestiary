<?php
/** @var \DrdPlus\RulesSkeleton\RulesController $controller */
/** @noinspection PhpIncludeInspection */
if (require $controller->getConfiguration()->getDirs()->getGenericPartsRoot() . '/router.php') {
    return ''; // routing solved
}

/** @noinspection PhpIncludeInspection */
return require $controller->getConfiguration()->getDirs()->getVendorRoot() . '/drdplus/frontend-skeleton/parts/frontend-skeleton/content.php';