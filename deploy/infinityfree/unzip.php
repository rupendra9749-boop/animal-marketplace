<?php

// One-time helper to unpack an uploaded .zip into a folder of the site. Delete it after the deploy.
// Usage: unzip.php?t=TOKEN&zip=vendor.zip&to=_app
if (! hash_equals('@@TOKEN@@', (string) ($_GET['t'] ?? ''))) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain');

$zip = basename((string) ($_GET['zip'] ?? ''));
$to = trim((string) ($_GET['to'] ?? ''), '/');
if (! preg_match('/^[A-Za-z0-9._-]+\.zip$/', $zip) || ! preg_match('#^[A-Za-z0-9_./-]*$#', $to) || str_contains($to, '..')) {
    exit("bad arguments\n");
}

$archive = new ZipArchive;
$source = __DIR__.'/'.$zip;

if (! is_file($source) || $archive->open($source) !== true) {
    exit("cannot open $zip\n");
}

$target = __DIR__.($to !== '' ? '/'.$to : '');
@mkdir($target, 0755, true);
$ok = $archive->extractTo($target);
$count = $archive->numFiles;
$archive->close();

echo ($ok ? 'extracted' : 'FAILED')." $count entries from $zip into /$to\n";

if ($ok && isset($_GET['keep']) === false) {
    @unlink($source);
    echo "removed $zip\n";
}
