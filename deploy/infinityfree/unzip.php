<?php

// One-time helper to unpack an uploaded .zip into a folder of the site. The deploy pipeline uploads it with a
// one-time token for each deploy and deletes it afterwards.
// Usage: unzip.php?t=TOKEN&zip=vendor.zip&to=_app[&prune=build/assets]
//   prune=build/assets  after unpacking, delete files in that folder that build/manifest.json no longer refers to
//                       (old hashed style and script files).
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

if ($ok && isset($_GET['prune'])) {
    $dir = trim((string) $_GET['prune'], '/');
    $manifest = __DIR__.'/build/manifest.json';
    if ($dir === 'build/assets' && is_file($manifest)) {
        $keep = [];
        foreach (json_decode(file_get_contents($manifest), true) ?: [] as $entry) {
            foreach (array_merge([$entry['file'] ?? null], $entry['css'] ?? [], $entry['assets'] ?? []) as $file) {
                if ($file) {
                    $keep[basename($file)] = true;
                }
            }
        }
        $removed = 0;
        if ($keep) {
            foreach (glob(__DIR__.'/build/assets/*') ?: [] as $file) {
                if (is_file($file) && ! isset($keep[basename($file)])) {
                    @unlink($file);
                    $removed++;
                }
            }
        }
        echo "pruned $removed old file(s) from /$dir\n";
    }
}

if ($ok && isset($_GET['keep']) === false) {
    @unlink($source);
    echo "removed $zip\n";
}

exit($ok ? 0 : 1);
