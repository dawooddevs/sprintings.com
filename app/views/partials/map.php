<?php
/** Google map of the shop, loaded with the page (lazily, as it scrolls near), with a directions link. @var array $shop  @var string $class */
if (empty($shop)) return;
?>
<div class="map <?= e($class ?? '') ?>">
  <iframe src="<?= e(Content::mapEmbed($shop)) ?>" title="Map of <?= e(setting('site_name')) ?>, <?= e(Content::fullAddress($shop)) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
  <a class="btn btn--accent btn--sm map__directions" href="<?= e(Content::mapsDirections($shop)) ?>" target="_blank" rel="noopener">Directions<?= icon('arrow-up-right') ?></a>
</div>
