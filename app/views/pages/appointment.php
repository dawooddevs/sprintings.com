<?php
/**
 * Request a Quote (/request-a-quote/, template "appointment"): the main quote form as a 3-step
 * wizard (contact details → project specs → artwork upload and comments). Without JavaScript
 * it is one long form.
 * @var array $page
 */
Seo::set(['title' => $page['meta_title'] ?: $page['title'], 'description' => $page['meta_description'] ?: $page['intro'], 'canonical' => abs_url($page['slug'] . '/')]);
$phone = (string)setting('phone');
$email = (string)setting('email');
$steps = [[1, 'Contact details', 'Who should we send the quote to?'], [2, 'Project specs', 'Quantity, size, paper, color, finish'], [3, 'Artwork & comments', 'Upload files or describe your idea']];
partial('page-head', ['title' => $page['title'], 'intro' => $page['intro'], 'eyebrow' => $page['eyebrow'] ?: 'Get a quote', 'crumbs' => [[$page['title'], $page['slug'] . '/']], 'dark' => true]);
?>
<section class="section" style="padding-top:clamp(32px,4vw,56px)">
  <div class="container wizard">
    <aside class="wizard__aside">
      <ol class="steps js-only" data-wizard-steps aria-label="Quote steps">
        <?php foreach ($steps as [$n, $t, $d]): ?><li><button type="button" data-goto="<?= $n ?>"<?= $n === 1 ? ' aria-current="step"' : ' disabled' ?>><span><?= e($t) ?><small><?= e($d) ?></small></span></button></li><?php endforeach; ?>
      </ol>
      <div class="prose" style="font-size:.97rem">
        <?= Html::clean((string)$page['content']) ?>
      </div>
      <ul class="contact-list" style="margin-top:8px">
        <?php if ($phone): ?><li><?= icon('phone') ?><div><small>Prefer to talk?</small><a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a></div></li><?php endif; ?>
        <?php if ($email): ?><li><?= icon('mail') ?><div><small>Email artwork</small><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></div></li><?php endif; ?>
      </ul>
      <p class="p-hero__guarantee"><?= icon('shield-check') ?>100% money refund if not satisfied</p>
    </aside>
    <div class="wizard__panel">
      <?php partial('form', ['type' => 'quote', 'wizard' => true, 'idp' => 'rq']); ?>
    </div>
  </div>
</section>
<section class="section section--paper2">
  <div class="container"><?php partial('reviews'); ?></div>
</section>
