<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\FrontendSkeleton\FrontendControllerTest;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTestTrait;
use DrdPlus\Tests\RulesSkeleton\Partials\TestsConfigurationReaderTest;

class TestsTest extends \DrdPlus\Tests\FrontendSkeleton\TestsTest
{
    use AbstractContentTestTrait;

    /**
     * @test
     * @throws \ReflectionException
     */
    public function All_frontend_skeleton_tests_are_used(): void
    {
        if (!$this->isSkeletonChecked()) {
            self::assertTrue(true, 'Already tested in skeleton');

            return;
        }
        $reflectionClass = new \ReflectionClass(\DrdPlus\Tests\FrontendSkeleton\WebContentTest::class);
        $frontendSkeletonDir = \dirname($reflectionClass->getFileName());
        foreach ($this->getClassesFromDir($frontendSkeletonDir) as $frontendSkeletonTestClass) {
            if (\is_a($frontendSkeletonTestClass, \Throwable::class, true)
                || \is_a($frontendSkeletonTestClass, FrontendControllerTest::class, true) // it is solved via RulesSkeletonController
            ) {
                continue;
            }
            $frontendSkeletonTestClassReflection = new \ReflectionClass($frontendSkeletonTestClass);
            if ($frontendSkeletonTestClassReflection->isAbstract()
                || $frontendSkeletonTestClassReflection->isInterface()
                || $frontendSkeletonTestClassReflection->isTrait()
            ) {
                continue;
            }
            $expectedRulesTestClass = \str_replace('\\FrontendSkeleton', '\\RulesSkeleton', $frontendSkeletonTestClass);
            self::assertTrue(\class_exists($expectedRulesTestClass), "Missing test class {$expectedRulesTestClass} adopted from frontend skeleton");
            self::assertTrue(
                \is_a($expectedRulesTestClass, $frontendSkeletonTestClass, true),
                "$expectedRulesTestClass should be a child of $frontendSkeletonTestClass"
            );
        }
    }

    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        $sutClass = parent::getSutClass($sutTestClass, $regexp);
        $frontendClass = \str_replace('RulesSkeleton', 'FrontendSkeleton', $sutClass);
        if (\class_exists($frontendClass)) {
            return $frontendClass;
        }

        return $sutClass;
    }

    protected function getNotClassesTestingTests(): array
    {
        return [
            AnchorsTest::class,
            GraphicsTest::class,
            TestsConfigurationTest::class,
            RulesSkeletonExceptionsHierarchyTest::class,
            TablesTest::class,
            GoogleTest::class,
            WebContentVersionTest::class,
            ComposerConfigTest::class,
            TracyTest::class,
            TrialTest::class,
            WebContentTest::class,
            StandardModeTest::class,
            static::class,
            \DrdPlus\Tests\FrontendSkeleton\TestsTest::class,
            CoveredPartsCanBeHiddenTest::class,
            PassingTest::class,
            ContactsContentTest::class,
            DevModeTest::class,
            CalculationsTest::class,
            SourceCodeLinksTest::class,
            TestsConfigurationReaderTest::class,
            TableOfContentTest::class,
        ];
    }

}