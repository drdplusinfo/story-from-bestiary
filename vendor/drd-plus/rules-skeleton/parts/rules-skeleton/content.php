<?php
/** @var \DrdPlus\RulesSkeleton\RulesController $controller */
/** @noinspection PhpIncludeInspection */
if (require $controller->getDirs()->getGenericPartsRoot() . '/router.php') {
    return ''; // routing solved
}

/** @noinspection PhpIncludeInspection */
return require $controller->getDirs()->getVendorRoot() . '/drd-plus/frontend-skeleton/parts/frontend-skeleton/content.php';