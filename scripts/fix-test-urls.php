<?php

/**
 * Script to update test assertions to expect relative paths instead of full URLs.
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

    // Replace assertSame('https://api.eu.elavonpayments.com/path', ...) with assertSame('/path', ...)
    $content = preg_replace(
        '/assertSame\([\'"]https:\/\/api\.eu\.elavonpayments\.com(\/[^\'"]*)[\'"]/',
        'assertSame(\'$1\'',
        $content
    );

    // Replace assertStringStartsWith('https://api.eu.elavonpayments.com', ...) with assertStringStartsWith('/', ...)
    $content = preg_replace(
        '/assertStringStartsWith\([\'"]https:\/\/api\.eu\.elavonpayments\.com[\'"]/',
        'assertStringStartsWith(\'/\'',
        $content
    );

    // Replace assertStringStartsWith('https://api.eu.elavonpayments.com/path?', ...) with assertStringStartsWith('/path?', ...)
    $content = preg_replace(
        '/assertStringStartsWith\([\'"]https:\/\/api\.eu\.elavonpayments\.com(\/[^\'"]*)[\'"]/',
        'assertStringStartsWith(\'$1\'',
        $content
    );

    // Remove test methods named *_usesDefaultBaseUri*
    $content = preg_replace(
        '/\n\s+public function test_\w*[Uu]sesDefaultBaseUri\w*\(\): void\s*\{[^}]+\}/',
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
