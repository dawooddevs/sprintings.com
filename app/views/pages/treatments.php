<?php
/**
 * Products index (/products/, template "treatments"): every product, filterable by category,
 * with search. Without JavaScript all categories are listed in order.
 * @var array $page
 */
Seo::set(['title' => $page['meta_title'] ?: $page['title'], 'description' => $page['meta_description'] ?: $page['intro'], 'canonical' => abs_url($page['slug'] . '/'), 'image' => $page['image'] ?: setting('seo_og_image')]);
$byCat = array_filter(Content::servicesByCategory());
$total = array_sum(array_map('count', $byCat));
$list = [];
foreach ($byCat as $items) {
    foreach ($items as $i => $s) {
        $list[] = ['@type' => 'ListItem', 'position' => count($list) + 1, 'url' => abs_url(Content::SERVICE_BASE . '/' . $s['slug'] . '/'), 'name' => $s['title']];
    }
}
Seo::addSchema(['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => 'Sprintings products', 'numberOfItems' => $total, 'itemListElement' => $list]);
partial('page-head', ['title' => $page['title'], 'intro' => $page['intro'], 'eyebrow' => $page['eyebrow'] ?: 'Everything we print', 'crumbs' => [[$page['title'], $page['slug'] . '/']], 'dark' => true]);
?>
<div class="filters" data-filter>
  <div class="container filters__inner">
    <label class="filters__search"><span class="sr-only">Search products</span><?= icon('search') ?><input type="search" placeholder="Search <?= $total ?> products" data-filter-search autocomplete="off"></label>
    <div class="filters__tabs js-only" role="group" aria-label="Filter by category">
      <button class="ftab" type="button" aria-pressed="true" data-filter-cat="">All <small><?= $total ?></small></button>
      <?php foreach ($byCat as $cat => $items): ?>
      <button class="ftab" type="button" aria-pressed="false" data-filter-cat="<?= e($cat) ?>"><?= icon(Content::CATEGORIES[$cat]['icon'] ?? 'printer') ?><?= e(Content::CATEGORIES[$cat]['short'] ?? $cat) ?> <small><?= count($items) ?></small></button>
      <?php endforeach; ?>
    </div>
    <p class="sr-only" aria-live="polite" data-filter-live></p>
  </div>
</div>
<section class="section" style="padding-top:0">
  <div class="container">
    <?php foreach ($byCat as $cat => $items): $c = Content::CATEGORIES[$cat] ?? ['label' => $cat, 'icon' => 'printer', 'blurb' => '']; ?>
    <div class="cat-block" id="<?= e($cat) ?>" data-cat-block>
      <div class="cat-block__head reveal">
        <h2><?= icon($c['icon']) ?><?= e($c['label']) ?></h2>
        <p><?= e($c['blurb']) ?></p>
      </div>
      <div class="cards">
        <?php foreach ($items as $i => $s): ?><div class="reveal" style="--d:<?= ($i % 4) * .05 ?>s" data-card-wrap><?php partial('product-card', ['s' => $s]); ?></div><?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <div class="no-results" data-no-results hidden>
      <h2>No products match your search</h2>
      <p>If you can imagine it, we’ll print it. Tell us what you need.</p>
      <a class="btn btn--accent" href="<?= e(url('request-a-quote/')) ?>">Request a custom quote</a>
    </div>
    <?php if (trim(strip_tags((string)$page['content'])) !== ''): ?><div class="prose"><?= Html::clean($page['content']) ?></div><?php endif; ?>
    <?php partial('cta-band'); ?>
  </div>
</section>
