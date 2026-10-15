<?php
/**
 * Contact details, opening hours and a click-to-load map, beside a form.
 * @var string $form form key  @var array|null $product  @var string $title  @var string $intro  @var bool $dark
 */
$shop = Content::shop();
$phone = (string)setting('phone');
$email = (string)setting('email');
$hours = $shop ? Content::hours($shop) : [];
$title = $title ?? 'Request a quote or just say hello.';
$form = array_key_exists('form', get_defined_vars()) ? $form : 'contact';
?>
<div class="contact-grid">
  <div class="reveal">
    <p class="eyebrow">Get in touch</p>
    <h2><?= e($title) ?></h2>
    <?php if (!empty($intro)): ?><p class="lead"><?= e($intro) ?></p><?php endif; ?>
    <ul class="contact-list">
      <?php if ($phone): ?><li><?= icon('phone') ?><div><small>Phone</small><a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a></div></li><?php endif; ?>
      <?php if ($email): ?><li><?= icon('mail') ?><div><small>Email</small><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></div></li><?php endif; ?>
      <?php if ($shop): ?><li><?= icon('map-pin') ?><div><small>Address</small><a href="<?= e(Content::mapsDirections($shop)) ?>" target="_blank" rel="noopener"><?= e(Content::fullAddress($shop)) ?></a></div></li><?php endif; ?>
    </ul>
    <?php if ($hours): ?>
    <p class="open-now" data-open-now hidden></p>
    <table class="hours" data-hours>
      <caption class="sr-only">Opening hours</caption>
      <?php foreach (Content::hoursTable([$shop]) ?: [] as $row): $t = current($row['times']); ?>
      <tr data-iso="<?= e(implode(',', $row['iso'])) ?>" data-time="<?= e((string)$t) ?>"><th scope="row"><?= e($row['days']) ?></th><td><?= e($t ?: 'Closed') ?></td></tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
    <?php if ($form): ?>
    <?php if ($shop): ?>
    <div class="map" style="margin-top:24px" data-map data-src="<?= e(Content::mapEmbed($shop)) ?>">
      <div class="map__ph">
        <div>
          <div class="map__pin"><?= icon('map-pin') ?></div>
          <strong><?= e($shop['address']) ?></strong>
          <p><?= e($shop['city'] . ', ' . $shop['state'] . ' ' . $shop['zip']) ?></p>
          <div class="btn-row" style="justify-content:center">
            <button class="btn btn--accent btn--sm js-only" type="button" data-map-load>Show map</button>
            <a class="btn btn--ghost-light btn--ghost btn--sm" href="<?= e(Content::mapsDirections($shop)) ?>" target="_blank" rel="noopener">Directions<?= icon('arrow-up-right') ?></a>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
  <div class="reveal" style="--d:.1s">
    <?php if (!$form): ?>
    <?php if ($shop): ?>
    <div class="map" style="aspect-ratio:auto;min-height:420px;height:100%" data-map data-src="<?= e(Content::mapEmbed($shop)) ?>">
      <div class="map__ph">
        <div>
          <div class="map__pin"><?= icon('map-pin') ?></div>
          <strong><?= e($shop['address']) ?></strong>
          <p><?= e($shop['city'] . ', ' . $shop['state'] . ' ' . $shop['zip']) ?></p>
          <div class="btn-row" style="justify-content:center">
            <button class="btn btn--accent btn--sm js-only" type="button" data-map-load>Show map</button>
            <a class="btn btn--ghost-light btn--ghost btn--sm" href="<?= e(Content::mapsDirections($shop)) ?>" target="_blank" rel="noopener">Directions<?= icon('arrow-up-right') ?></a>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <div class="quote-card">
      <div class="quote-card__head">
        <h3 style="color:#fff;font-size:1.45rem;margin-bottom:.2em"><?= e($form === 'contact' ? 'Send us a message' : 'Request a quote') ?></h3>
        <p><?= e(setting('cta_copy')) ?></p>
        <span class="cmyk-bar" aria-hidden="true"><span></span><span></span><span></span><span></span></span>
      </div>
      <div class="quote-card__body"><?php partial('form', ['type' => $form, 'product' => $product ?? null, 'idp' => $form . '-cb']); ?></div>
    </div>
    <?php endif; ?>
  </div>
</div>
