<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Web;

use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;

class MainContentTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function I_can_get_content(): void
    {
        self::assertSame($this->getHtmlDocument()->saveHTML(), $this->getContent());
    }

    /**
     * @test
     */
    public function I_can_get_body(): void
    {
        self::assertNotEmpty($this->getHtmlDocument()->body->innerHTML);
    }

    /**
     * @test
     */
    public function Body_has_container_bootstrap_class(): void
    {
        self::assertTrue(
            $this->getHtmlDocument()->body->classList->contains('container'),
            'Body should has "container" class to be usable for Bootstrap'
        );
    }
}