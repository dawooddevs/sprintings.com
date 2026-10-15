<?php /** The satisfaction guarantee block. */ ?>
<div class="guarantee reveal">
  <?php partial('seal'); ?>
  <div>
    <h2><?= e(setting('guarantee_title')) ?>.</h2>
    <p><?= e(setting('guarantee_text')) ?> All prints come with a “100% money refund if not satisfied” guarantee.</p>
  </div>
  <a class="btn btn--ink btn--lg" style="--btn-bg:var(--ink);--btn-fg:#fff" href="<?= e(url('request-a-quote/')) ?>">Start your project<?= icon('arrow-right', 'icon icon--arrow') ?></a>
</div>
