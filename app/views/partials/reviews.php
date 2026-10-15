<?php
/** Google reviews carousel (Dashboard → Reviews; featured ones only). @var bool $dark */
$reviews = Content::reviews(true);
if (!$reviews) return;
$uid = 'rv' . substr(md5((string)mt_rand()), 0, 5);
$initials = function (string $n) { $p = preg_split('/\s+/', trim($n)); return mb_strtoupper(mb_substr($p[0] ?? '', 0, 1) . mb_substr(count($p) > 1 ? end($p) : '', 0, 1)); };
?>
<div class="reviews" data-carousel>
  <div class="reviews__head">
    <div>
      <p class="eyebrow">Google reviews</p>
      <h2><?= e(setting('reviews_title')) ?></h2>
      <?php if ($url = trim((string)setting('reviews_url'))): ?><a class="link-arrow" href="<?= e($url) ?>" target="_blank" rel="noopener">See all reviews on Google<?= icon('arrow-up-right') ?></a><?php endif; ?>
    </div>
    <div class="reviews__controls js-only">
      <button class="round-btn" type="button" data-carousel-prev aria-controls="<?= $uid ?>"><?= icon('arrow-left') ?><span class="sr-only">Previous reviews</span></button>
      <button class="round-btn" type="button" data-carousel-next aria-controls="<?= $uid ?>"><?= icon('arrow-right') ?><span class="sr-only">Next reviews</span></button>
    </div>
  </div>
  <div class="reviews__track" id="<?= $uid ?>" data-carousel-track tabindex="0" role="region" aria-label="Customer reviews">
    <?php foreach ($reviews as $r): $rating = max(1, min(5, (int)$r['rating'])); ?>
    <figure class="review">
      <div class="stars" role="img" aria-label="<?= $rating ?> out of 5 stars"><?php for ($i = 1; $i <= 5; $i++): ?><?= icon('star', 'icon' . ($i > $rating ? ' icon--off' : '')) ?><?php endfor; ?></div>
      <blockquote><p style="margin:0"><?= nl2br(e($r['content'])) ?></p></blockquote>
      <figcaption><span class="review__avatar" aria-hidden="true"><?= e($initials($r['name'])) ?></span><span><span class="review__name"><?= e($r['name']) ?></span><span class="review__src"><?= icon('google') ?><?= e($r['label'] ?: 'Google review') ?></span></span></figcaption>
    </figure>
    <?php endforeach; ?>
  </div>
</div>
