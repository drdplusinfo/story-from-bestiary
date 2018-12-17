<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;

class TestsConfigurationTest extends AbstractContentTest
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_use_it(): void
    {
        $testsConfigurationReflection = new \ReflectionClass(static::getSutClass());
        $methods = $testsConfigurationReflection->getMethods(
            \ReflectionMethod::IS_PUBLIC ^ \ReflectionMethod::IS_STATIC ^ \ReflectionMethod::IS_ABSTRACT
        );
        $getters = [];
        $setters = [];
        $hasGetters = [];
        $disablingMethods = [];
        $setterReflections = [];
        foreach ($methods as $method) {
            $methodName = $method->getName();
            if (\strpos($methodName, 'get') === 0) {
                $getters[] = $methodName;
            } elseif (\strpos($methodName, 'has') === 0 || \strpos($methodName, 'can') === 0) {
                $hasGetters[] = $methodName;
            } elseif (\strpos($methodName, 'disable') === 0) {
                $disablingMethods[] = $methodName;
            } elseif (\strpos($methodName, 'set') === 0) {
                $setterReflections[] = $method;
                $setters[] = $methodName;
            }
        }
        $this->Every_boolean_setting_is_enabled_by_default($hasGetters);
        $this->Every_boolean_setting_can_be_disabled_by_specific_method($disablingMethods, $hasGetters);
        $this->I_can_call_disabling_methods_in_chain($disablingMethods);
        $this->I_can_set_what_i_can_get($getters, $setters);
        $this->I_can_call_setters_in_chain($setterReflections);
    }

    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~(.+)Test$~'): string
    {
        return parent::getSutClass($sutTestClass, $regexp);
    }

    private function Every_boolean_setting_is_enabled_by_default(array $hasGetters): void
    {
        $testsConfiguration = $this->createSut();
        foreach ($hasGetters as $hasGetter) {
            self::assertTrue($testsConfiguration->$hasGetter(), "$hasGetter should return true by default to ensure strict mode");
        }
    }

    private function Every_boolean_setting_can_be_disabled_by_specific_method(array $disablingMethods, array $hasGetters): void
    {
        $testsConfiguration = $this->createSut();
        foreach ($disablingMethods as $disablingMethod) {
            $expectedHasGetter = \lcfirst(\preg_replace('~^disable~', '', $disablingMethod));
            self::assertContains(
                $expectedHasGetter,
                $hasGetters,
                "$disablingMethod does not match to any 'has' nor 'can' getter: " . \var_export($hasGetters, true)
            );
            self::assertTrue($testsConfiguration->$expectedHasGetter());
            $testsConfiguration->$disablingMethod();
            self::assertFalse($testsConfiguration->$expectedHasGetter(), "$disablingMethod does not changed the setting");
        }
        self::assertCount(
            \count($hasGetters),
            $disablingMethods,
            'Count of disabling methods should be equal to count of boolean setting getters'
            . '; disabling methods: ' . \print_r($disablingMethods, true) . '; boolean getters' . \print_r($hasGetters, true)
        );
    }

    private function I_can_call_disabling_methods_in_chain(array $disablingMethods): void
    {
        $testsConfiguration = $this->createSut();
        foreach ($disablingMethods as $disablingMethod) {
            self::assertSame(
                $testsConfiguration,
                $testsConfiguration->$disablingMethod(),
                "$disablingMethod should return the " . static::getSutClass() . ' to get fluent interface'
            );
        }
    }

    private function I_can_set_what_i_can_get(array $getters, array $setters): void
    {
        $expectedGetters = [];
        foreach ($setters as $setter) {
            $expectedGetters[] = 'get' . \substr($setter, 3);
        }
        $missingGetters = \array_diff($expectedGetters, $getters);
        self::assertSame([], $missingGetters, 'Some getters are missing');
        $expectedSetters = [];
        foreach ($getters as $getter) {
            $expectedSetters[] = 'set' . \substr($getter, 3);
        }
        $expectedSetters = \array_diff($expectedSetters, $this->getNonExistingSettersToSkip());
        $missingSetters = \array_diff($expectedSetters, $setters);
        self::assertSame([], $missingSetters, 'Some setters are missing');
    }

    /**
     * @param array|\ReflectionMethod $setterReflections
     * @throws \LogicException
     * @throws \ReflectionException
     */
    private function I_can_call_setters_in_chain(array $setterReflections): void
    {
        $testsConfiguration = $this->createSut();
        /** @var \ReflectionMethod $setterReflection */
        foreach ($setterReflections as $setterReflection) {
            $parameterReflections = $setterReflection->getParameters();
            $parameters = [];
            foreach ($parameterReflections as $parameterReflection) {
                if ($parameterReflection->allowsNull()) {
                    $parameters[] = null;
                } elseif ($parameterReflection->isDefaultValueAvailable()) {
                    $parameters[] = $parameterReflection->getDefaultValue();
                } elseif (!$parameterReflection->hasType()) {
                    $parameters[] = null;
                } elseif ($parameterReflection->getType()->isBuiltin()) {
                    switch ($parameterReflection->getType()->getName()) {
                        case 'bool' :
                            throw new \LogicException(
                                "{$setterReflection->getName()} should not be a setter but a disabling method"
                            );
                            break;
                        case 'int' :
                            $parameters[] = 123;
                            break;
                        case 'float' :
                            $parameters[] = 123.456;
                            break;
                        case 'string' :
                            $parameters[] = 'Some parameter 123.456';
                            break;
                        case 'array' :
                            $parameters[] = ['One of parameters 123.456'];
                            break;
                        default :
                            throw new \LogicException(
                                "Do not know how to use parameter {$parameterReflection->getName()} of type {$parameterReflection->getType()} in method {$setterReflection->getName()}"
                            );
                    }
                } else {
                    throw new \LogicException(
                        "Do not know how to use parameter {$parameterReflection->getName()} of type {$parameterReflection->getType()} in method {$setterReflection->getName()}"
                    );
                }
            }
            self::assertSame(
                $testsConfiguration,
                $setterReflection->invokeArgs($testsConfiguration, $parameters),
                "$setterReflection should return the " . static::getSutClass() . ' to get fluent interface'
            );
        }
    }

    /**
     * @test
     */
    public function I_can_add_allowed_calculation_id_prefix(): void
    {
        $testsConfiguration = new TestsConfiguration('https://www.drdplus.info');
        $originalAllowedCalculationIdPrefixes = $testsConfiguration->getAllowedCalculationIdPrefixes();
        self::assertNotEmpty($originalAllowedCalculationIdPrefixes, 'Some allowed calculation ID prefixes expected');
        $returnedTestsConfiguration = $testsConfiguration->addAllowedCalculationIdPrefix('Foo allowed calculation id prefix');
        self::assertSame(
            $testsConfiguration,
            $returnedTestsConfiguration,
            'Method addAllowedCalculationIdPrefix should return ' . TestsConfiguration::class . ' instance for fluent interface'
        );
        $addedPrefixes = \array_values( // to re-index result from zero index
            \array_diff($testsConfiguration->getAllowedCalculationIdPrefixes(), $originalAllowedCalculationIdPrefixes)
        );
        self::assertSame(['Foo allowed calculation id prefix'], $addedPrefixes);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Tests\RulesSkeleton\Exceptions\AllowedCalculationPrefixShouldStartByUpperLetter
     * @expectedExceptionMessageRegExp ~říčany u čeho chceš~
     */
    public function I_can_not_add_allowed_calculation_id_prefix_with_lowercase_first_letter(): void
    {
        (new TestsConfiguration('https://www.drdplus.info'))->addAllowedCalculationIdPrefix('říčany u čeho chceš');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Tests\RulesSkeleton\Exceptions\AllowedCalculationPrefixShouldStartByUpperLetter
     * @expectedExceptionMessageRegExp ~žbrdloch~
     */
    public function I_can_not_set_allowed_calculation_id_prefixes_with_even_single_one_with_lowercase_first_letter(): void
    {
        (new TestsConfiguration('https://www.drdplus.info'))->setAllowedCalculationIdPrefixes([
            'Potvora na entou',
            'Kuloár',
            'žbrdloch',
        ]);
    }

    /**
     * @test
     */
    public function I_will_get_some_stable_version_if_has_more_versions(): void
    {
        $testsConfiguration = new TestsConfiguration('https://www.drdplus.info');
        if ($this->isSkeletonChecked() && !$testsConfiguration->hasMoreVersions()) {
            self::assertSame('master', $testsConfiguration->getExpectedLastVersion(), 'Expected master as a single version');

            return;
        }
        self::assertTrue($testsConfiguration->hasMoreVersions(), 'More versions expected');
        self::assertRegExp(
            '~^\d+[.]\d+([.]\d+)?$~',
            $testsConfiguration->getExpectedLastVersion(),
            'Expected stable version in format x.y[.z]'
        );
    }

    /**
     * @test
     */
    public function I_can_get_last_unstable_version(): void
    {
        $testsConfiguration = new TestsConfiguration('https://www.drdplus.info');
        self::assertSame('master', $testsConfiguration->getExpectedLastUnstableVersion());
        if (!$testsConfiguration->hasMoreVersions()) {
            self::assertSame(
                $testsConfiguration->getExpectedLastVersion(),
                $testsConfiguration->getExpectedLastUnstableVersion(),
                'Expected same last version and last unstable version as only a single version is expected'
            );
        }
    }

    /**
     * @test
     */
    public function I_can_disable_test_of_headings(): void
    {
        $testsConfiguration = new TestsConfiguration('https://www.drdplus.info');
        self::assertTrue($testsConfiguration->hasHeadings(), 'Test of headings should be enabled by default');
        $testsConfiguration->disableHasHeadings();
        self::assertFalse($testsConfiguration->hasHeadings(), 'Can not disable test of headings');
    }

    /**
     * @param string $publicUrl
     * @return \DrdPlus\Tests\RulesSkeleton\TestsConfiguration|TestsConfiguration
     */
    protected function createSut(string $publicUrl = 'https://rules.skeleton.drdplus.info'): TestsConfiguration
    {
        $sutClass = static::getSutClass();

        return new $sutClass($publicUrl);
    }

    protected function getNonExistingSettersToSkip(): array
    {
        return ['setLocalUrl', 'setPublicUrl'];
    }

    /**
     * @test
     */
    public function I_can_set_and_get_local_and_public_url(): void
    {
        $testsConfiguration = $this->createSut('https://drdplus.info');
        self::assertSame('http://drdplus.loc', $testsConfiguration->getLocalUrl());
        self::assertSame('https://drdplus.info', $testsConfiguration->getPublicUrl());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Tests\RulesSkeleton\Exceptions\InvalidPublicUrl
     * @expectedExceptionMessageRegExp ~not valid~
     */
    public function I_can_not_create_it_with_invalid_public_url(): void
    {
        $this->createSut('example.com'); // missing protocol
    }

    /**
     * @test
     * @expectedException \DrdPlus\Tests\RulesSkeleton\Exceptions\PublicUrlShouldUseHttps
     * @expectedExceptionMessageRegExp ~HTTPS~
     */
    public function I_can_not_create_it_with_public_url_without_https(): void
    {
        $this->createSut('http://example.com');
    }

    /**
     * @test
     */
    public function I_will_get_expected_licence_by_access_by_default(): void
    {
        $testsConfiguration = $this->createSut();
        self::assertTrue($testsConfiguration->hasProtectedAccess());
        self::assertSame('proprietary', $testsConfiguration->getExpectedLicence(), 'Expected proprietary licence for protected access');
        $testsConfiguration->disableHasProtectedAccess();
        self::assertFalse($testsConfiguration->hasProtectedAccess());
        self::assertSame('MIT', $testsConfiguration->getExpectedLicence(), 'Expected MIT licence for free access');
        $testsConfiguration->setExpectedLicence('foo');
        self::assertSame('foo', $testsConfiguration->getExpectedLicence());
    }

    /**
     * @test
     */
    public function I_can_add_too_short_failure_names(): void
    {
        $testsConfiguration = $this->createSut();
        self::assertCount(1, $testsConfiguration->getTooShortFailureNames());
        $testsConfiguration->addTooShortFailureName('foo');
        self::assertSame(['nevšiml si', 'foo'], $testsConfiguration->getTooShortFailureNames());
        $testsConfiguration->addTooShortFailureName('bar');
        self::assertSame(['nevšiml si', 'foo', 'bar'], $testsConfiguration->getTooShortFailureNames());
        $testsConfiguration->setTooShortFailureNames(['baz', 'qux']);
        self::assertSame(['baz', 'qux'], $testsConfiguration->getTooShortFailureNames());
    }

    /**
     * @test
     */
    public function I_can_add_every_too_short_failure_name_just_once(): void
    {
        $testsConfiguration = $this->createSut();
        $testsConfiguration->setTooShortFailureNames(['foo', 'bar']);
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortFailureNames());
        $testsConfiguration->addTooShortFailureName('foo');
        $testsConfiguration->addTooShortFailureName('bar');
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortFailureNames());
    }

    /**
     * @test
     */
    public function I_can_add_too_short_success_names(): void
    {
        $testsConfiguration = $this->createSut();
        self::assertCount(1, $testsConfiguration->getTooShortSuccessNames());
        $testsConfiguration->addTooShortSuccessName('foo');
        self::assertSame(['všiml si', 'foo'], $testsConfiguration->getTooShortSuccessNames());
        $testsConfiguration->addTooShortSuccessName('bar');
        self::assertSame(['všiml si', 'foo', 'bar'], $testsConfiguration->getTooShortSuccessNames());
        $testsConfiguration->setTooShortSuccessNames(['baz', 'qux']);
        self::assertSame(['baz', 'qux'], $testsConfiguration->getTooShortSuccessNames());
    }

    /**
     * @test
     */
    public function I_can_add_every_too_short_success_name_just_once(): void
    {
        $testsConfiguration = $this->createSut();
        $testsConfiguration->setTooShortSuccessNames(['foo', 'bar']);
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortSuccessNames());
        $testsConfiguration->addTooShortSuccessName('foo');
        $testsConfiguration->addTooShortSuccessName('bar');
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortSuccessNames());
    }

    /**
     * @test
     */
    public function I_can_add_too_short_result_names(): void
    {
        $testsConfiguration = $this->createSut();
        self::assertCount(2, $testsConfiguration->getTooShortResultNames());
        $testsConfiguration->addTooShortResultName('foo');
        self::assertSame(['Bonus', 'Postih', 'foo'], $testsConfiguration->getTooShortResultNames());
        $testsConfiguration->addTooShortResultName('bar');
        self::assertSame(['Bonus', 'Postih', 'foo', 'bar'], $testsConfiguration->getTooShortResultNames());
        $testsConfiguration->setTooShortResultNames(['baz', 'qux']);
        self::assertSame(['baz', 'qux'], $testsConfiguration->getTooShortResultNames());
    }

    /**
     * @test
     */
    public function I_can_add_every_too_short_result_name_just_once(): void
    {
        $testsConfiguration = $this->createSut();
        $testsConfiguration->setTooShortResultNames(['foo', 'bar']);
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortResultNames());
        $testsConfiguration->addTooShortResultName('foo');
        $testsConfiguration->addTooShortResultName('bar');
        self::assertSame(['foo', 'bar'], $testsConfiguration->getTooShortResultNames());
    }

    /**
     * @test
     */
    public function I_can_disable_test_of_table_of_contents(): void
    {
        $testsConfiguration = $this->createSut();
        self::assertTrue($testsConfiguration->hasTableOfContents(), 'Table of contents should be expected to test by default');
        $testsConfiguration->disableHasTableOfContents();
        self::assertFalse($testsConfiguration->hasTableOfContents(), 'Test of table of contents should be disabled now');
    }
}