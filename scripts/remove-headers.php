<?php

/**
 * Script to remove Content-Type and Accept headers from Request classes.
 *
 * ElavonApiFactory handles these headers, so request classes don't need to set them.
 */

$requestDir = __DIR__ . '/../src/Messages/Request';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($requestDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$filesModified = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);
    $original = $content;

    // Remove ->withHeader('Content-Type', 'application/json') lines
    $content = preg_replace(
        '/\n\s*->withHeader\([\'"]Content-Type[\'"],\s*[\'"]application\/json[\'"]\)/',
        '',
        $content
    );

    // Remove ->withHeader('Accept', 'application/json') lines
    $content = preg_replace(
        '/\n\s*->withHeader\([\'"]Accept[\'"],\s*[\'"]application\/json[\'"]\)/',
        '',
        $content
    );

    if ($content !== $original) {
        file_put_contents($path, $content);
        $relativePath = basename(dirname($path)) . '/' . basename($path);
        echo "Modified: {$relativePath}\n";
        $filesModified++;
    }
}

echo "\nTotal files modified: {$filesModified}\n";
