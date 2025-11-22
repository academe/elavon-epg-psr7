<?php

declare(strict_types=1);

/**
 * Demo Configuration
 *
 * Loads credentials from the project's .env file.
 */

// Load .env file from project root
$envPaths = [
    __DIR__ . '/../.env',  // Running from demo/
    __DIR__ . '/.env',      // If .env is in demo/ for some reason
];
$envFile = null;
foreach ($envPaths as $path) {
    if (file_exists($path)) {
        $envFile = $path;
        break;
    }
}
if (!$envFile) {
    die('Error: .env file not found. Copy .env.example to .env and add your credentials.');
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#')) {
        continue;
    }
    if (str_contains($line, '=')) {
        putenv($line);
    }
}

// Configuration
return [
    'merchant_alias' => getenv('ELAVON_MERCHANT_ALIAS') ?: die('ELAVON_MERCHANT_ALIAS not set'),
    'api_secret' => getenv('ELAVON_API_SECRET') ?: die('ELAVON_API_SECRET not set'),
    'base_uri' => getenv('ELAVON_BASE_URI') ?: 'https://uat.api.converge.eu.elavonaws.com',

    // Demo server URL (update if using different port)
    'demo_url' => 'http://localhost:8000',
];
