<?php

/**
 * Script to remove Content-Type and Accept header assertions from Request tests.
 *
 * Since ElavonApiFactory handles these headers, tests shouldn't assert them.
 */

$testDir = __DIR__ . '/../tests/Unit/Messages/Request';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($testDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$filesModified = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);
    $original = $content;

    // Remove assertSame('application/json', ...->getHeaderLine('Content-Type')) lines
    $content = preg_replace(
        '/\n\s*\$this->assertSame\([\'"]application\/json[\'"],\s*\$\w+->getHeaderLine\([\'"]Content-Type[\'"]\)\);/',
        '',
        $content
    );

    // Remove assertSame('application/json', ...->getHeaderLine('Accept')) lines
    $content = preg_replace(
        '/\n\s*\$this->assertSame\([\'"]application\/json[\'"],\s*\$\w+->getHeaderLine\([\'"]Accept[\'"]\)\);/',
        '',
        $content
    );

    // Remove assertSame(['application/json'], ...->getHeader('Content-Type')) lines
    $content = preg_replace(
        '/\n\s*\$this->assertSame\(\[[\'"]application\/json[\'"]\],\s*\$\w+->getHeader\([\'"]Content-Type[\'"]\)\);/',
        '',
        $content
    );

    // Remove assertSame(['application/json'], ...->getHeader('Accept')) lines
    $content = preg_replace(
        '/\n\s*\$this->assertSame\(\[[\'"]application\/json[\'"]\],\s*\$\w+->getHeader\([\'"]Accept[\'"]\)\);/',
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
