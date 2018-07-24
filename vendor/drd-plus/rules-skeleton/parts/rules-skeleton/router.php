<?php
/** @var \DrdPlus\RulesSkeleton\RulesController $controller */
if (\array_key_exists('tables', $_GET) || \array_key_exists('tabulky', $_GET)) { // we do not require licence confirmation for tables only
    echo include __DIR__ . '/get_tables.php';

    return true; // routing solved
}

if ((($_SERVER['QUERY_STRING'] ?? false) === 'pdf' || !\file_exists($controller->getDirs()->getDocumentRoot() . '/web'))
    && \file_exists($controller->getDirs()->getDocumentRoot() . '/pdf') && \glob($controller->getDirs()->getDocumentRoot() . '/pdf/*.pdf')
) {
    /** @noinspection PhpIncludeInspection */
    echo include $controller->getDirs()->getGenericPartsRoot() . '/get_pdf.php';

    return true; // routing solved
}

if (empty($visitorCanAccessContent) && !$controller->isFreeAccess()) { // can be defined externally by including script
    $visitorCanAccessContent = $controller->getUsagePolicy()->isVisitorBot();
    if (!$visitorCanAccessContent) {
        $visitorCanAccessContent = $controller->getUsagePolicy()->hasVisitorConfirmedOwnership();
        if (!$visitorCanAccessContent) {
            $visitorCanAccessContent = $controller->getUsagePolicy()->isVisitorUsingTrial();
        }
        if (!$visitorCanAccessContent) {
            if (!empty($_POST['confirm'])) {
                $visitorCanAccessContent = $controller->getUsagePolicy()->confirmOwnershipOfVisitor(new \DateTime('+1 year'));
            }
            if (!$visitorCanAccessContent && !empty($_POST['trial'])) {
                $now = $now ?? new \DateTime();
                $visitorCanAccessContent = $controller->activateTrial((clone $now)->modify('+4 minutes'));
            }
            if (!$visitorCanAccessContent) {
                $controller->getDirs()->setWebRoot(\file_exists($controller->getDirs()->getDocumentRoot() . '/web/pass')
                    ? $controller->getDirs()->getDocumentRoot() . '/web/pass'
                    : $controller->getDirs()->getVendorRoot() . '/drd-plus/rules-skeleton/web/pass'
                );
                $controller->addBodyClass('pass');
            }
        }
    }
}

return false; // routing passed to index