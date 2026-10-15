<?php
/**
 * Contact details and opening hours beside a form, with a full-width map below
 * (or the map beside the details when there is no form).
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
  </div>
  <div class="reveal" style="--d:.1s">
    <?php if (!$form): ?>
    <?php partial('map', ['shop' => $shop, 'class' => 'map--side']); ?>
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
<?php if ($form && $shop): ?>
<div class="reveal" style="margin-top:clamp(32px,4vw,48px)"><?php partial('map', ['shop' => $shop, 'class' => 'map--wide']); ?></div>
<?php endif; ?>
