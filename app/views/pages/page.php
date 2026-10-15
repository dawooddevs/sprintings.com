<?php
/** Standard content page (template "default"). @var array $page */
Seo::set(['title' => $page['meta_title'] ?: $page['title'], 'description' => $page['meta_description'] ?: $page['intro'], 'canonical' => abs_url($page['slug'] . '/'), 'image' => $page['image']]);
if ($page['slug'] === 'thank-you') Seo::$noindex = true;
partial('page-head', ['title' => $page['title'], 'intro' => $page['intro'], 'eyebrow' => $page['eyebrow'], 'crumbs' => [[$page['title'], $page['slug'] . '/']], 'dark' => true]);
?>
<section class="section">
  <div class="container container--narrow">
    <?php if ($page['image']): ?><figure style="margin:0 0 40px"><?= img($page['image'], $page['title'], ['sizes' => '820px']) ?></figure><?php endif; ?>
    <div class="prose"><?= Html::clean((string)$page['content']) ?></div>
  </div>
</section>
<?php if ((int)$page['show_cta']): ?><section class="section section--paper2"><div class="container"><?php partial('cta-band'); ?></div></section><?php endif; ?>
