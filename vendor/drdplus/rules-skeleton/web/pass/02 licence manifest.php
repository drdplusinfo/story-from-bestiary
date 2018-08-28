<?php
/** @var \DrdPlus\RulesSkeleton\RulesController $controller */
$webName = $controller->getWebName();
$eShopUrl = $controller->getConfiguration()->getEshopUrl();
?>
<h1><?= $webName ?></h1>

<h3>Zkusím</h3>
<div class="row">
  <form class="manifest trial" action="/" method="post">
    <div class="col">
      <button class="btn btn-light" type="submit" id="trial" name="trial" value="trial">
        Juknu na <?= $webName ?>
      </button>
    </div>
    <div class="col">
      <ul>
        <li>
          <label for="trial">
            chci se na <strong><?= $webName ?></strong> jen na chvíli podívat, ať vím, o co jde
          </label>
        </li>
      </ul>
    </div>
  </form>
</div>

<h2>Koupím</h2>
<div class="row">
  <form class="manifest buy" action="<?= $eShopUrl ?>">
    <div class="col">
      <button class="btn btn-light" type="submit" id="buy" name="buy" value="buy">Koupím <?= $webName ?></button>
    </div>
    <div class="col">
      <ul>
        <li>
          <label for="buy">
            zatím nemám <strong><?= $webName ?></strong>, tak si je od Altaru koupím <span class="note">(doporučujeme PDF verzi)</span>
          </label>
        </li>
      </ul>
    </div>
  </form>
</div>

<h3>Vlastním</h3>
<div class="row">
  <div class="manifest owning">
    <div class="col">
      <button class="btn btn-light" type="button" id="confirm" data-toggle="modal" data-target="#confirm_ownership">
        Vlastním <?= $webName ?>
      </button>
    </div>
    <div class="col">
      <ul>
        <li>
          <label for="confirm">
            prohlašuji na svou čest, že vlastním legální kopii <strong><?= $webName ?></strong>
          </label>
        </li>
      </ul>
    </div>
  </div>
</div>

<div class="modal" id="confirmOwnership">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title note" id="confirmOwnershipModalLabel">Vlastním <?= $controller->getWebName() ?></div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        A klidně to potvrdím dvakrát
      </div>
      <div class="modal-footer">
        <form class="manifest owning" action="/" method="post">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Zavřít</button>
          <button type="submit" class="btn btn-primary" name="confirm" value="1">Vlastním</button>
        </form>
      </div>
    </div>
  </div>
</div>
