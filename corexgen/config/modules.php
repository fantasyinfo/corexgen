<?php


return [
    'module_directory' => base_path('modules'),
    // 'namespace' => 'App\\Modules',
    'namespace' => 'Modules',
    'allowed_file_types' => ['zip'],
    'required_files' => ['module.json', 'ModuleServiceProvider.php']
];
