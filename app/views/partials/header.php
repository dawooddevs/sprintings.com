<?php
/**
 * Site header: logo, main menu (Settings → Menus) with the Products mega-menu (every product by
 * category, with search-as-you-type), click-to-call phone and the "Get a Quote" button.
 * Below 1024px the menu moves into a slide-in drawer.
 * @var string $path
 */
$phone = (string)setting('phone');
$menu = Content::menu('menu_header');
$cur = fn($u) => rtrim($path ?? '', '/') === rtrim($u, '/') || ($u !== '/' && str_starts_with(($path ?? '') . '/', rtrim($u, '/') . '/')) ? ' aria-current="page"' : '';
$isProducts = fn($u) => rtrim($u, '/') === '/products';
$groups = [];
foreach (Content::servicesByCategory() as $cat => $items) {
    $items = array_values(array_filter($items, fn($s) => (int)$s['show_in_menu'] === 1));
    if ($items) {
        $groups[$cat] = $items;
    }
}
$count = array_sum(array_map('count', $groups));
$productsActive = str_starts_with($path ?? '', '/' . Content::SERVICE_BASE . '/') || rtrim($path ?? '', '/') === '/products';
$logo = setting('logo_light') ?: setting('logo');

$megaCols = function (string $idPrefix) use ($groups) {
    ob_start(); ?>
    <div class="mega__cols" data-mega-results>
      <?php foreach ($groups as $cat => $items): ?>
      <div class="mega__group" data-mega-group>
        <h3><?= icon(Content::CATEGORIES[$cat]['icon'] ?? 'printer') ?><?= e(Content::CATEGORIES[$cat]['short'] ?? Content::categoryLabel($cat)) ?></h3>
        <ul>
          <?php foreach ($items as $s): $label = $s['menu_label'] ?: $s['title']; ?>
          <li data-mega-item data-name="<?= e(mb_strtolower($label . ' ' . $s['title'] . ' ' . Content::categoryLabel($cat))) ?>"><a href="<?= e(Content::serviceUrl($s)) ?>"><span data-label><?= e($label) ?></span></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="mega__empty" data-mega-empty hidden>No products match. <a href="<?= e(url('request-a-quote/')) ?>">Ask us for a custom quote</a> — if you can imagine it, we’ll print it.</p>
    <?php return ob_get_clean();
};
?>
<header class="site-header" data-header>
  <div class="container header__inner">
    <a class="brand" href="<?= e(url('')) ?>" aria-label="<?= e(setting('site_name')) ?> — home">
      <?php if ($logo): ?><?= img($logo, setting('site_name'), ['loading' => 'eager', 'fetchpriority' => 'high']) ?><?php else: ?><span class="brand__word"><?= e(setting('site_name')) ?></span><?php endif; ?>
    </a>
    <nav aria-label="Main">
      <ul class="nav">
        <?php foreach ($menu as $m): ?>
        <li>
          <?php if ($isProducts($m['url'])): ?>
          <a class="nav__link no-js-only" href="<?= e(url(ltrim($m['url'], '/'))) ?>"<?= $productsActive ? ' aria-current="page"' : '' ?>><?= e($m['label']) ?></a>
          <button class="nav__link js-only" type="button" aria-expanded="false" aria-controls="mega" data-mega-toggle<?= $productsActive ? ' aria-current="page"' : '' ?>><?= e($m['label']) ?><?= icon('chevron-down') ?></button>
          <?php else: ?>
          <a class="nav__link" href="<?= e(url(ltrim($m['url'], '/'))) ?>"<?= $cur($m['url']) ?>><?= e($m['label']) ?></a>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="header__actions">
      <?php if ($phone): ?>
      <a class="header__phone" href="<?= e(tel_href($phone)) ?>" aria-label="Call us at <?= e($phone) ?>"><span class="ring"><?= icon('phone') ?></span><span class="label"><small>Call now</small><?= e($phone) ?></span></a>
      <?php endif; ?>
      <a class="btn btn--accent btn--magnetic" href="<?= e(url('request-a-quote/')) ?>"<?= $cur('/request-a-quote/') ?>>Get a Quote<?= icon('arrow-right', 'icon icon--arrow') ?></a>
      <button class="menu-toggle js-only" type="button" aria-expanded="false" aria-controls="drawer" data-drawer-open><?= icon('menu') ?><span class="sr-only">Open menu</span></button>
      <a class="menu-toggle no-js-only" href="#drawer"><?= icon('menu') ?><span class="sr-only">Open menu</span></a>
    </div>
  </div>

  <div class="mega" id="mega" data-mega hidden>
    <div class="container mega__inner">
      <div>
        <div class="mega__top">
          <label class="mega__search"><span class="sr-only">Search products</span><?= icon('search') ?><input type="search" placeholder="Search <?= (int)$count ?> products — try “cards” or “labels”" autocomplete="off" data-mega-search></label>
          <span class="mega__count" data-mega-count aria-live="polite"><?= (int)$count ?> products</span>
        </div>
        <?= $megaCols('mega') ?>
      </div>
      <aside class="mega__promo on-dark">
        <?php if ($promo = Content::mediaByName('Sprinting_magazine_page') ?: setting('seo_og_image')): ?><?= img($promo, '', ['alt-empty' => 1, 'sizes' => '300px']) ?><?php endif; ?>
        <span class="cmyk-bar" aria-hidden="true" style="margin-bottom:14px"><span></span><span></span><span></span><span></span></span>
        <h3>Not sure what you need?</h3>
        <p>Our printing experts are ready to help you bring your ideas to life.</p>
        <div class="btn-row"><a class="btn btn--accent btn--sm" href="<?= e(url('request-a-quote/')) ?>">Get a Quote</a><a class="link-arrow" href="<?= e(url('products/')) ?>">All products<?= icon('arrow-right') ?></a></div>
      </aside>
    </div>
  </div>
</header>

<div class="drawer" id="drawer" data-drawer>
  <div class="drawer__scrim" data-drawer-close></div>
  <div class="drawer__panel" role="dialog" aria-modal="true" aria-label="Menu">
    <div class="drawer__head">
      <a class="brand" href="<?= e(url('')) ?>"><?php if ($logo): ?><?= img($logo, setting('site_name'), ['loading' => 'lazy']) ?><?php else: ?><span class="brand__word"><?= e(setting('site_name')) ?></span><?php endif; ?></a>
      <button class="drawer__close js-only" type="button" data-drawer-close><?= icon('x') ?><span class="sr-only">Close menu</span></button>
      <a class="drawer__close no-js-only" href="#"><?= icon('x') ?><span class="sr-only">Close menu</span></a>
    </div>
    <div class="drawer__body">
      <ul class="drawer__nav">
        <li><a href="<?= e(url('')) ?>"<?= $cur('/') ?>>Home</a></li>
        <?php foreach ($menu as $m): ?>
        <?php if ($isProducts($m['url'])): ?>
        <li>
          <a class="no-js-only" href="<?= e(url(ltrim($m['url'], '/'))) ?>"><?= e($m['label']) ?><?= icon('arrow-right') ?></a>
          <button class="js-only" type="button" aria-expanded="false" aria-controls="drawer-products" data-drawer-products><?= e($m['label']) ?><?= icon('chevron-down') ?></button>
          <div class="drawer__products" id="drawer-products" hidden data-mega-scope>
            <label class="mega__search"><span class="sr-only">Search products</span><?= icon('search') ?><input type="search" placeholder="Search products" autocomplete="off" data-mega-search></label>
            <div data-mega-clone></div>
            <p><a class="link-arrow" href="<?= e(url('products/')) ?>">View all products<?= icon('arrow-right') ?></a></p>
          </div>
        </li>
        <?php else: ?>
        <li><a href="<?= e(url(ltrim($m['url'], '/'))) ?>"<?= $cur($m['url']) ?>><?= e($m['label']) ?><?= icon('arrow-right') ?></a></li>
        <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="drawer__foot">
      <a class="btn btn--accent btn--lg btn--block" href="<?= e(url('request-a-quote/')) ?>">Get a Quote<?= icon('arrow-right', 'icon icon--arrow') ?></a>
      <?php if ($phone): ?><a class="btn btn--ghost btn--lg btn--block" href="<?= e(tel_href($phone)) ?>"><?= icon('phone') ?>Call <?= e($phone) ?></a><?php endif; ?>
    </div>
  </div>
</div>
