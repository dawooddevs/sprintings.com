<?php
/** Thank You (/thank-you/, template "thanks", noindex). ?signup=1 after the newsletter form. @var array $page */
Seo::set(['title' => $page['meta_title'] ?: $page['title'], 'description' => $page['meta_description'] ?: $page['intro'], 'canonical' => abs_url($page['slug'] . '/')]);
Seo::$noindex = true;
$signup = isset($_GET['signup']);
$phone = (string)setting('phone');
$intro = (string)$page['intro'];
?>
<section class="center-hero">
  <div class="container container--narrow">
    <div class="big-check reveal reveal--scale"><?= icon('check') ?></div>
    <p class="eyebrow reveal" style="justify-content:center"><?= e($signup ? 'You’re signed up' : ($page['eyebrow'] ?: 'Request received')) ?></p>
    <h1 class="reveal"><?= e($page['title']) ?></h1>
    <?php if ($signup): ?>
    <p class="lead reveal">Thanks for signing up! Watch your inbox for savings.</p>
    <?php else: ?>
    <p class="lead reveal"><?php
        // "…on our contact form or by calling us here." → link both
        $html = e($intro);
        $html = str_replace('contact form', '<a href="' . e(url('contact/')) . '">contact form</a>', $html);
        if ($phone) $html = str_replace('calling us here', 'calling us at <a href="' . e(tel_href($phone)) . '">' . e($phone) . '</a>', $html);
        echo $html; ?></p>
    <?php endif; ?>
    <div class="btn-row reveal">
      <a class="btn btn--accent btn--lg" href="<?= e(url('products/')) ?>">Explore Our Products<?= icon('arrow-right', 'icon icon--arrow') ?></a>
      <a class="btn btn--ghost btn--lg" href="<?= e(url('')) ?>">Back to home</a>
    </div>
  </div>
</section>
<section class="section section--paper2"><div class="container"><?php partial('reviews'); ?></div></section>
