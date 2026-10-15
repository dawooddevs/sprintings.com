<?php
/** RSS feed of the latest blog posts (/blog/feed/). */
$posts = Content::posts(20);
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
  <title><?= e(setting('site_name')) ?> Blog</title>
  <link><?= e(abs_url('blog/')) ?></link>
  <atom:link href="<?= e(abs_url('blog/feed/')) ?>" rel="self" type="application/rss+xml"/>
  <description><?= e(setting('tagline')) ?></description>
  <language>en-us</language>
<?php foreach ($posts as $p): ?>
  <item>
    <title><?= e($p['title']) ?></title>
    <link><?= e(abs_url(Content::postPath($p))) ?></link>
    <guid isPermaLink="true"><?= e(abs_url(Content::postPath($p))) ?></guid>
    <pubDate><?= e(date(DATE_RSS, strtotime((string)$p['published_at']))) ?></pubDate>
    <description><?= e(str_limit((string)($p['excerpt'] ?: $p['content']), 400)) ?></description>
  </item>
<?php endforeach; ?>
</channel>
</rss>
