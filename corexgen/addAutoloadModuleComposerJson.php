<?php

$composerJsonPath = __DIR__ . '/composer.json';

// var_dump($composerJsonPath);

// Check if the composer.json file exists
if (!file_exists($composerJsonPath)) {
    // echo "composer.json not found!";
    exit;
}

// Read the composer.json file
$composerJson = json_decode(file_get_contents($composerJsonPath), true);

// Check if repositories and autoload sections exist, if not create them
// if (!isset($composerJson['repositories'])) {
//     $composerJson['repositories'] = [];
// }
if (!isset($composerJson['autoload']['psr-4'])) {
    $composerJson['autoload']['psr-4'] = [];
}

$modulesDir = __DIR__ . '/modules';

// Scan the modules directory for subdirectories (modules)
$modules = array_filter(glob($modulesDir . '/*'), 'is_dir');

foreach ($modules as $moduleDir) {
    $moduleName = basename($moduleDir);
    $moduleNamespace = 'Modules\\' . $moduleName;

    // Add module to repositories section
    // $composerJson['repositories'][] = [
    //     'type' => 'path',
    //     'url' => 'modules/' . $moduleName
    // ];

    // Add module to autoload section if the 'src' directory exists
    $moduleSrcDir = $moduleDir . '/src';
    if (is_dir($moduleSrcDir)) {
        $composerJson['autoload']['psr-4'][$moduleNamespace . '\\'] = 'modules/' . $moduleName . '/src';
    }
}

// Debug: Check the composer.json content before saving
// echo json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Save the updated composer.json
file_put_contents($composerJsonPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// echo "composer.json updated successfully.\n";
