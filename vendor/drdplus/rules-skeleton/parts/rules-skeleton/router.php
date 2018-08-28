<?php
/** @var \DrdPlus\RulesSkeleton\RulesController $controller */
if (\array_key_exists('tables', $_GET) || \array_key_exists('tabulky', $_GET)) { // we do not require licence confirmation for tables only
    echo include __DIR__ . '/get_tables.php';

    return true; // routing solved
}

if (($_SERVER['QUERY_STRING'] ?? false) === 'pdf'
    && \file_exists($controller->getConfiguration()->getDirs()->getDocumentRoot() . '/pdf')
    && \glob($controller->getConfiguration()->getDirs()->getDocumentRoot() . '/pdf/*.pdf')
) {
    /** @noinspection PhpIncludeInspection */
    echo include $controller->getConfiguration()->getDirs()->getGenericPartsRoot() . '/get_pdf.php';

    return true; // routing solved
}

$visitorCanAccessContent = !$controller->getConfiguration()->hasProtectedAccess();
if (!$visitorCanAccessContent) {
    $visitorCanAccessContent = $controller->getUsagePolicy()->isVisitorBot();
    if (!$visitorCanAccessContent) {
        if (!empty($_POST['confirm'])) {
            $visitorCanAccessContent = $controller->getUsagePolicy()->confirmOwnershipOfVisitor(new \DateTime('+1 year'));
        }
        if (!$visitorCanAccessContent && !empty($_POST['trial'])) {
            $visitorCanAccessContent = $controller->activateTrial($now ?? new \DateTime());
        }
        if (!$visitorCanAccessContent) {
            $visitorCanAccessContent = $controller->getUsagePolicy()->hasVisitorConfirmedOwnership();
            if (!$visitorCanAccessContent) {
                $visitorCanAccessContent = $controller->getUsagePolicy()->isVisitorUsingValidTrial();
                if (!$visitorCanAccessContent) {
                    $controller->addBodyClass('pass');
                }
            }
        }
    }
}

if ($visitorCanAccessContent) {
    $controller->allowAccess();
}

return false; // routing passed to index