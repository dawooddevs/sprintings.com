<?php
/** Blog post (/YYYY/MM/DD/slug/): reading time, share links and related posts. @var array $post */
$p = $post;
$path = Content::postPath($p);
$url = abs_url($path);
$minutes = Content::readingTime((string)$p['content']);
Seo::set([
    'title' => $p['meta_title'] ?: $p['title'],
    'description' => $p['meta_description'] ?: str_limit((string)($p['excerpt'] ?: $p['content']), 155),
    'canonical' => $url,
    'image' => $p['image'],
    'type' => 'article',
]);
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $p['title'],
    'datePublished' => date('c', strtotime((string)$p['published_at'])),
    'dateModified' => date('c', strtotime((string)($p['updated_at'] ?: $p['published_at']))),
    'author' => ['@type' => 'Organization', 'name' => setting('site_name'), '@id' => abs_url('') . '#organization'],
    'publisher' => ['@id' => abs_url('') . '#organization'],
    'mainEntityOfPage' => $url,
    'description' => str_limit((string)($p['excerpt'] ?: $p['content']), 200),
];
if ($p['image']) $schema['image'] = abs_url($p['image']);
Seo::addSchema($schema);
Seo::$breadcrumbs = [['Home', ''], ['Blog', 'blog/'], [$p['title'], $path]];
$related = array_values(array_filter(Content::posts(4), fn($o) => $o['id'] !== $p['id']));
$related = array_slice($related, 0, 3);
$share = rawurlencode($url);
$t = rawurlencode($p['title']);
?>
<article>
  <header class="article-head">
    <div class="container container--narrow" style="max-width:calc(980px + var(--gutter)*2)">
      <?php partial('crumbs'); ?>
      <p class="eyebrow"><?= e($p['category'] ?: 'Blog') ?></p>
      <h1><?= e($p['title']) ?></h1>
      <p class="article-meta">
        <span><?= icon('calendar') ?><time datetime="<?= e(date('c', strtotime((string)$p['published_at']))) ?>"><?= e(date('F j, Y', strtotime((string)$p['published_at']))) ?></time></span>
        <span><?= icon('clock') ?><?= $minutes ?> min read</span>
        <span><?= icon('printer') ?>By <?= e(setting('site_name')) ?></span>
      </p>
    </div>
  </header>
  <div class="container" style="max-width:calc(1180px + var(--gutter)*2)">
    <?php if ($p['image']): ?><figure class="article-hero reveal" style="margin:0 0 clamp(32px,5vw,56px)"><?= img($p['image'], $p['title'], ['loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 1180px) 100vw, 1180px']) ?></figure><?php endif; ?>
    <div class="article">
      <div class="prose"><?= Html::clean((string)$p['content']) ?></div>
      <aside class="article-aside">
        <div class="share">
          <h2>Share this article</h2>
          <div class="share__links">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $share ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"><?= icon('facebook') ?></a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $share ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><?= icon('linkedin') ?></a>
            <a href="mailto:?subject=<?= $t ?>&amp;body=<?= $share ?>" aria-label="Share by email"><?= icon('mail') ?></a>
            <button type="button" data-copy-link aria-label="Copy link"><?= icon('link') ?></button>
          </div>
          <p class="share__msg" data-share-msg aria-live="polite"></p>
        </div>
        <div class="quote-card">
          <div class="quote-card__head"><h2 style="font-size:1.3rem">Ready to print?</h2><p><?= e(setting('cta_copy')) ?>.</p><span class="cmyk-bar" aria-hidden="true"><span></span><span></span><span></span><span></span></span></div>
          <div class="quote-card__body"><a class="btn btn--accent btn--block btn--magnetic" href="<?= e(url('request-a-quote/')) ?>">Get a Quote<?= icon('arrow-right', 'icon icon--arrow') ?></a></div>
        </div>
      </aside>
    </div>
  </div>
</article>
<?php if ($related): ?>
<section class="section section--paper2" style="margin-top:clamp(56px,7vw,96px)">
  <div class="container">
    <div class="section-head section-head--split reveal"><div><p class="eyebrow">Keep reading</p><h2>More from the blog</h2></div><a class="btn btn--ghost" href="<?= e(url('blog/')) ?>">All articles<?= icon('arrow-right', 'icon icon--arrow') ?></a></div>
    <div class="posts">
      <?php foreach ($related as $i => $r): ?>
      <a class="post-card reveal" style="--d:<?= $i * .06 ?>s" href="<?= e(Content::postUrl($r)) ?>">
        <div class="post-card__img"><?php if ($r['image']): ?><?= img($r['image'], '', ['alt-empty' => 1, 'sizes' => '(max-width: 720px) 100vw, 400px']) ?><?php endif; ?></div>
        <div class="post-card__body"><p class="post-card__meta"><time datetime="<?= e(date('c', strtotime((string)$r['published_at']))) ?>"><?= e(date('F j, Y', strtotime((string)$r['published_at']))) ?></time><span><?= Content::readingTime((string)$r['content']) ?> min read</span></p><h3><?= e($r['title']) ?></h3><span class="link-arrow">Read article<?= icon('arrow-right') ?></span></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
