<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use Granam\String\StringTools;

class TablesTest extends \DrdPlus\Tests\FrontendSkeleton\TablesTest
{
    /**
     * @test
     */
    public function I_can_get_wanted_tables_from_content(): void
    {
        if (!$this->getTestsConfiguration()->hasTables()) {
            self::assertCount(
                0,
                $this->getTestsConfiguration()->getSomeExpectedTableIds(),
                'No tables expected due to tests configuration'
            );

            return;
        }
        $htmlDocument = $this->getHtmlDocument(
            ['tables' => \implode(',', $this->getTestsConfiguration()->getSomeExpectedTableIds())]
        );
        foreach ($this->getTestsConfiguration()->getSomeExpectedTableIds() as $tableId) {
            $tableId = StringTools::toConstantLikeValue(StringTools::camelCaseToSnakeCase($tableId));
            self::assertNotNull(
                $htmlDocument->getElementById(StringTools::toConstantLikeValue($tableId)), 'Missing table of ID ' . $tableId);
        }
    }
}