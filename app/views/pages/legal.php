<?php
/** Legal / narrow content page (template "legal"). @var array $page */
Seo::set(['title' => $page['meta_title'] ?: $page['title'], 'description' => $page['meta_description'] ?: $page['intro'], 'canonical' => abs_url($page['slug'] . '/')]);
partial('page-head', ['title' => $page['title'], 'intro' => $page['intro'], 'eyebrow' => $page['eyebrow'], 'crumbs' => [[$page['title'], $page['slug'] . '/']]]);
?>
<section class="legal">
  <div class="container container--narrow">
    <div class="prose"><?= Html::clean((string)$page['content']) ?></div>
    <p style="margin-top:40px;color:var(--muted);font-size:.9rem">Last updated <?= e(date('F j, Y', strtotime((string)$page['updated_at']))) ?>. Questions? Email <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a>.</p>
  </div>
</section>
