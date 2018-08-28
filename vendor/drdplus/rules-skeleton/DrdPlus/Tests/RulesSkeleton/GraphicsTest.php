<?php
namespace DrdPlus\Tests\RulesSkeleton;

/**
 * @method TestsConfiguration getTestsConfiguration
 */
class GraphicsTest extends \DrdPlus\Tests\FrontendSkeleton\GraphicsTest
{
    use Partials\AbstractContentTestTrait;

    /**
     * @test
     */
    public function Licence_page_has_colored_background_image(): void
    {
        if (!$this->getTestsConfiguration()->hasProtectedAccess()) {
            self::assertFalse(false, 'Licence background image is not needed for free content');

            return;
        }
        self::assertFileExists($this->getDocumentRoot() . '/images/licence-background.png');
    }
}