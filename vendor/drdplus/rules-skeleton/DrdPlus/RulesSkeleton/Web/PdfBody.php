<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Dirs;

class PdfBody extends Body
{
    /** @var Dirs */
    private $dirs;
    /** @var string|bool */
    private $pdfFile;

    public function __construct(WebFiles $webFiles, Dirs $dirs)
    {
        parent::__construct($webFiles);
        $this->dirs = $dirs;
    }

    /**
     * @return string
     * @throws \DrdPlus\RulesSkeleton\Web\Exceptions\CanNotReadPdfFile
     */
    public function getBodyString(): string
    {
        $pdfFile = $this->getPdfFile();
        if (!$pdfFile) {
            return '';
        }

        $content = \file_get_contents($pdfFile);
        if ($content === false) {
            throw new Exceptions\CanNotReadPdfFile($pdfFile . ' can not be read');
        }

        return $content;
    }

    public function getPdfFile(): ?string
    {
        if ($this->pdfFile === null) {
            if (!\file_exists($this->dirs->getPdfRoot())) {
                $this->pdfFile = false;
            } else {
                $pdfFiles = \glob($this->dirs->getPdfRoot() . '/*.pdf');

                $this->pdfFile = $pdfFiles[0] ?? false;
            }
        }

        return $this->pdfFile ?: null;
    }
}