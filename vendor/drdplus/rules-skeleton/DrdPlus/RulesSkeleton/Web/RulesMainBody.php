<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use Granam\WebContentBuilder\Web\Body;
use Granam\WebContentBuilder\Web\WebFiles;

class RulesMainBody extends Body
{
    /** @var string */
    private $debugContacts;

    public function __construct(WebFiles $webFiles, DebugContactsBody $debugContactsBody)
    {
        parent::__construct($webFiles);
        $this->debugContacts = $debugContactsBody->getValue();
    }

    protected function fetchPhpFileContent(string $file): string
    {
        \ob_start();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $debugContacts = $this->debugContacts;
        /** @noinspection PhpIncludeInspection */
        include $file;

        return \ob_get_clean();
    }

}