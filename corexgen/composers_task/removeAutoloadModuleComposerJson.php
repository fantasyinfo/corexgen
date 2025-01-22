<?php

$composerJsonPath = dirname(__DIR__) . '/composer.json';

// Check if the composer.json file exists
if (!file_exists($composerJsonPath)) {
    \Log::info('Composer json file not found', [$composerJsonPath]);
    exit('composer.json not found!');
}

// Read the composer.json file
$composerJson = json_decode(file_get_contents($composerJsonPath), true);

// Check if the autoload section exists, if not, create it
if (!isset($composerJson['autoload']['psr-4'])) {
    $composerJson['autoload']['psr-4'] = [];
}

$modulesDir = __DIR__ . '/modules';

// Scan the modules directory for subdirectories (modules)
$modules = array_filter(glob($modulesDir . '/*'), 'is_dir');

// Get the current autoload entries for modules
$currentAutoload = $composerJson['autoload']['psr-4'];

// Only remove entries that start with "Modules\\" and match deleted modules
foreach ($currentAutoload as $namespace => $path) {
    if (str_starts_with($namespace, 'Modules\\')) {
        $moduleName = str_replace('Modules\\', '', $namespace);
        $moduleDir = $modulesDir . '/' . $moduleName;

        // If the module directory doesn't exist, remove it from the autoload section
        if (!is_dir($moduleDir)) {
            unset($composerJson['autoload']['psr-4'][$namespace]);
            \Log::info("Removed autoload entry for deleted module: {$namespace}");
        }
    }
}

// Save the updated composer.json
file_put_contents($composerJsonPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

\Log::info('composer.json updated successfully.');
