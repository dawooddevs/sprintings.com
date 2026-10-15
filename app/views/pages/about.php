<?php
/** About Us (/about-us/, template "about"). Text from the old About page. @var array $page */
Seo::set(['title' => $page['meta_title'] ?: $page['title'], 'description' => $page['meta_description'] ?: $page['intro'], 'canonical' => abs_url($page['slug'] . '/'), 'image' => $page['image']]);
Seo::addSchema(['@context' => 'https://schema.org', '@type' => 'AboutPage', 'name' => $page['title'], 'url' => abs_url($page['slug'] . '/'), 'about' => ['@id' => abs_url('') . '#organization']]);
$pillars = [
    ['layers', 'Set, print and bind', 'A full-service print and binding firm with the capability to take on a wide variety of projects.'],
    ['award', 'Dedicated to quality', 'From individual graphic promotions and business cards to catalogs, packaging and mailing materials.'],
    ['upload', 'Easy uploads', 'Projects can be easily uploaded to our site, and we keep you posted through the print process.'],
    ['message-square', 'Honest, expert advice', 'We listen, and we’ll always be there with honest, expert advice and quick, friendly service.'],
];
partial('page-head', ['title' => $page['title'], 'intro' => '', 'eyebrow' => $page['eyebrow'] ?: 'About Sprintings', 'crumbs' => [[$page['title'], $page['slug'] . '/']], 'dark' => true]);
?>
<section class="section">
  <div class="container about-grid">
    <div class="reveal">
      <p class="eyebrow">Long Beach, California</p>
      <h2><?= e(setting('tagline')) ?></h2>
      <?php if ($page['intro']): ?><p class="lead"><?= e($page['intro']) ?></p><?php endif; ?>
      <div class="prose"><?= Html::clean((string)$page['content']) ?></div>
      <div class="btn-row" style="margin-top:24px">
        <a class="btn btn--accent btn--lg btn--magnetic" href="<?= e(url('request-a-quote/')) ?>">Get a Quote<?= icon('arrow-right', 'icon icon--arrow') ?></a>
        <a class="btn btn--ghost btn--lg" href="<?= e(url('products/')) ?>">Explore Our Products</a>
      </div>
    </div>
    <div class="about-art reveal reveal--scale">
      <?php if ($page['image']): ?><?= img($page['image'], 'Illustration of a print shop: printing, binding, packaging and marketing materials', ['sizes' => '(max-width: 1023px) 90vw, 520px']) ?><?php endif; ?>
    </div>
  </div>
</section>
<section class="section on-dark" style="background:var(--teal-950)">
  <div class="container">
    <div class="section-head reveal">
      <p class="eyebrow">What we do</p>
      <h2>Every project, from first idea to finished print.</h2>
    </div>
    <div class="pillars">
      <?php foreach ($pillars as $i => [$ic, $t, $d]): ?>
      <div class="pillar reveal" style="--d:<?= $i * .06 ?>s"><?= icon($ic) ?><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php partial('reviews'); ?>
    <div style="margin-top:clamp(48px,6vw,80px)"><?php partial('guarantee'); ?></div>
  </div>
</section>
<?php if ($faqs = Content::faqs('about')): ?>
<section class="section section--paper2"><div class="container container--narrow"><h2>Frequently asked questions</h2><?php partial('faq', ['faqs' => $faqs]); ?></div></section>
<?php endif; ?>
<section class="section section--white">
  <div class="container"><?php partial('contact-block', ['form' => 'contact', 'title' => 'Request a quote or just say hello.']); ?></div>
</section>
