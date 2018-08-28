<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class Request extends \DrdPlus\FrontendSkeleton\Request
{
    /**
     * @return array|string[]
     */
    public function getWantedTablesIds(): array
    {
        $wantedTableIds = \array_map(
            function (string $id) {
                return \trim($id);
            },
            \explode(',', $_GET['tables'] ?? $_GET['tabulky'] ?? '')
        );

        return \array_filter(
            $wantedTableIds,
            function (string $id) {
                return $id !== '';
            }
        );
    }

    public function getPath(): string
    {
        return $_SERVER['PATH_INFO'] ?? '';
    }
}