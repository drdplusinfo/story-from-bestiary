<?php
/** @var \DrdPlus\RulesSkeleton\RulesController $controller */
if ($controller->getUsagePolicy()->trialJustExpired()) { ?>
  <div class="message warning">⌛ Čas tvého testování se naplnil ⌛</div><?php
} ?>