<?php
/**
 * Blog listing (/blog/, template "blog"), 9 posts per page at /blog/page/N/, and category
 * archives at /category/<slug>/ when there is more than one category.
 * @var array $page  @var int $pageNo  @var string $category
 */
$per = 9;
$pageNo = max(1, (int)($pageNo ?? 1));
$catSlug = (string)($category ?? '');
$cats = Content::postCategories();
$catName = $catSlug !== '' ? ($cats[$catSlug] ?? '') : '';
$total = Content::postCount($catName);
$pages = max(1, (int)ceil($total / $per));
if ($pageNo > $pages) {
    http_response_code(404);
    Seo::$noindex = true;
}
$posts = Content::posts($per, ($pageNo - 1) * $per, $catName);
$base = $catSlug !== '' ? 'category/' . $catSlug . '/' : 'blog/';
$canonical = $pageNo > 1 ? $base . 'page/' . $pageNo . '/' : $base;
Seo::set([
    'title' => ($catName !== '' ? $catName . ' — ' : '') . ($page['meta_title'] ?: $page['title']) . ($pageNo > 1 ? ' — Page ' . $pageNo : ''),
    'description' => $page['meta_description'] ?: $page['intro'],
    'canonical' => abs_url($canonical),
]);
$crumbs = [[$page['title'], 'blog/']];
if ($catName !== '') $crumbs[] = [$catName, $base];
partial('page-head', ['title' => $catName !== '' ? $catName : $page['title'], 'intro' => $page['intro'], 'eyebrow' => $page['eyebrow'] ?: 'Blog', 'crumbs' => $crumbs, 'dark' => true]);
?>
<section class="section">
  <div class="container">
    <?php if (count($cats) > 1): ?>
    <nav class="filters__tabs" aria-label="Blog categories" style="margin-bottom:32px">
      <a class="ftab" href="<?= e(url('blog/')) ?>"<?= $catSlug === '' ? ' aria-pressed="true"' : '' ?>>All</a>
      <?php foreach ($cats as $slug => $name): ?><a class="ftab" href="<?= e(url('category/' . $slug . '/')) ?>"<?= $slug === $catSlug ? ' aria-pressed="true"' : '' ?>><?= e($name) ?></a><?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <?php if ($posts): ?>
    <div class="posts">
      <?php foreach ($posts as $i => $p): $feature = $i === 0 && $pageNo === 1; ?>
      <a class="post-card<?= $feature ? ' post-card--feature' : '' ?> reveal" style="--d:<?= ($i % 3) * .06 ?>s" href="<?= e(Content::postUrl($p)) ?>">
        <div class="post-card__img"><?php if ($p['image']): ?><?= img($p['image'], '', ['alt-empty' => 1, 'sizes' => $feature ? '(max-width: 1023px) 100vw, 700px' : '(max-width: 720px) 100vw, 400px', 'loading' => $i < 2 ? 'eager' : 'lazy']) ?><?php endif; ?></div>
        <div class="post-card__body">
          <p class="post-card__meta"><time datetime="<?= e(date('c', strtotime((string)$p['published_at']))) ?>"><?= e(date('F j, Y', strtotime((string)$p['published_at']))) ?></time><span><?= Content::readingTime((string)$p['content']) ?> min read</span></p>
          <<?= $feature ? 'h2' : 'h3' ?>><?= e($p['title']) ?></<?= $feature ? 'h2' : 'h3' ?>>
          <p><?= e(str_limit((string)($p['excerpt'] ?: $p['content']), 220)) ?></p>
          <span class="link-arrow">Read article<?= icon('arrow-right') ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="Blog pages">
      <?php if ($pageNo > 1): ?><a href="<?= e(url($pageNo === 2 ? $base : $base . 'page/' . ($pageNo - 1) . '/')) ?>" rel="prev"><?= icon('arrow-left') ?><span class="sr-only">Newer posts</span></a><?php endif; ?>
      <?php for ($n = 1; $n <= $pages; $n++): ?>
      <?php if ($n === $pageNo): ?><span aria-current="page"><?= $n ?></span><?php else: ?><a href="<?= e(url($n === 1 ? $base : $base . 'page/' . $n . '/')) ?>"><?= $n ?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($pageNo < $pages): ?><a href="<?= e(url($base . 'page/' . ($pageNo + 1) . '/')) ?>" rel="next"><?= icon('arrow-right') ?><span class="sr-only">Older posts</span></a><?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php else: ?>
    <p class="no-results">No posts yet. Check back soon.</p>
    <?php endif; ?>
    <div style="margin-top:clamp(56px,7vw,96px)"><?php partial('cta-band'); ?></div>
  </div>
</section>
