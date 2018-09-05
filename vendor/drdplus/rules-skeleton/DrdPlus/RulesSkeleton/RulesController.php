<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\Web\Content;

/**
 * @method ServicesContainer getServicesContainer
 */
class RulesController extends \DrdPlus\FrontendSkeleton\FrontendController
{
    public function __construct(ServicesContainer $servicesContainer)
    {
        parent::__construct($servicesContainer);
    }

    /**
     * @return Content|\DrdPlus\FrontendSkeleton\Web\Content
     */
    public function getContent(): \DrdPlus\FrontendSkeleton\Web\Content
    {
        if ($this->content === null) {
            if (($_SERVER['QUERY_STRING'] ?? false) === 'pdf'
                && $this->getServicesContainer()->getPdfBody()->getPdfFile()
            ) {
                $this->content = new Content(
                    $this->getServicesContainer()->getHtmlHelper(),
                    $this->getServicesContainer()->getWebVersions(),
                    $this->getServicesContainer()->getEmptyHead(),
                    $this->getServicesContainer()->getEmptyMenu(),
                    $this->getServicesContainer()->getPdfBody(),
                    $this->getServicesContainer()->getEmptyWebCache(),
                    Content::PDF,
                    $this->getRedirect()
                );
            } elseif ($this->getServicesContainer()->getRequest()->getValueFromGet(Request::TABLES) !== null
                || $this->getServicesContainer()->getRequest()->getValueFromGet(Request::TABULKY)
            ) { // we do not require licence confirmation for tables only
                $this->content = new Content(
                    $this->getServicesContainer()->getHtmlHelper(),
                    $this->getServicesContainer()->getWebVersions(),
                    $this->getServicesContainer()->getHeadForTables(),
                    $this->getServicesContainer()->getMenu(),
                    $this->getServicesContainer()->getTablesBody(),
                    $this->getServicesContainer()->getTablesWebCache(),
                    Content::TABLES,
                    $this->getRedirect()
                );
            } elseif (!$this->solveAccess()) {
                $this->content = new Content(
                    $this->getServicesContainer()->getHtmlHelper(),
                    $this->getServicesContainer()->getWebVersions(),
                    $this->getServicesContainer()->getHead(),
                    $this->getServicesContainer()->getMenu(),
                    $this->getServicesContainer()->getPassBody(),
                    $this->getServicesContainer()->getPassWebCache(),
                    Content::PASS,
                    $this->getRedirect()
                );
            } else {
                $this->content = new Content(
                    $this->getServicesContainer()->getHtmlHelper(),
                    $this->getServicesContainer()->getWebVersions(),
                    $this->getServicesContainer()->getHead(),
                    $this->getServicesContainer()->getMenu(),
                    $this->getServicesContainer()->getBody(),
                    $this->getServicesContainer()->getPassedWebCache(),
                    Content::PASSED,
                    $this->getRedirect()
                );
            }
        }

        return $this->content;
    }

    private function solveAccess(): bool
    {
        $visitorCanAccessContent = !$this->getServicesContainer()->getConfiguration()->hasProtectedAccess();
        if (!$visitorCanAccessContent) {
            $usagePolicy = $this->getServicesContainer()->getUsagePolicy();
            $visitorCanAccessContent = $usagePolicy->isVisitorBot();
            if (!$visitorCanAccessContent) {
                if ($this->getServicesContainer()->getRequest()->getValueFromPost('confirm')) {
                    $visitorCanAccessContent = $usagePolicy->confirmOwnershipOfVisitor(new \DateTime('+1 year'));
                }
                if (!$visitorCanAccessContent && $this->getServicesContainer()->getRequest()->getValueFromPost('trial')) {
                    $visitorCanAccessContent = $this->activateTrial($this->getServicesContainer()->getNow());
                }
                if (!$visitorCanAccessContent) {
                    $visitorCanAccessContent = $usagePolicy->hasVisitorConfirmedOwnership();
                    if (!$visitorCanAccessContent) {
                        $visitorCanAccessContent = $usagePolicy->isVisitorUsingValidTrial();
                    }
                }
            }
        }

        return $visitorCanAccessContent;
    }

    protected function activateTrial(\DateTime $now): bool
    {
        $trialExpiration = (clone $now)->modify('+4 minutes');
        $visitorCanAccessContent = $this->getServicesContainer()->getUsagePolicy()->activateTrial($trialExpiration);
        if ($visitorCanAccessContent) {
            $at = $trialExpiration->getTimestamp() + 1; // one second "insurance" overlap
            $afterSeconds = $at - $now->getTimestamp();
            $this->setRedirect(
                new \DrdPlus\FrontendSkeleton\Redirect(
                    "/?{$this->getServicesContainer()->getUsagePolicy()->getTrialExpiredAtName()}={$at}",
                    $afterSeconds
                )
            );
        }

        return $visitorCanAccessContent;
    }

    public function sendCustomHeaders(): void
    {
        if (\PHP_SAPI === 'cli') {
            return;
        }
        if ($this->getContent()->containsTables()) {
            // anyone can show content of this page
            \header('Access-Control-Allow-Origin: *', true);
        } elseif ($this->getContent()->containsPdf()) {
            \header('Content-type: application/pdf');
            $pdfFile = $this->getServicesContainer()->getPdfBody()->getPdfFile();
            \header('Content-Length: ' . \filesize($pdfFile));
            $pdfFileBasename = \basename($pdfFile);
            \header("Content-Disposition: attachment; filename=\"$pdfFileBasename\"");
        }
    }
}