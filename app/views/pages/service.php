<?php
/**
 * Product page (/service/<slug>/), one template for every product: hero with gallery and options,
 * sales sections, specification sheet, FAQs, the product's own quote form in a sticky panel,
 * reviews and guarantee, contact details with map, and related products.
 * @var array $service
 */
$s = $service;
$title = $s['title'];
$cat = Content::CATEGORIES[$s['category']] ?? ['label' => Content::categoryLabel($s['category']), 'short' => '', 'icon' => 'printer'];
$gallery = Content::gallery($s);
$specs = Content::specs($s);
$options = lines($s['conditions']);
$design = lines($s['benefits']);
$faqs = array_values(array_filter(json_list($s['faqs']), fn($f) => !empty($f['q']) && !empty($f['a'])));
$related = Content::related($s, 4);
if (count($related) < 3) {
    foreach (Content::servicesBySlugs(csv_list(setting('featured_services'))) as $o) {
        if ($o['slug'] !== $s['slug'] && !in_array($o, $related, true)) $related[] = $o;
        if (count($related) >= 4) break;
    }
}
$formKey = QuoteForms::forProduct($s);
$phone = (string)setting('phone');
$intro = trim((string)$s['hero_text']) ?: (string)$s['excerpt'];

Seo::set([
    'title' => $s['meta_title'] ?: $title,
    'description' => $s['meta_description'] ?: $s['excerpt'],
    'canonical' => abs_url(Content::SERVICE_BASE . '/' . $s['slug'] . '/'),
    'image' => $gallery[0] ?? '',
]);
Seo::$breadcrumbs = [['Home', ''], ['Products', 'products/'], [$title, Content::SERVICE_BASE . '/' . $s['slug'] . '/']];
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $title,
    'serviceType' => $title . ' printing',
    'description' => $s['excerpt'] ?: $intro,
    'provider' => ['@id' => abs_url('') . '#organization'],
    'areaServed' => array_map(fn($c) => ['@type' => 'City', 'name' => $c], csv_list(setting('communities'))),
    'url' => abs_url(Content::SERVICE_BASE . '/' . $s['slug'] . '/'),
    'category' => $cat['label'],
];
if ($gallery) {
    $schema['image'] = array_map(fn($p) => abs_url($p), array_slice($gallery, 0, 3));
}
Seo::addSchema($schema);
$sec = 0;
$label = function (string $text) use (&$sec) { $sec++; return '<span class="p-section__label"><b>' . $sec . '</b>' . e($text) . '</span>'; };
?>
<section class="p-hero">
  <div class="container">
    <?php partial('crumbs'); ?>
    <div class="p-hero__grid">
      <div class="reveal">
        <a class="p-hero__cat" href="<?= e(url('products/')) ?>#<?= e($s['category']) ?>"><?= icon(Content::serviceIcon($s)) ?><?= e($cat['label']) ?></a>
        <h1><?= e($title) ?></h1>
        <?php if ($intro !== ''): ?><p class="lead"><?= e($intro) ?></p><?php endif; ?>
        <?php if ($options): ?>
        <div class="options">
          <p class="options__title">We have the perfect <?= e($title) ?> to fit your needs:</p>
          <ul class="chips"><?php foreach ($options as $o): ?><li class="chip"><?= e(mb_strlen($o) > 70 ? preg_replace('/\s+[–-]\s+.*/u', '', $o) : $o) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
        <div class="p-hero__actions">
          <a class="btn btn--accent btn--lg btn--magnetic" href="#quote">Get a Quote<?= icon('arrow-right', 'icon icon--arrow') ?></a>
          <?php if ($phone): ?><a class="btn btn--ghost btn--lg" href="<?= e(tel_href($phone)) ?>"><?= icon('phone') ?><?= e($phone) ?></a><?php endif; ?>
        </div>
        <p class="p-hero__guarantee"><?= icon('shield-check') ?>100% money refund if not satisfied · Local Long Beach team</p>
      </div>
      <div class="reveal reveal--scale" style="--d:.1s">
        <?php if ($gallery): ?>
        <div class="gallery" data-gallery>
          <div class="gallery__frame">
            <div class="gallery__main crop" aria-roledescription="carousel" aria-label="<?= e($title) ?> images">
              <?php foreach ($gallery as $i => $p): ?>
              <div class="gallery__slide<?= $i ? '' : ' is-active' ?>" data-slide<?= $i ? ' aria-hidden="true"' : '' ?> role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= count($gallery) ?>">
                <?= img($p, $title . (count($gallery) > 1 ? ' — image ' . ($i + 1) : ''), ['loading' => $i ? 'lazy' : 'eager', 'fetchpriority' => $i ? null : 'high', 'sizes' => '(max-width: 1023px) 92vw, 560px']) ?>
              </div>
              <?php endforeach; ?>
            </div>
            <?php if (count($gallery) > 1): ?>
            <button class="gallery__nav gallery__nav--prev" type="button" data-gallery-prev><?= icon('arrow-left') ?><span class="sr-only">Previous image</span></button>
            <button class="gallery__nav gallery__nav--next" type="button" data-gallery-next><?= icon('arrow-right') ?><span class="sr-only">Next image</span></button>
            <?php endif; ?>
          </div>
          <?php if (count($gallery) > 1): ?>
          <ul class="gallery__thumbs">
            <?php foreach ($gallery as $i => $p): ?>
            <li><button type="button" data-thumb="<?= $i ?>"<?= $i ? '' : ' aria-current="true"' ?>><?= img($p, '', ['alt-empty' => 1, 'sizes' => '76px', 'loading' => 'eager']) ?><span class="sr-only">Show image <?= $i + 1 ?></span></button></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="gallery__ph crop" aria-hidden="true"><?= icon(Content::serviceIcon($s)) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container p-body">
    <div class="p-sections">
      <?php if (trim(strip_tags((string)$s['what_is'])) !== ''): ?>
      <div class="p-section reveal">
        <?= $label('Overview') ?>
        <div class="prose"><?= Html::clean($s['what_is']) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($specs): ?>
      <div class="p-section reveal" id="specs">
        <?= $label('Specifications') ?>
        <h2><?= e($title) ?> options</h2>
        <div class="spec-sheet">
          <div class="spec-sheet__head"><span>Print spec sheet</span><span class="cmyk-bar" aria-hidden="true"><span></span><span></span><span></span><span></span></span></div>
          <div class="spec-sheet__grid">
            <?php foreach ($specs as $g): $long = array_filter($g['items'], fn($i) => mb_strlen($i) > 90); ?>
            <div class="spec<?= $long || count($specs) === 1 ? ' spec--wide' : '' ?>">
              <h3><?= e($g['title']) ?></h3>
              <?php if ($long): ?>
              <?php foreach ($g['items'] as $it): ?><p><?= e($it) ?></p><?php endforeach; ?>
              <?php else: ?>
              <ul><?php foreach ($g['items'] as $it): ?><li><?= e($it) ?></li><?php endforeach; ?></ul>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if (trim(strip_tags((string)$s['how_it_works'])) !== ''): ?>
      <div class="p-section reveal">
        <?= $label('Our service promise') ?>
        <div class="promise-card on-dark">
          <?php partial('seal'); ?>
          <div><h2>Our Service Promise</h2><div class="prose"><?= Html::clean($s['how_it_works']) ?></div></div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($design): ?>
      <div class="p-section reveal">
        <?= $label('Design details') ?>
        <h2><?= e($title) ?> design details</h2>
        <ul class="checklist"><?php foreach ($design as $d): ?><li><?= icon('check') ?><span><?= e($d) ?></span></li><?php endforeach; ?></ul>
      </div>
      <?php endif; ?>

      <?php if (trim(strip_tags((string)$s['what_to_expect'])) !== ''): ?>
      <div class="p-section reveal">
        <?= $label('Related products') ?>
        <div class="prose"><?= Html::clean($s['what_to_expect']) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($faqs): ?>
      <div class="p-section reveal">
        <?= $label('FAQ') ?>
        <h2>Frequently asked questions</h2>
        <?php partial('faq', ['faqs' => $faqs, 'id' => 'pfaq']); ?>
      </div>
      <?php endif; ?>
    </div>

    <aside class="quote-panel" id="quote" aria-label="Request a quote for <?= e($title) ?>">
      <div class="quote-card reveal">
        <div class="quote-card__head">
          <h2>If you’re interested in <?= e($title) ?>, reach out to us</h2>
          <p>Get a quote today. Our printing experts are ready to assist you with your order.</p>
          <span class="cmyk-bar" aria-hidden="true"><span></span><span></span><span></span><span></span></span>
        </div>
        <div class="quote-card__body">
          <?php partial('form', ['type' => $formKey, 'product' => $s, 'idp' => 'pq']); ?>
          <?php if ($phone): ?><p class="quote-card__phone">Prefer to talk? <a href="<?= e(tel_href($phone)) ?>"><?= icon('phone') ?> <?= e($phone) ?></a></p><?php endif; ?>
        </div>
      </div>
    </aside>
  </div>
</section>

<section class="section section--paper2">
  <div class="container">
    <?php partial('reviews'); ?>
    <div class="ways-grid" style="margin-top:clamp(40px,5vw,72px);align-items:stretch">
      <div class="promise-card on-dark reveal" style="align-items:center">
        <?php if ($ic = Content::mediaByName('Sprintings_Home_Icon_Four')): ?><?= img($ic, '', ['alt-empty' => 1, 'width' => 76, 'height' => 76, 'class' => 'seal-img']) ?><?php endif; ?>
        <div><h2><?= e(setting('promise_title')) ?></h2><p><?= e(setting('promise_text')) ?></p></div>
      </div>
      <div class="promise-card reveal" style="background:var(--orange);color:var(--ink);align-items:center;--d:.1s">
        <?php if ($ic = Content::mediaByName('Sprintings-Home_Icon_One')): ?><?= img($ic, '', ['alt-empty' => 1, 'width' => 76, 'height' => 76]) ?><?php endif; ?>
        <div><h2 style="color:var(--ink)"><?= str_replace('(S)', '(S)', e(setting('start_title'))) ?></h2><p><?= e(setting('start_text')) ?></p><a class="link-arrow" href="#quote" style="color:var(--ink);margin-top:10px">Request a quote<?= icon('arrow-right') ?></a></div>
      </div>
    </div>
  </div>
</section>

<?php if ($related): ?>
<section class="section">
  <div class="container">
    <div class="section-head section-head--split reveal">
      <div><p class="eyebrow">Keep exploring</p><h2>Related products</h2></div>
      <a class="btn btn--ghost" href="<?= e(url('products/')) ?>">All products<?= icon('arrow-right', 'icon icon--arrow') ?></a>
    </div>
    <div class="cards">
      <?php foreach (array_slice($related, 0, 4) as $i => $r): ?><div class="reveal" style="--d:<?= $i * .06 ?>s"><?php partial('product-card', ['s' => $r, 'showCat' => true]); ?></div><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section--white">
  <div class="container"><?php partial('contact-block', ['form' => null, 'title' => 'Questions about ' . $title . '?', 'intro' => 'Reach out to our Long Beach, CA team. We respond surprisingly quickly.']); ?></div>
</section>
