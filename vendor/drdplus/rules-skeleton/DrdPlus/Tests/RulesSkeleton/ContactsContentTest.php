<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;
use Gt\Dom\Element;

class ContactsContentTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function Proper_email_is_used_in_debug_contacts(): void
    {
        self::assertRegExp(
            '~[^[:alnum:]]info@drdplus[.]info[^[:alnum:]]~',
            $this->getDebugContactsContent(),
            'Email to info@drdplus.info has not been found in debug contacts template'
        );
    }

    private function getDebugContactsContent(): string
    {
        static $debugContactsContent;
        if ($debugContactsContent === null) {
            $debugContactsFile = $this->getVendorRoot() . '/drdplus/rules-skeleton-web/web/shared/debug_contacts.html';
            if (!\file_exists($debugContactsFile)) {
                throw new \LogicException(
                    "Missing file $debugContactsFile, run 'composer require --dev drdplus/rules-skeleton-web' first"
                );
            }
            $debugContactsContent = \file_get_contents($debugContactsFile);
        }

        return $debugContactsContent;
    }

    /**
     * @test
     */
    public function Proper_facebook_link_is_used_in_debug_contacts(): void
    {
        self::assertRegExp(
            '~[^[:alnum:]]https://www[.]facebook[.]com/drdplus[.]info[^[:alnum:]]~',
            $this->getDebugContactsContent(),
            'Link to facebook.com/drdplus.info has not been found in debug contacts template'
        );
    }

    /**
     * @test
     */
    public function Proper_rpg_forum_link_is_used_in_debug_contacts(): void
    {
        self::assertRegExp(
            '~[^[:alnum:]]https://rpgforum[.]cz/forum/viewtopic[.]php[?]f=238&t=14870[^[:alnum:]]~',
            $this->getDebugContactsContent(),
            'Link to RPG forum has not been found in debug contacts template'
        );
    }

    /**
     * @test
     */
    public function I_can_use_link_to_drdplus_info_email(): void
    {
        $debugContactsElement = $this->getDebugContactsElement();
        if (!$this->getTestsConfiguration()->hasDebugContacts()) {
            self::assertNull($debugContactsElement, 'Debug contacts have not been expected');

            return;
        }
        $this->guardDebugContactsAreNotEmpty($debugContactsElement);
        $anchors = $debugContactsElement->getElementsByTagName('a');
        self::assertNotEmpty($anchors, 'No anchors found in debug contacts');
        $mailTo = null;
        foreach ($anchors as $anchor) {
            $href = (string)$anchor->getAttribute('href');
            if (!$href || \strpos($href, 'mailto:') !== 0) {
                continue;
            }
            $mailTo = $href;
        }
        self::assertNotEmpty($mailTo, 'Missing mailto: in debug contacts ' . $debugContactsElement->innerHTML);
        self::assertSame('mailto:info@drdplus.info', $mailTo);
    }

    private function getDebugContactsElement(): ?Element
    {
        return $this->getHtmlDocument()->getElementById('debug_contacts');
    }

    private function guardDebugContactsAreNotEmpty(?Element $debugContactsElement): void
    {
        self::assertNotEmpty($debugContactsElement, 'Debug contacts has not been found by ID debug_contacts (debugContacts)');
        self::assertNotEmpty($debugContactsElement->textContent, 'Debug contacts are empty');
    }

}