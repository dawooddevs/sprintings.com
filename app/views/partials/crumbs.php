<?php /** Breadcrumb trail from Seo::$breadcrumbs. */
$last = count(Seo::$breadcrumbs) - 1; ?>
<nav aria-label="Breadcrumb">
  <ol class="crumbs">
    <?php foreach (Seo::$breadcrumbs as $i => [$label, $p]): ?>
    <li><?php if ($i === $last): ?><span aria-current="page"><?= e($label) ?></span><?php else: ?><a href="<?= e(url($p)) ?>"><?= e($label) ?></a><?php endif; ?></li>
    <?php endforeach; ?>
  </ol>
</nav>
