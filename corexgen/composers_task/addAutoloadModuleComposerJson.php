<?php

$composerJsonPath = dirname(__DIR__) . '/composer.json';

// Check if the composer.json file exists
if (!file_exists($composerJsonPath)) {
    exit('composer.json not found!');
}

// Read the composer.json file
$composerJson = json_decode(file_get_contents($composerJsonPath), true);

// Ensure the `autoload` and `psr-4` sections exist
if (!isset($composerJson['autoload'])) {
    $composerJson['autoload'] = [];
}
if (!isset($composerJson['autoload']['psr-4'])) {
    $composerJson['autoload']['psr-4'] = [];
}

$modulesDir = __DIR__ . '/modules';

// Scan the modules directory for subdirectories (modules)
$modules = array_filter(glob($modulesDir . '/*'), 'is_dir');

foreach ($modules as $moduleDir) {
    $moduleName = basename($moduleDir);
    $moduleNamespace = 'Modules\\' . $moduleName . '\\';

    // Add module to autoload section if the 'src' directory exists
    $moduleSrcDir = $moduleDir . '/src';
    if (is_dir($moduleSrcDir)) {
        $relativePath = 'modules/' . $moduleName . '/src';

        // Check if the namespace is already set correctly to avoid duplicates
        if (!isset($composerJson['autoload']['psr-4'][$moduleNamespace]) || 
            $composerJson['autoload']['psr-4'][$moduleNamespace] !== $relativePath) {
            $composerJson['autoload']['psr-4'][$moduleNamespace] = $relativePath;
        }
    }
}

// Save the updated composer.json
file_put_contents($composerJsonPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Output success message for debugging
\Log::info('composer.json updated successfully.');
