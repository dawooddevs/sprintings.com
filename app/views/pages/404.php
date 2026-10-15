<?php
/** 404 page: a playful "misprint" with search and the most useful links. */
Seo::set(['title' => 'Page Not Found']);
Seo::$noindex = true;
$phone = (string)setting('phone');
?>
<section class="center-hero">
  <div class="container container--narrow">
    <div class="big-404" aria-hidden="true">4<span>0</span>4</div>
    <h1>Looks like a misprint.</h1>
    <p class="lead">We couldn’t find that page. It may have moved when we rebuilt the site. Try one of these instead:</p>
    <div class="btn-row">
      <a class="btn btn--accent btn--lg" href="<?= e(url('products/')) ?>">Browse products<?= icon('arrow-right', 'icon icon--arrow') ?></a>
      <a class="btn btn--ghost btn--lg" href="<?= e(url('request-a-quote/')) ?>">Request a quote</a>
      <a class="btn btn--ghost btn--lg" href="<?= e(url('')) ?>">Home</a>
    </div>
    <?php if ($phone): ?><p style="margin-top:28px">Or call us at <a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a>.</p><?php endif; ?>
  </div>
</section>
