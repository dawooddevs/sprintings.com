<?php
/**
 * Page heading with breadcrumbs (also emitted as BreadcrumbList schema by Seo::head()).
 * @var string $title  @var string $intro  @var string $eyebrow  @var array $crumbs [[label, path], …] after Home
 * @var bool $dark  deep-teal variant
 */
Seo::$breadcrumbs = array_merge([['Home', '']], $crumbs ?? []);
$last = count(Seo::$breadcrumbs) - 1;
?>
<section class="page-head<?= !empty($dark) ? ' page-head--dark on-dark' : '' ?>">
  <div class="container">
    <?php partial('crumbs'); ?>
    <?php if (!empty($eyebrow)): ?><p class="eyebrow"><?= e($eyebrow) ?></p><?php endif; ?>
    <h1><?= e($title) ?></h1>
    <?php if (!empty($intro)): ?><p class="lead"><?= e($intro) ?></p><?php endif; ?>
  </div>
  <div class="page-head__art" aria-hidden="true"><span class="reg"></span><span class="cmyk-bar"><span></span><span></span><span></span><span></span></span></div>
</section>
