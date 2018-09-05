<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class Request extends \DrdPlus\FrontendSkeleton\Request
{
    public const TABLES = 'tables';
    public const TABULKY = 'tabulky';

    /**
     * @return array|string[]
     */
    public function getWantedTablesIds(): array
    {
        $wantedTableIds = \array_map(
            function (string $id) {
                return \trim($id);
            },
            \explode(',', $_GET[self::TABLES] ?? $_GET[self::TABULKY] ?? '')
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

    public function getValueFromPost(string $name)
    {
        return $_POST[$name] ?? null;
    }

    public function getValueFromGet(string $name)
    {
        return $_GET[$name] ?? null;
    }
}