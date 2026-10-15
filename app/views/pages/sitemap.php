<?php
/** HTML sitemap (template "sitemap"). @var array $page */
Seo::set(['title' => $page['meta_title'] ?: $page['title'], 'description' => $page['meta_description'], 'canonical' => abs_url('sitemap/')]);
partial('page-head', ['title' => $page['title'], 'intro' => $page['intro'], 'crumbs' => [[$page['title'], 'sitemap/']]]);
$pages = DB::all("SELECT slug, title FROM pages WHERE status = 'published' AND slug NOT IN ('thank-you', 'sitemap') ORDER BY sort_order, title");
?>
<section class="section" style="padding-top:24px">
  <div class="container sitemap-cols prose" style="max-width:calc(var(--max) + var(--gutter)*2)">
    <section><h2>Pages</h2><ul><li><a href="<?= e(url('')) ?>">Home</a></li><?php foreach ($pages as $p): ?><li><a href="<?= e(url($p['slug'] . '/')) ?>"><?= e($p['title']) ?></a></li><?php endforeach; ?></ul></section>
    <?php foreach (Content::servicesByCategory() as $cat => $items): if (!$items) continue; ?>
    <section><h2><?= e(Content::categoryLabel($cat)) ?></h2><ul><?php foreach ($items as $s): ?><li><a href="<?= e(Content::serviceUrl($s)) ?>"><?= e($s['menu_label'] ?: $s['title']) ?></a></li><?php endforeach; ?></ul></section>
    <?php endforeach; ?>
    <?php if ($posts = Content::posts()): ?>
    <section><h2>Blog</h2><ul><?php foreach ($posts as $p): ?><li><a href="<?= e(Content::postUrl($p)) ?>"><?= e($p['title']) ?></a></li><?php endforeach; ?></ul></section>
    <?php endif; ?>
  </div>
</section>
