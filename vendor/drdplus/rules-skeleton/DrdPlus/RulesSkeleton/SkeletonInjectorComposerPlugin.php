<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use Composer\Installer\PackageEvent;
use DrdPlus\FrontendSkeleton\AbstractSkeletonInjectorComposerPlugin;

class SkeletonInjectorComposerPlugin extends AbstractSkeletonInjectorComposerPlugin
{
    public const RULES_SKELETON_PACKAGE_NAME = 'drdplus/rules-skeleton';

    public function __construct()
    {
        parent::__construct(static::RULES_SKELETON_PACKAGE_NAME);
    }

    public function plugInSkeleton(PackageEvent $event)
    {
        if ($this->alreadyInjected || !$this->isThisPackageChanged($event)) {
            return;
        }
        $documentRoot = $GLOBALS['documentRoot'] ?? getcwd();
        $this->io->write("Injecting {$this->skeletonPackageName} using document root $documentRoot");
        $this->publishSkeletonImages($documentRoot);
        $this->publishSkeletonCss($documentRoot);
        $this->publishSkeletonJs($documentRoot);
        $this->flushCache($documentRoot);
        $this->addVersionsToAssets($documentRoot);
        $this->alreadyInjected = true;
        $this->io->write("Injection of {$this->skeletonPackageName} finished");
    }

    protected function isChangedPackageThisOne(string $changedPackageName): bool
    {
        return $changedPackageName === static::RULES_SKELETON_PACKAGE_NAME
            || parent::isChangedPackageThisOne($changedPackageName);
    }

    protected function publishSkeletonImages(string $documentRoot): void
    {
        $this->passThrough(
            [
                'rm -f ./images/generic/skeleton/rules*',
                'cp -r ./vendor/drdplus/rules-skeleton/images/generic ./images/'
            ],
            $documentRoot
        );
    }

    protected function publishSkeletonCss(string $documentRoot): void
    {
        $this->passThrough(
            [
                'rm -f ./css/generic/skeleton/rules*',
                'cp -r ./vendor/drdplus/rules-skeleton/css/generic ./css/',
            ],
            $documentRoot
        );
    }

    protected function publishSkeletonJs(string $documentRoot): void
    {
        $this->passThrough(
            [
                'rm -f ./js/generic/skeleton/rules*',
                'cp -r ./vendor/drdplus/rules-skeleton/js/generic ./js/',
            ],
            $documentRoot
        );
    }
}