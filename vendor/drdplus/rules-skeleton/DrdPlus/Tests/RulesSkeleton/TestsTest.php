<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\FrontendSkeleton\FrontendControllerTest;
use PHPUnit\Framework\TestCase;

class TestsTest extends TestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function All_frontend_skeleton_tests_are_used(): void
    {
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
            self::assertTrue(\is_a($expectedRulesTestClass, $frontendSkeletonTestClass, true), "$expectedRulesTestClass should be a child of $frontendSkeletonTestClass");
        }
    }

    private function getClassesFromDir(string $dir): array
    {
        $classes = [];
        foreach (\scandir($dir, SCANDIR_SORT_NONE) as $folder) {
            if ($folder === '.' || $folder === '..') {
                continue;
            }
            if (!\preg_match('~\.php$~', $folder)) {
                if (\is_dir($dir . '/' . $folder)) {
                    foreach ($this->getClassesFromDir($dir . '/' . $folder) as $class) {
                        $classes[] = $class;
                    }
                }
                continue;
            }
            self::assertNotEmpty(
                \preg_match('~(?<className>DrdPlus/[^/].+)\.php~', $dir . '/' . $folder, $matches),
                "DrdPlus class name has not been determined from $dir/$folder"
            );
            $classes[] = \str_replace('/', '\\', $matches['className']);
        }

        return $classes;
    }
}