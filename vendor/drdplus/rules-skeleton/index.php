<?php
\error_reporting(-1);
if ((!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') || PHP_SAPI === 'cli') {
    \ini_set('display_errors', '1');
} else {
    \ini_set('display_errors', '0');
}
$documentRoot = $documentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());

/** @noinspection PhpIncludeInspection */
require_once $documentRoot . '/vendor/autoload.php';

$dirs = new \DrdPlus\RulesSkeleton\Dirs($documentRoot);
$htmlHelper = $htmlHelper ?? \DrdPlus\RulesSkeleton\HtmlHelper::createFromGlobals($dirs);
if (PHP_SAPI !== 'cli') {
    \DrdPlus\FrontendSkeleton\TracyDebugger::enable($htmlHelper->isInProduction());
}

$configuration = \DrdPlus\RulesSkeleton\Configuration::createFromYml($dirs);
$controller = $controller ?? new \DrdPlus\RulesSkeleton\RulesController($configuration, $htmlHelper);

/** @noinspection PhpIncludeInspection */
require $dirs->getVendorRoot() . '/drdplus/frontend-skeleton/index.php';