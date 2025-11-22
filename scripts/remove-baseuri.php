<?php

/**
 * Script to remove baseUri parameter from all Request classes and their tests.
 *
 * The ElavonApiFactory handles base URI, so request classes should just use paths like '/orders'.
 */

$srcDir = __DIR__ . '/../src/Messages/Request';
$testDir = __DIR__ . '/../tests';

// Process source files
processDirectory($srcDir, 'source');

// Process test files
processDirectory($testDir, 'test');

function processDirectory(string $dir, string $type): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $filesModified = 0;

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        if ($type === 'source') {
            $content = processSourceFile($content);
        } else {
            $content = processTestFile($content);
        }

        if ($content !== $original) {
            file_put_contents($path, $content);
            $relativePath = basename(dirname($path)) . '/' . basename($path);
            echo "Modified ({$type}): {$relativePath}\n";
            $filesModified++;
        }
    }

    echo "Total {$type} files modified: {$filesModified}\n\n";
}

function processSourceFile(string $content): string
{
    // 1. Remove the baseUri constructor parameter line
    $content = preg_replace(
        '/\n\s+private readonly string \$baseUri = \'https:\/\/[^\']+\',/',
        '',
        $content
    );

    // 2. Remove the @param string $baseUri docblock line
    $content = preg_replace(
        '/\n\s+\*\s+@param string \$baseUri[^\n]*/',
        '',
        $content
    );

    // 3. Replace $this->baseUri . '/path' with just '/path'
    $content = preg_replace(
        '/\$this->baseUri\s*\.\s*\'/',
        '\'',
        $content
    );

    // 4. Also handle double-quoted strings
    $content = preg_replace(
        '/\$this->baseUri\s*\.\s*"/',
        '"',
        $content
    );

    return $content;
}

function processTestFile(string $content): string
{
    // 1. Remove entire test methods that test custom baseUri
    // Match: public function test_*_withCustomBaseUri_*(): void { ... }
    $content = preg_replace(
        '/\n\s+public function test_\w*[Ww]ith[Cc]ustom[Bb]ase[Uu]ri\w*\(\): void\s*\{[^}]+\}/',
        '',
        $content
    );

    // Also match test_build_usesCustomBaseUri pattern
    $content = preg_replace(
        '/\n\s+public function test_\w*[Uu]ses[Cc]ustom[Bb]ase[Uu]ri\w*\(\): void\s*\{[^}]+\}/',
        '',
        $content
    );

    // 2. Remove baseUri: parameter from constructor calls in tests
    // Handles: new SomeRequest(..., baseUri: 'https://...')
    $content = preg_replace(
        '/,\s*baseUri:\s*[\'"][^\'"]+[\'"]/',
        '',
        $content
    );

    // Also handle: new SomeRequest(..., baseUri: $variable)
    $content = preg_replace(
        '/,\s*baseUri:\s*\$\w+/',
        '',
        $content
    );

    return $content;
}

