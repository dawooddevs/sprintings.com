<?php
/** Product card: stacked-paper image, category, name and summary. @var array $s product row  @var bool $showCat */
$img = $s['image'] ?? '';
?>
<a class="pcard" href="<?= e(Content::serviceUrl($s)) ?>" data-tilt data-card data-name="<?= e(mb_strtolower($s['title'] . ' ' . $s['menu_label'] . ' ' . $s['excerpt'] . ' ' . Content::categoryLabel($s['category']))) ?>" data-cat="<?= e($s['category']) ?>">
  <div class="pcard__media">
    <div class="pcard__sheet">
      <?php if ($img): ?><?= img($img, $s['title'], ['sizes' => '(max-width: 720px) 92vw, 300px']) ?><?php else: ?><div class="pcard__ph"><?= icon(Content::serviceIcon($s)) ?></div><?php endif; ?>
    </div>
    <?php if ((int)($s['coming_soon'] ?? 0)): ?><span class="pcard__badge">New</span><?php endif; ?>
  </div>
  <div class="pcard__body">
    <?php if (!empty($showCat)): ?><p class="pcard__cat"><?= e(Content::CATEGORIES[$s['category']]['short'] ?? '') ?></p><?php endif; ?>
    <h3><?= e($s['menu_label'] ?: $s['title']) ?></h3>
    <?php if (!empty($s['excerpt'])): ?><p><?= e($s['excerpt']) ?></p><?php endif; ?>
  </div>
</a>
