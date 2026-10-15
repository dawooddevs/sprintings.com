<?php
/**
 * Sprintings schema additions for installs made before they were part of app/schema.php:
 * product gallery, specifications and quote form; the blog (posts) table; quote-form artwork
 * uploads (submission_files) and the product name on submissions. Safe to run more than once.
 */
return function () {
    require_once APP . '/schema.php';
    $log = [];
    schema_install(); // creates any missing tables and indexes (CREATE TABLE IF NOT EXISTS)
    foreach ([
        ['services', 'gallery', '{TEXT} NULL'],
        ['services', 'specs', '{LONGTEXT} NULL'],
        ['services', 'quote_form', "VARCHAR(60) NOT NULL DEFAULT ''"],
        ['submissions', 'product', "VARCHAR(190) NOT NULL DEFAULT ''"],
    ] as [$table, $col, $type]) {
        if (schema_add_column($table, $col, $type)) {
            $log[] = "added $table.$col";
        }
    }
    return $log ?: ['schema already up to date'];
};
