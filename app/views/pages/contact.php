<?php
/** Contact (/contact/, template "contact"): contact details, hours, map and the "Contact Us Directly" form. @var array $page */
Seo::set(['title' => $page['meta_title'] ?: $page['title'], 'description' => $page['meta_description'] ?: $page['intro'], 'canonical' => abs_url($page['slug'] . '/')]);
Seo::addSchema(['@context' => 'https://schema.org', '@type' => 'ContactPage', 'name' => $page['title'], 'url' => abs_url($page['slug'] . '/'), 'about' => ['@id' => abs_url('') . '#organization']]);
$heading = trim(strip_tags((string)$page['content'])) ?: 'Request a quote or just say hello.';
partial('page-head', ['title' => $page['title'], 'intro' => $page['intro'], 'eyebrow' => $page['eyebrow'] ?: 'Contact', 'crumbs' => [[$page['title'], $page['slug'] . '/']], 'dark' => true]);
?>
<section class="section" id="long-beach">
  <div class="container"><?php partial('contact-block', ['form' => 'contact', 'title' => $heading, 'intro' => 'Our printing experts are ready to help you bring your ideas to life.']); ?></div>
</section>
<?php if ($faqs = Content::faqs('contact')): ?>
<section class="section section--paper2"><div class="container container--narrow"><h2>Frequently asked questions</h2><?php partial('faq', ['faqs' => $faqs]); ?></div></section>
<?php endif; ?>
<section class="section section--paper2">
  <div class="container"><?php partial('cta-band'); ?></div>
</section>
