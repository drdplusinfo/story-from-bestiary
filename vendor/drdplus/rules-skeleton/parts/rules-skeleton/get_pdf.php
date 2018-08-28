<?php
$documentRoot = PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd();
if (!\file_exists($documentRoot . '/pdf')) {
    \header('HTTP/1.0 404 PDF Not Found', true, 404);
    exit;
}
$pdfFiles = glob($documentRoot . '/pdf/*.pdf');
if (\count($pdfFiles) === 0) {
    \header('HTTP/1.0 404 PDF Not Found', true, 404);
    exit;
}
$pdfFile = $pdfFiles[0];
$pdfFileBasename = \basename($pdfFile);
\header('Content-type: application/pdf');
\header('Content-Length: ' . \filesize($pdfFile));
\readfile($pdfFile);