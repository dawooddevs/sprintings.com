<?php
/**
 * Site footer: "Call now" bar, brand, menus, social links, "Sign up for savings" form, the
 * guarantee, address and hours, legal links and credit.
 * @var string $path
 */
$phone = (string)setting('phone');
$shop = Content::shop();
$logo = setting('logo_light') ?: setting('logo');
$social = array_filter([
    'facebook' => ['Facebook', setting('social_facebook')],
    'instagram' => ['Instagram', setting('social_instagram')],
    'linkedin' => ['LinkedIn', setting('social_linkedin')],
    'x-social' => ['X', setting('social_x')],
], fn($s) => trim((string)$s[1]) !== '');
$cats = array_filter(Content::servicesByCategory());
?>
<?php if ($phone): ?>
<section class="callbar" aria-label="Call us">
  <div class="container callbar__inner">
    <a class="callbar__phone" href="<?= e(tel_href($phone)) ?>"><span class="ring"><?= icon('phone') ?></span><span><small>Call now</small><strong><?= e($phone) ?></strong></span></a>
    <a class="btn btn--ink btn--lg btn--magnetic" style="--btn-bg:var(--ink);--btn-fg:#fff" href="<?= e(url('request-a-quote/')) ?>">Get a Quote<?= icon('arrow-right', 'icon icon--arrow') ?></a>
  </div>
</section>
<?php endif; ?>
<footer class="site-footer on-dark">
  <div class="container footer__inner">
    <div class="footer__top">
      <div class="footer__brand">
        <?php if ($logo): ?><?= img($logo, setting('site_name')) ?><?php else: ?><p class="brand__word"><?= e(setting('site_name')) ?></p><?php endif; ?>
        <p><?= e(setting('brand_description') ?: setting('tagline')) ?></p>
        <?php if ($social): ?>
        <div class="social">
          <?php foreach ($social as $ic => [$label, $href]): ?><a href="<?= e($href) ?>" target="_blank" rel="noopener" aria-label="<?= e(setting('site_name')) ?> on <?= e($label) ?>"><?= icon($ic) ?></a><?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="footer__guarantee"><?= icon('shield-check') ?><div><strong><?= e(setting('guarantee_title')) ?></strong><span><?= e(setting('guarantee_text')) ?></span></div></div>
      </div>
      <nav aria-label="Footer">
        <h2>Explore</h2>
        <ul class="footer__links">
          <?php foreach (Content::menu('menu_footer') as $m): ?><li><a href="<?= e(url(ltrim($m['url'], '/'))) ?>"><?= e($m['label']) ?></a></li><?php endforeach; ?>
          <li><a href="<?= e(url('request-a-quote/')) ?>">Request a Quote</a></li>
        </ul>
      </nav>
      <nav aria-label="Product categories">
        <h2>Products</h2>
        <ul class="footer__links">
          <?php foreach ($cats as $cat => $items): ?><li><a href="<?= e(url('products/')) ?>#<?= e($cat) ?>"><?= e(Content::CATEGORIES[$cat]['label'] ?? $cat) ?></a></li><?php endforeach; ?>
        </ul>
      </nav>
      <div>
        <div class="newsletter">
          <h2><?= e(setting('newsletter_title')) ?></h2>
          <p>Get Sprintings savings and offers by email.</p>
          <?php partial('form', ['type' => 'newsletter', 'compact' => true]); ?>
        </div>
        <?php if ($shop): ?>
        <h2 style="margin-top:28px">Visit the shop</h2>
        <address style="font-style:normal">
          <a href="<?= e(Content::mapsDirections($shop)) ?>" target="_blank" rel="noopener"><?= e($shop['address']) ?><br><?= e($shop['city'] . ', ' . $shop['state'] . ' ' . $shop['zip']) ?></a>
        </address>
        <p style="margin-top:10px"><?php foreach (Content::hours($shop) as $i => $h): ?><?= $i ? '<br>' : '' ?><?= e($h['days']) ?>: <?= e($h['time']) ?><?php endforeach; ?></p>
        <?php endif; ?>
      </div>
    </div>
    <div class="footer__bottom">
      <p style="margin:0">© <?= e(setting('site_name')) ?> <?= date('Y') ?>. All rights reserved.<?php if ($credit = trim((string)setting('footer_credit'))): ?> <?= e($credit) ?><?php endif; ?></p>
      <ul>
        <li><a href="<?= e(url('privacy-policy/')) ?>">Privacy Policy</a></li>
        <li><a href="<?= e(url('terms-and-conditions/')) ?>">Terms &amp; Conditions</a></li>
        <li><a href="<?= e(url('sitemap/')) ?>">Sitemap</a></li>
      </ul>
    </div>
  </div>
  <div class="footer__mega" aria-hidden="true"><?= e(setting('tagline')) ?></div>
</footer>
