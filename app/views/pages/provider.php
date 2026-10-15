<?php
/** Team member profile (/team/<slug>/). Not used on the Sprintings site; kept so the backend route still renders. @var array $provider */
$p = $provider;
Seo::set(['title' => $p['meta_title'] ?: $p['name'], 'description' => $p['meta_description'] ?: str_limit(strip_tags((string)($p['short_bio'] ?: $p['bio'])), 160), 'image' => $p['photo'], 'type' => 'profile']);
Seo::addSchema(['@context' => 'https://schema.org', '@type' => 'Person', 'name' => $p['name'], 'jobTitle' => $p['title'], 'worksFor' => ['@id' => abs_url('') . '#organization']]);
partial('page-head', ['title' => $p['name'], 'intro' => $p['title'], 'crumbs' => [[$p['name'], Content::PROVIDER_BASE . '/' . $p['slug'] . '/']]]);
?>
<section class="section">
  <div class="container container--narrow prose">
    <?php if ($p['photo']): ?><?= img($p['photo'], $p['name']) ?><?php endif; ?>
    <?php if ($p['short_bio']): ?><p class="lead"><?= e($p['short_bio']) ?></p><?php endif; ?>
    <?= Html::clean((string)$p['bio']) ?>
  </div>
</section>
