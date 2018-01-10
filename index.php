<?php
error_reporting(-1);
if ((!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') || PHP_SAPI === 'cli') {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

$documentRoot = PHP_SAPI !== 'cli' ? rtrim(dirname($_SERVER['SCRIPT_FILENAME']), '\/') : getcwd();

/** @noinspection PhpIncludeInspection */
require_once $documentRoot . '/vendor/autoload.php';

if (($_SERVER['QUERY_STRING'] === 'pdf' || !file_exists($documentRoot . '/html'))
    && file_exists($documentRoot . '/pdf') && glob($documentRoot . '/pdf/*.pdf')
) {
    include __DIR__ . '/vendor/drd-plus/rules-html-skeleton/get_pdf.php';
    exit;
}

$visitorHasConfirmedOwnership = true; // just a little hack to make content public for anyone
echo require __DIR__ . '/vendor/drd-plus/rules-html-skeleton/content.php';