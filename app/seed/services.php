<?php
/**
 * Services loaded on a fresh install (empty by default). Sprintings products come from SprintingsImport instead; add extra products here
 * BEFORE the site is installed on the server; after that, add them in the dashboard or with
 * a migration in app/migrations/ (the seed only runs once, at install).
 *
 * One entry per service. HTML is allowed in what_is, how_it_works and what_to_expect;
 * conditions and benefits take one item per line; faqs is a list of [question, answer].
 *
 *   [
 *       'slug' => 'business-cards', 'title' => 'Business Cards', 'menu_label' => '',
 *       'category' => 'cards',                   // a key of Content::CATEGORIES
 *       'icon' => 'spine',                       // a key of app/lib/icons.php
 *       'featured' => 1, 'status' => 'published', // or 'draft'
 *       'excerpt' => 'One or two sentences for cards and meta descriptions.',
 *       'hero_text' => 'Intro line under the page title.',
 *       'what_is' => '<p>…</p>', 'how_it_works' => '<p>…</p>', 'what_to_expect' => '<p>…</p>',
 *       'conditions' => "Traditional\nSquare",
 *       'benefits' => "Use a template or request a full-service design.",
 *       'faqs' => [['Question?', 'Answer.']],
 *       'related' => 'other-slug,another-slug',   // related services
 *       'body_areas' => '',                      // not used
 *       'meta_title' => '', 'meta_description' => '',
 *   ],
 */
return [];
