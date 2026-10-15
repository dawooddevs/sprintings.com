<?php
/**
 * Re-runs the Sprintings content import on an installed site (local resets and testing).
 * Rows are matched by slug and updated in place; media already in the library is reused.
 *
 *   php app/cli/import-sprintings.php            import everything (products, pages, posts, media, …)
 *   php app/cli/import-sprintings.php --no-media skip copying images into the Media Library
 *
 * On the live server, deliver content changes as migrations instead (app/migrations/), so edits
 * made in the dashboard are not overwritten.
 */
if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}
require dirname(__DIR__) . '/bootstrap.php';
require_once APP . '/lib/Forms.php';
require_once APP . '/seed/SprintingsImport.php';

if (!is_installed()) {
    fwrite(STDERR, "Install the site first (php app/cli/install.php …).\n");
    exit(1);
}
$o = getopt('', ['no-media']);
$t = microtime(true);
try {
    $lines = DB::tx(fn() => (new SprintingsImport(!isset($o['no-media'])))->run());
} catch (Throwable $e) {
    fwrite(STDERR, 'Import failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . "\n");
    exit(1);
}
foreach ($lines as $l) {
    echo "- $l\n";
}
printf("Done in %.1fs\n", microtime(true) - $t);
