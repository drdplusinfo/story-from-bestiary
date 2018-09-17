<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\Web\Content;

/**
 * @method ServicesContainer getServicesContainer
 */
class RulesController extends \DrdPlus\FrontendSkeleton\FrontendController
{
    /** @var Redirect */
    private $redirect;
    /** @var bool */
    private $canPassIn;

    public function __construct(ServicesContainer $servicesContainer)
    {
        parent::__construct($servicesContainer);
    }

    /**
     * @return Content|\DrdPlus\FrontendSkeleton\Web\Content
     */
    public function getContent(): \DrdPlus\FrontendSkeleton\Web\Content
    {
        if ($this->content) {
            return $this->content;
        }
        $servicesContainer = $this->getServicesContainer();
        if ($servicesContainer->getRequest()->areRequestedTables()) {
            $this->content = new Content(
                $servicesContainer->getHtmlHelper(),
                $servicesContainer->getWebVersions(),
                $servicesContainer->getHeadForTables(),
                $servicesContainer->getMenu(),
                $servicesContainer->getTablesBody(),
                $servicesContainer->getTablesWebCache(),
                Content::TABLES,
                $this->getRedirect()
            );

            return $this->content;
        }
        if ($servicesContainer->getRequest()->isRequestedPdf() && $servicesContainer->getPdfBody()->getPdfFile()) {
            $this->content = new Content(
                $servicesContainer->getHtmlHelper(),
                $servicesContainer->getWebVersions(),
                $servicesContainer->getEmptyHead(),
                $servicesContainer->getEmptyMenu(),
                $servicesContainer->getPdfBody(),
                $servicesContainer->getEmptyWebCache(),
                Content::PDF,
                $this->getRedirect()
            );

            return $this->content;
        }
        if (!$this->canPassIn()) {
            $this->content = new Content(
                $servicesContainer->getHtmlHelper(),
                $servicesContainer->getWebVersions(),
                $servicesContainer->getHead(),
                $servicesContainer->getMenu(),
                $servicesContainer->getPassBody(),
                $servicesContainer->getPassWebCache(),
                Content::PASS,
                $this->getRedirect()
            );

            return $this->content;
        }
        $this->content = new Content(
            $servicesContainer->getHtmlHelper(),
            $servicesContainer->getWebVersions(),
            $servicesContainer->getHead(),
            $servicesContainer->getMenu(),
            $servicesContainer->getBody(),
            $servicesContainer->getWebCache(),
            Content::FULL,
            $this->getRedirect()
        );

        return $this->content;
    }

    private function getRedirect(): ?Redirect
    {
        return $this->redirect;
    }

    private function canPassIn(): bool
    {
        if ($this->canPassIn !== null) {
            return $this->canPassIn;
        }
        $canPassIn = !$this->getServicesContainer()->getConfiguration()->hasProtectedAccess();
        if (!$canPassIn) {
            $usagePolicy = $this->getServicesContainer()->getUsagePolicy();
            $canPassIn = $usagePolicy->isVisitorBot();
            if (!$canPassIn) {
                if ($this->getServicesContainer()->getRequest()->getValueFromPost('confirm')) {
                    $canPassIn = $usagePolicy->confirmOwnershipOfVisitor(new \DateTime('+1 year'));
                }
                if (!$canPassIn && $this->getServicesContainer()->getRequest()->getValueFromPost('trial')) {
                    $canPassIn = $this->activateTrial($this->getServicesContainer()->getNow());
                }
                if (!$canPassIn) {
                    $canPassIn = $usagePolicy->hasVisitorConfirmedOwnership();
                    if (!$canPassIn) {
                        $canPassIn = $usagePolicy->isVisitorUsingValidTrial();
                    }
                }
            }
        }

        return $this->canPassIn = $canPassIn;
    }

    protected function activateTrial(\DateTime $now): bool
    {
        $trialExpiration = (clone $now)->modify('+4 minutes');
        $visitorCanAccessContent = $this->getServicesContainer()->getUsagePolicy()->activateTrial($trialExpiration);
        if ($visitorCanAccessContent) {
            $at = $trialExpiration->getTimestamp() + 1; // one second "insurance" overlap
            $afterSeconds = $at - $now->getTimestamp();
            $this->setRedirect(
                new Redirect("/?{$this->getServicesContainer()->getUsagePolicy()->getTrialExpiredAtName()}={$at}", $afterSeconds)
            );
        }

        return $visitorCanAccessContent;
    }

    private function setRedirect(Redirect $redirect): void
    {
        $this->redirect = $redirect;
        $this->content = null; // unset Content to re-create it with new redirect
    }

    public function sendCustomHeaders(): void
    {
        if ($this->getContent()->containsTables()) {
            if (\PHP_SAPI === 'cli') {
                return;
            }
            // anyone can show content of this page
            \header('Access-Control-Allow-Origin: *', true);
        } elseif ($this->getContent()->containsPdf()) {
            $pdfFile = $this->getServicesContainer()->getPdfBody()->getPdfFile();
            $pdfFileBasename = \basename($pdfFile);
            if (\PHP_SAPI === 'cli') {
                return;
            }
            \header('Content-type: application/pdf');
            \header('Content-Length: ' . \filesize($pdfFile));
            \header("Content-Disposition: attachment; filename=\"$pdfFileBasename\"");
        }
    }
}