<?php /** Orange "Get your printing job started" band. */ $phone = (string)setting('phone'); ?>
<div class="cta-band reveal">
  <div>
    <h2><?= e(setting('cta_headline')) ?></h2>
    <p><?= e(setting('cta_copy')) ?>.</p>
  </div>
  <div class="btn-row">
    <a class="btn btn--ink btn--lg btn--magnetic" style="--btn-bg:var(--ink);--btn-fg:#fff" href="<?= e(url('request-a-quote/')) ?>">Get In Touch Now<?= icon('arrow-right', 'icon icon--arrow') ?></a>
    <?php if ($phone): ?><a class="btn btn--ghost btn--lg" href="<?= e(tel_href($phone)) ?>"><?= icon('phone') ?><?= e($phone) ?></a><?php endif; ?>
  </div>
</div>
