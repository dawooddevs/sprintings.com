<?php
$urls = [['', '1.0', null]];
foreach (DB::all("SELECT slug, updated_at FROM pages WHERE status = 'published' AND slug NOT IN ('sitemap','thank-you') ORDER BY sort_order") as $p) $urls[] = [$p['slug'] . '/', '0.8', $p['updated_at']];
foreach (Content::services() as $s) $urls[] = [Content::SERVICE_BASE . '/' . $s['slug'] . '/', '0.9', $s['updated_at']];
foreach (Content::posts() as $p) $urls[] = [Content::postPath($p), '0.6', $p['updated_at'] ?: $p['published_at']];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as [$u, $prio, $mod]): ?>
  <url><loc><?= e(abs_url($u)) ?></loc><?php if ($mod): ?><lastmod><?= e(date('Y-m-d', strtotime($mod))) ?></lastmod><?php endif; ?><priority><?= $prio ?></priority></url>
<?php endforeach; ?>
</urlset>
