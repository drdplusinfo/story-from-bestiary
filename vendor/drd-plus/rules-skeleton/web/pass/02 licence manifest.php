<?php
/** @var \DrdPlus\RulesSkeleton\RulesController $controller */
?>
<h1>Prohlášení</h1>
<?php
$webName = $controller->getWebName();
$eShopUrl = $controller->getEshopUrl();
?>
<div class="row">
  <form class="manifest trial" action="/" method="post">
    <div class="col">
      <button type="submit" id="trial" name="trial" value="trial">Juknu na <?= $webName ?></button>
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
<div class="row">
  <form class="manifest buy" action="<?= $eShopUrl ?>">
    <div class="col">
      <button type="submit" id="buy" name="buy" value="buy">Koupím <?= $webName ?></button>
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
<div class="row">
  <form class="manifest owning" action="/" method="post"
        onsubmit="return window.confirm('A klidně to potvrdím dvakrát')">
    <div class="col">
      <button type="submit" id="confirm" name="confirm" value="submit">Vlastním <?= $webName ?></button>
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
  </form>
</div>