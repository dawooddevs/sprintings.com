<?php
/**
 * Homepage. Every text comes from Settings → Homepage (seeded from the old homepage).
 * Sections: hero, product marquee, "Need printing?", problems band, speed + 3-step process,
 * "Every way you need it", product bento, reviews, guarantee, contact + map.
 */
Seo::set([
    'title' => setting('site_name') . ' — ' . setting('tagline') . ' Printing in Long Beach, CA',
    'description' => setting('seo_default_description'),
    'canonical' => abs_url(''),
    'image' => setting('seo_og_image'),
]);
$m = fn(string $name) => Content::mediaByName($name);
$values = json_list(setting('home_values'));
$process = json_list(setting('home_process'));
$ways = json_list(setting('home_ways'));
$badges = lines(setting('hero_badges'));
$byCat = array_filter(Content::servicesByCategory());
$all = Content::services();
$stack = array_values(array_filter([$m('Sprinting_brochure_image'), $m('Sprinting_flyer_image'), $m('Sprinting_magazine_page')]));
$valueIcons = [$m('Sprintings-Home_Icon_Three'), $m('Sprintings-Home_Icon_Two'), $m('Sprintings-Home_Icon_One')];
$stepIcons = [$m('Sprintings_Home_Icon_Five'), $m('Sprintings_Home_Icon_Six'), $m('Sprintings_Home_Icon_Seven')];
$wayIcons = ['book-open', 'palette', 'layers'];
$phone = (string)setting('phone');
// Bento: one tile per category, sized by how many products it holds
$tiles = [];
foreach ($byCat as $cat => $items) {
    $img = '';
    foreach ($items as $s) {
        if ($s['image'] && (int)$s['featured']) { $img = $s['image']; break; }
    }
    if (!$img) {
        foreach ($items as $s) { if ($s['image']) { $img = $s['image']; break; } }
    }
    $tiles[] = ['cat' => $cat, 'items' => $items, 'img' => $img];
}
usort($tiles, fn($a, $b) => count($b['items']) <=> count($a['items']));
$sizes = ['tile--xl', 'tile--lg', 'tile', 'tile', 'tile', 'tile--wide', 'tile--wide'];
?>
<section class="hero">
  <div class="container hero__grid">
    <div class="hero__copy">
      <p class="hero__kicker reveal"><span class="dot"><?= icon('map-pin') ?></span>Your local print shop in Long Beach, CA</p>
      <?php $words = preg_split('/(?<=\.)\s+/', trim((string)setting('hero_line1'))) ?: [setting('hero_line1')]; ?>
      <h1 class="reveal" style="--d:.05s">
        <?php foreach ($words as $i => $w): $last = $i === count($words) - 1; ?>
        <span class="line<?= $last && count($words) > 1 ? ' line--right' : '' ?>"><?= e($w) ?><?php if ($last && count($words) > 1): ?> <span class="speed-lines" aria-hidden="true"><i></i><i></i><i></i></span><?php endif; ?></span>
        <?php endforeach; ?>
      </h1>
      <p class="hero__sub reveal" style="--d:.1s"><?= e(setting('hero_line2')) ?></p>
      <p class="lead reveal" style="--d:.15s"><?= e(setting('hero_copy')) ?></p>
      <div class="btn-row reveal" style="--d:.2s">
        <a class="btn btn--accent btn--lg btn--magnetic" href="<?= e(url('request-a-quote/')) ?>">Get A Quote Today<?= icon('arrow-right', 'icon icon--arrow') ?></a>
        <a class="btn btn--ghost btn--lg" href="<?= e(url('products/')) ?>">Explore Our Products</a>
      </div>
      <?php if ($badges): ?>
      <ul class="hero__badges reveal" style="--d:.25s">
        <?php foreach ($badges as $b): ?><li><?= icon('check-circle') ?><?= e($b) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
    <div class="hero__visual reveal reveal--scale" style="--d:.1s">
      <div class="stack" data-parallax>
        <?php foreach ($stack as $i => $p): ?>
        <div class="stack__sheet"><?= img($p, '', ['alt-empty' => 1, 'loading' => $i === 1 ? 'eager' : 'lazy', 'fetchpriority' => $i === 1 ? 'high' : null, 'sizes' => '(max-width: 1023px) 50vw, 320px']) ?></div>
        <?php endforeach; ?>
        <div class="stack__sticker" aria-hidden="true"><span>Same Day<small>Service</small></span></div>
        <div class="stack__ticket"><?= icon('upload') ?><span><strong>Upload artwork</strong>Get a proof &amp; full quote</span></div>
        <span class="reg hero__reg" aria-hidden="true"></span>
        <span class="cmyk-bar hero__bar" aria-hidden="true"><span></span><span></span><span></span><span></span></span>
      </div>
    </div>
  </div>
</section>

<?php if ($all): ?>
<section class="marquee" aria-label="Products we print">
  <div class="marquee__track">
    <?php for ($g = 0; $g < 2; $g++): ?>
    <div class="marquee__group"<?= $g ? ' aria-hidden="true"' : '' ?>>
      <?php $seen = []; foreach ($all as $s): $label = $s['menu_label'] ?: $s['title']; if (isset($seen[$label])) continue; $seen[$label] = 1; ?>
      <a href="<?= e(Content::serviceUrl($s)) ?>"<?= $g ? ' tabindex="-1"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endfor; ?>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <div class="intro-grid">
      <div class="intro-art reveal reveal--left">
        <?php if (setting('about_image')): ?><?= img(setting('about_image'), 'Illustration of a print shop printer', ['sizes' => '(max-width: 1023px) 90vw, 520px']) ?><?php endif; ?>
      </div>
      <div class="reveal">
        <p class="eyebrow">Why Sprintings</p>
        <h2><?= str_replace('(S)Print', '<span class="sprint-s">(S)</span>Print', e(setting('home_intro_title'))) ?></h2>
        <p class="lead"><?= e(setting('home_intro_copy')) ?></p>
        <a class="link-arrow" href="<?= e(url('products/')) ?>">Explore Our Products<?= icon('arrow-right') ?></a>
      </div>
    </div>
    <?php if ($values): ?>
    <div class="values">
      <?php foreach ($values as $i => $v): ?>
      <article class="value reveal" style="--d:<?= $i * .08 ?>s">
        <div class="value__icon"><?php if (!empty($valueIcons[$i])): ?><?= img($valueIcons[$i], '', ['alt-empty' => 1, 'width' => 44, 'height' => 44]) ?><?php endif; ?></div>
        <h3><?= e($v['title'] ?? '') ?></h3>
        <p><?= e($v['text'] ?? '') ?></p>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($problems = trim((string)setting('home_problems'))):
    $parts = preg_split('/(?<=[?!])\s+/', $problems); ?>
<section class="section band on-dark">
  <?php if ($bg = $m('Sprintings_Home_Img_Two')): ?><div class="band__bg" aria-hidden="true"><?= img($bg, '', ['alt-empty' => 1, 'sizes' => '100vw']) ?></div><?php endif; ?>
  <div class="container band__inner">
    <div class="reveal">
      <p class="eyebrow">Problems, solved</p>
      <p class="band__quote"><?= e($parts[0] ?? '') ?> <em><?= e($parts[1] ?? '') ?></em></p>
      <?php if (count($parts) > 2): ?><p><?= e(implode(' ', array_slice($parts, 2))) ?></p><?php endif; ?>
    </div>
    <div class="reveal" style="--d:.1s"><a class="btn btn--accent btn--lg btn--magnetic" href="<?= e(url('request-a-quote/')) ?>">Get Started<?= icon('arrow-right', 'icon icon--arrow') ?></a></div>
  </div>
</section>
<?php endif; ?>

<?php if ($process): ?>
<section class="section on-dark" style="background:var(--teal-950)">
  <div class="container">
    <div class="speed-head reveal">
      <?php if ($ic = $m('Sprintings_Home_Icon_Four')): ?><?= img($ic, '', ['alt-empty' => 1, 'width' => 72, 'height' => 72]) ?><?php endif; ?>
      <div>
        <p class="eyebrow"><?= e(setting('home_speed_title')) ?></p>
        <h2><?= str_replace('(S)Print', '<span class="sprint-s">(S)</span>Print', e(setting('home_process_title'))) ?></h2>
      </div>
    </div>
    <p class="lead reveal" style="margin:-24px 0 40px"><?= e(setting('home_process_copy')) ?></p>
    <div style="position:relative" data-run>
    <div class="process__track" aria-hidden="true"><span></span></div>
    <ol class="process">
      <?php foreach ($process as $i => $st): ?>
      <li class="process__step reveal" style="--d:<?= .1 + $i * .15 ?>s">
        <div class="process__num"><?php if (!empty($stepIcons[$i])): ?><?= img($stepIcons[$i], '', ['alt-empty' => 1, 'width' => 58, 'height' => 58]) ?><?php endif; ?><b aria-hidden="true"><?= $i + 1 ?></b></div>
        <div><h3><?= str_replace('(S)Print', '<span class="sprint-s">(S)</span>Print', e($st['title'] ?? '')) ?></h3><p><?= e($st['text'] ?? '') ?></p></div>
      </li>
      <?php endforeach; ?>
    </ol>
    </div>
    <div class="process-cta reveal">
      <a class="btn btn--accent btn--lg btn--magnetic" href="<?= e(url('request-a-quote/')) ?>"><?= icon('upload') ?>Upload Artwork &amp; Get a Quote</a>
      <?php if ($phone): ?><p>Or call <a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a></p><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($ways): ?>
<section class="section">
  <div class="container ways-grid">
    <div class="reveal">
      <p class="eyebrow">Our services</p>
      <h2><?= e(setting('home_ways_title')) ?></h2>
      <p class="lead"><?= e(setting('home_ways_copy')) ?></p>
      <div class="ways">
        <?php foreach ($ways as $i => $w): ?>
        <a class="way" href="<?= e(url(ltrim((string)($w['link'] ?? 'products/'), '/'))) ?>" style="text-decoration:none">
          <span class="way__icon"><?= icon($wayIcons[$i % 3]) ?></span>
          <h3><?= e($w['title'] ?? '') ?></h3>
          <p><?= e($w['text'] ?? '') ?></p>
          <span class="link-arrow" style="font-size:.92rem">Learn More<?= icon('arrow-right') ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="ways-art reveal reveal--scale">
      <?php if (setting('testimonials_image')): ?><?= img(setting('testimonials_image'), 'Paper stock, binding and print finishing', ['sizes' => '(max-width: 1023px) 90vw, 560px']) ?><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($tiles): ?>
<section class="section section--paper2" id="products">
  <div class="container">
    <div class="section-head section-head--split reveal">
      <div>
        <p class="eyebrow">Everything we print</p>
        <h2><?= count($all) ?> products. One local team.</h2>
        <p class="lead">Envelopes, brochures, flyers, cards and much more. If you can imagine it, we’ll print it.</p>
      </div>
      <a class="btn btn--ghost" href="<?= e(url('products/')) ?>">View all products<?= icon('arrow-right', 'icon icon--arrow') ?></a>
    </div>
    <div class="bento">
      <?php foreach ($tiles as $i => $t): $c = Content::CATEGORIES[$t['cat']] ?? ['label' => $t['cat'], 'icon' => 'printer', 'blurb' => '', 'short' => $t['cat']]; ?>
      <a class="tile <?= $sizes[$i] ?? 'tile' ?> reveal" style="--d:<?= ($i % 3) * .06 ?>s" href="<?= e(url('products/')) ?>#<?= e($t['cat']) ?>">
        <?php if ($t['img']): ?><div class="tile__img" aria-hidden="true"><?= img($t['img'], '', ['alt-empty' => 1, 'sizes' => $i === 0 ? '(max-width: 1023px) 100vw, 700px' : '(max-width: 1023px) 100vw, 420px']) ?></div><?php endif; ?>
        <span class="tile__go" aria-hidden="true"><?= icon('arrow-up-right') ?></span>
        <div class="tile__body">
          <span class="tile__label"><?= icon($c['icon']) ?><?= e(plural(count($t['items']), 'product', 'products')) ?></span>
          <h3><?= e($c['label']) ?></h3>
          <?php if ($i < 2): ?><p><?= e($c['blurb']) ?></p><?php endif; ?>
          <ul class="tile__list">
            <?php $seen = []; foreach (array_slice($t['items'], 0, $i < 2 ? 6 : 3) as $s): $l = $s['menu_label'] ?: $s['title']; if (isset($seen[$l])) continue; $seen[$l] = 1; ?><li><?= e($l) ?></li><?php endforeach; ?>
          </ul>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <?php partial('reviews'); ?>
    <div style="margin-top:clamp(48px,6vw,80px)"><?php partial('guarantee'); ?></div>
  </div>
</section>

<section class="section section--white" id="contact">
  <div class="container">
    <?php partial('contact-block', ['form' => 'contact', 'title' => setting('cta_headline'), 'intro' => 'Do you have a printing idea in mind? Contact us today so we can help you with your project.']); ?>
  </div>
</section>
