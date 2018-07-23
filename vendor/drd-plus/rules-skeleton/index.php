<?php
\error_reporting(-1);
if ((!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') || PHP_SAPI === 'cli') {
    \ini_set('display_errors', '1');
} else {
    \ini_set('display_errors', '0');
}
$masterDocumentRoot = $masterDocumentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());
$documentRoot = $documentRoot ?? $masterDocumentRoot;
$latestVersion = $latestVersion ?? '1.0';

if (!require __DIR__ . '/parts/rules-skeleton/solve_version.php') {
    /** @noinspection PhpIncludeInspection */
    require_once __DIR__ . '/parts/rules-skeleton/safe_autoload.php';

    $dirs = !empty($dirs)
        ? new \DrdPlus\RulesSkeleton\Dirs($dirs->getMasterDocumentRoot(), $dirs->getDocumentRoot())
        : new \DrdPlus\RulesSkeleton\Dirs($masterDocumentRoot, $documentRoot);
    $controller = $controller ?? new \DrdPlus\RulesSkeleton\RulesController(
            $googleAnalyticsId ?? 'UA-121206931-1',
            \DrdPlus\RulesSkeleton\HtmlHelper::createFromGlobals($dirs),
            $dirs
        );
    if (!\is_a($controller, \DrdPlus\RulesSkeleton\RulesController::class)) {
        throw new \LogicException('Invalid controller class, expected ' . \DrdPlus\RulesSkeleton\RulesController::class
            . ' or descendant, got ' . \get_class($controller)
        );
    }
    if (!empty($hasFreeAccess)) {
        $controller->setFreeAccess();
    }
    if (!empty($hasContactsFixed)) {
        $controller->setContactsFixed();
    }
    if (!empty($hasHiddenHomeButton)) {
        $controller->hideHomeButton();
    }

    /** @noinspection PhpIncludeInspection */
    require $dirs->getVendorRoot() . '/drd-plus/frontend-skeleton/index.php';
}