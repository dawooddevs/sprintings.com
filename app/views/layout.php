<?php
/**
 * Page shell. Keeps the backend hooks: SEO head, brand colours, favicon, analytics and the
 * admin-managed scripts and chat widget.
 * @var string $content rendered page view  @var string $path request path
 */
$hex = fn($v, $d) => preg_match('/^#[0-9a-f]{6}$/i', (string)$v) ? $v : $d;
$primary = $hex(setting('color_primary'), '#246b7f');
$accent = $hex(setting('color_accent'), '#f56f46');
$gaId = preg_replace('/[^A-Za-z0-9\-]/', '', (string)setting('ga_id'));
$css = is_file(ROOT . '/assets/css/site.min.css') ? 'css/site.min.css' : 'css/site.css';
$onQuote = rtrim($path, '/') === '/request-a-quote';
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <?= Seo::head() /* KEEP: title, meta description, canonical, Open Graph, robots, JSON-LD schema */ ?>
  <meta name="theme-color" content="#0e3039">
  <link rel="preload" href="<?= e(url('assets/fonts/montserrat-latin-wght.woff2')) ?>" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="<?= e(url('assets/fonts/inter-latin-wght.woff2')) ?>" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= e(asset($css)) ?>">
  <style>:root{--primary:<?= $primary ?>;--accent:<?= $accent ?>}</style>
  <script>document.documentElement.classList.add('js')</script>
  <?php if (setting('favicon')): ?><link rel="icon" href="<?= e(media_url(setting('favicon'))) ?>"><?php endif; ?>
  <?php if (Content::posts(1)): ?><link rel="alternate" type="application/rss+xml" title="<?= e(setting('site_name')) ?> Blog" href="<?= e(abs_url('blog/feed/')) ?>"><?php endif; ?>
  <?php if ($gaId): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
  <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= e($gaId) ?>');</script>
  <?php endif; ?>
  <?= setting('head_scripts') /* KEEP: trusted, admin-only setting */ ?>
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>
  <div class="progress" aria-hidden="true"><span></span></div>
  <?php partial('header', ['path' => $path]); ?>
  <main id="main" tabindex="-1"><?= $content ?></main>
  <?php partial('footer', ['path' => $path]); ?>
  <?php if (!$onQuote): ?>
  <a class="btn btn--accent btn--lg quote-pill" href="<?= e(url('request-a-quote/')) ?>" data-quote-pill><?= icon('sparkles') ?>Get a Quote</a>
  <?php endif; ?>
  <?php if (setting('cookie_notice') === '1'): ?>
  <div class="cookie" data-cookie hidden><p>We use essential cookies to run this site. See our <a href="<?= e(url('privacy-policy/')) ?>">privacy policy</a>.</p><button class="btn btn--accent btn--sm" type="button" data-cookie-ok>OK</button></div>
  <?php endif; ?>
  <script src="<?= e(asset(is_file(ROOT . '/assets/js/site.min.js') ? 'js/site.min.js' : 'js/site.js')) ?>" defer></script>
  <?= setting('chat_widget') /* KEEP: trusted, admin-only setting */ ?>
  <?= setting('body_scripts') /* KEEP: trusted, admin-only setting */ ?>
</body>
</html>
