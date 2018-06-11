<?php
global $testsConfiguration;
$testsConfiguration = new \DrdPlus\Tests\RulesSkeleton\TestsConfiguration();
$testsConfiguration->disableHasLinksToJournals();
$testsConfiguration->disableHasLinkToSingleJournal();
$testsConfiguration->disableHasTables();
$testsConfiguration->disableHasNotes();
$testsConfiguration->disableHasProtectedAccess();
$testsConfiguration->disableHasCustomBodyContent();
$testsConfiguration->disableHasAuthors();
$testsConfiguration->disableHasDebugContacts();
$testsConfiguration->disableHasCharacterSheet();
$testsConfiguration->disableCanBeBoughtOnEshop();
$testsConfiguration->setBlockNamesToExpectedContent([]);
$testsConfiguration->setExpectedWebName('Příběh z DrD+ bestiáře');
$testsConfiguration->setExpectedPageTitle('⛏️ Příběh z DrD+ bestiáře');
