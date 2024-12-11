<?php

$composerJsonPath = __DIR__ . '/composer.json';

// Check if the composer.json file exists
if (!file_exists($composerJsonPath)) {
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

// Remove autoload entries for any deleted modules
foreach ($currentAutoload as $namespace => $path) {
    $moduleName = str_replace('Modules\\', '', $namespace);
    $moduleDir = $modulesDir . '/' . $moduleName;

    // If the module directory doesn't exist, remove it from the autoload section
    if (!is_dir($moduleDir)) {
        unset($composerJson['autoload']['psr-4'][$namespace]);
    }
}

// Save the updated composer.json
file_put_contents($composerJsonPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Optionally, output to confirm the removal
echo "composer.json updated successfully.\n";
