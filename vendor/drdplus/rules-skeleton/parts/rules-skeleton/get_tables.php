<?php
\error_reporting(-1);
if ((!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') || PHP_SAPI === 'cli') {
    \ini_set('display_errors', '1');
} else {
    \ini_set('display_errors', '0');
}
if (PHP_SAPI !== 'cli') {
// anyone can show content of this page
    \header('Access-Control-Allow-Origin: *', true);
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
$controller = new \DrdPlus\RulesSkeleton\RulesController($configuration, $htmlHelper);

/**
 * @var \DrdPlus\RulesSkeleton\RulesController $controller
 * @var \DrdPlus\RulesSkeleton\HtmlHelper $htmlHelper
 */
$tablesCache = new \DrdPlus\RulesSkeleton\TablesCache(
    $controller->getWebVersions(),
    $controller->getConfiguration()->getDirs(),
    $htmlHelper->isInProduction(),
    $controller->getConfiguration()->getDirs()->getVersionWebRoot($controller->getWebVersions()->getCurrentVersion())
);
$controller->allowAccess();
if ($tablesCache->isCacheValid()) {
    return $tablesCache->getCachedContent();
}
// must NOT include current content.php as it uses router and that requires this script so endless recursion happens
/** @noinspection PhpIncludeInspection */
$rawContent = require $controller->getConfiguration()->getDirs()->getDocumentRoot() . '/vendor/drdplus/frontend-skeleton/parts/frontend-skeleton/content.php';
$rawContentDocument = new \DrdPlus\FrontendSkeleton\HtmlDocument($rawContent);
$tables = $htmlHelper->findTablesWithIds($rawContentDocument, $controller->getRequest()->getWantedTablesIds());
$tablesContent = '';
foreach ($tables as $table) {
    $tablesContent .= $table->outerHTML . "\n";
}
unset($rawContent, $rawContentDocument);
\ob_start();
?>
  <!DOCTYPE html>
  <html lang="cs">
    <head>
      <title>Tabulky pro Drd+ <?= \basename($controller->getConfiguration()->getDirs()->getDocumentRoot()) ?></title>
      <link rel="shortcut icon" href="../../favicon.ico">
      <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
        <?php
        foreach ($controller->getCssFiles() as $cssFile) { ?>
          <link rel="stylesheet" type="text/css" href="css/<?= $cssFile ?>">
        <?php } ?>
      <style>
        table {
          float: left;
        }
      </style>
      <script type="text/javascript">
          // let just second level domain to be the document domain to allow access to iframes from other sub-domains
          document.domain = document.domain.replace(/^(?:[^.]+\.)*([^.]+\.[^.]+).*/, '$1');
      </script>
    </head>
    <body>
        <?php
        $content = \ob_get_contents();
        \ob_clean();
        $content .= $tablesContent;
        ?>
    </body>
  </html>
<?php
$content .= \ob_get_clean();
$tablesCache->cacheContent($content);

return $content;