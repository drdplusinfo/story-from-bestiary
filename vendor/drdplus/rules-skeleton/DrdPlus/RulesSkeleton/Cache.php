<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class Cache extends StrictObject
{
    public const TABLES = 'tables';

    /** @var string */
    protected $cacheRootDir;
    /** @var array|string[] */
    protected $cacheRoots;
    /** @var WebVersions */
    protected $webVersions;
    /** @var Request */
    private $request;
    /** @var Git */
    private $git;
    /** @var string */
    protected $cachePrefix;
    /** @var bool */
    protected $isInProduction;

    /**
     * @param WebVersions $webVersions
     * @param Dirs $dirs
     * @param Request $request
     * @param Git $git
     * @param bool $isInProduction
     * @param string $cachePrefix
     * @throws \RuntimeException
     */
    public function __construct(WebVersions $webVersions, Dirs $dirs, Request $request, Git $git, bool $isInProduction, string $cachePrefix)
    {
        $this->webVersions = $webVersions;
        $this->cacheRootDir = $dirs->getCacheRoot();
        $this->request = $request;
        $this->git = $git;
        $this->isInProduction = $isInProduction;
        $this->cachePrefix = $cachePrefix;
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        $currentVersion = $this->webVersions->getCurrentMinorVersion();
        if (($this->cacheRoots[$currentVersion] ?? null) === null) {
            $cacheRoot = $this->cacheRootDir . '/' . $currentVersion;
            if (!\file_exists($cacheRoot)) {
                if (!@\mkdir($cacheRoot, 0775, true /* with parents */) && !\is_dir($cacheRoot)) {
                    throw new \RuntimeException('Can not create directory for page cache ' . $cacheRoot);
                }
                \chmod($cacheRoot, 0775); // because umask could suppress it
            }
            $this->cacheRoots[$currentVersion] = $cacheRoot;
        }

        return $this->cacheRoots[$currentVersion];
    }

    public function isInProduction(): bool
    {
        return $this->isInProduction;
    }

    protected function getCurrentRequestHash(): string
    {
        return \md5(\serialize($this->request->getValuesFromGet()));
    }

    /**
     * @return bool
     * @throws \RuntimeException
     */
    public function isCacheValid(): bool
    {
        return ($this->request->getValue(Request::CACHE) ?? '') !== Request::DISABLE && \is_readable($this->getCacheFileName());
    }

    public function getCacheId(): string
    {
        return $this->getCacheFileBaseNamePartWithoutRequest() . '_' . $this->getCurrentRequestHash();
    }

    /**
     * @return string
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotGetGitStatus
     * @throws \DrdPlus\RulesSkeleton\Exceptions\ExecutingCommandFailed
     */
    private function getCacheFileName(): string
    {
        return $this->getCacheDir() . "/{$this->getCacheId()}.html";
    }

    /**
     * @return string
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotGetGitStatus
     * @throws \DrdPlus\RulesSkeleton\Exceptions\ExecutingCommandFailed
     */
    private function getCacheFileBaseNamePartWithoutRequest(): string
    {
        $prefix = \md5($this->getCachePrefix() . $this->getGitStamp());

        return "{$this->webVersions->getCurrentPatchVersion()}_{$prefix}_{$this->webVersions->getCurrentCommitHash()}";
    }

    protected function getCachePrefix(): string
    {
        return $this->cachePrefix;
    }

    /**
     * @return string
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotGetGitStatus
     */
    private function getGitStamp(): string
    {
        if ($this->isInProduction()) {
            return 'production';
        }
        $gitStatus = $this->git->getGitStatus();
        $diffAgainstOriginMaster = $this->git->getDiffAgainstOriginMaster();

        return \md5(\implode(\array_merge($gitStatus, $diffAgainstOriginMaster)));
    }

    /**
     * @return string
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotReadCachedContent
     */
    public function getCachedContent(): string
    {
        $cachedContent = \file_get_contents($this->getCacheFileName());
        if ($cachedContent === false) {
            throw new Exceptions\CanNotReadCachedContent("Can not read cached content from '{$this->getCacheFileName()}'");
        }

        return $cachedContent;
    }

    /**
     * @param string $content
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotSaveContentForDebug
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotChangeAccessToFileWithContentForDebug
     */
    public function saveContentForDebug(string $content): void
    {
        $cacheDebugFileName = $this->getCacheDebugFileName();
        if (!\file_put_contents($cacheDebugFileName, $content, \LOCK_EX)) {
            throw new Exceptions\CanNotSaveContentForDebug('Can not save content for debugging purpose into ' . $cacheDebugFileName);
        }
        if (!@\chmod($cacheDebugFileName, 0664)) {
            throw new Exceptions\CanNotChangeAccessToFileWithContentForDebug(
                'Can not change access to 0644 for file with content for debug ' . $cacheDebugFileName
            );
        }
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function getCacheDebugFileName(): string
    {
        return $this->getCacheDir() . "/{$this->geCacheDebugFileBaseNamePartWithoutGet()}_{$this->getCurrentRequestHash()}.html";
    }

    /**
     * @return string
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotGetGitStatus
     * @throws \DrdPlus\RulesSkeleton\Exceptions\ExecutingCommandFailed
     */
    private function geCacheDebugFileBaseNamePartWithoutGet(): string
    {
        return 'debug_' . $this->getCacheFileBaseNamePartWithoutRequest();
    }

    /**
     * @param string $content
     * @throws \RuntimeException
     */
    public function cacheContent(string $content): void
    {
        $cacheFileName = $this->getCacheFileName();
        \file_put_contents($cacheFileName, $content, \LOCK_EX);
        \chmod($cacheFileName, 0664);
        $this->clearOldCache();
    }

    /**
     * @throws \RuntimeException
     */
    private function clearOldCache(): void
    {
        $foldersToSkip = ['.', '..', '.gitignore'];
        $currentCacheStamp = $this->webVersions->getCurrentCommitHash();
        $currentVersion = $this->webVersions->getCurrentMinorVersion();
        $cacheRoot = $this->cacheRoots[$currentVersion];
        foreach (\scandir($cacheRoot, \SCANDIR_SORT_NONE) as $folder) {
            if (\in_array($folder, $foldersToSkip, true)) {
                continue;
            }
            if (\strpos($folder, $currentVersion) === false) { // we will clear old cache only of currently selected version
                continue;
            }
            if (\strpos($folder, $currentCacheStamp) !== false) { // that file is valid
                continue;
            }
            \unlink($cacheRoot . '/' . $folder);
        }
    }
}