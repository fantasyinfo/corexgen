<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $mysqldumpPath = $this->findMysqldumpPath();
        //\Log::info('mysql dump path', [$mysqldumpPath]);
        config(['database.connections.mysql.dump.dump_binary_path' => $mysqldumpPath]);
    }

    protected function findMysqldumpPath()
    {
        try {
            $commonPaths = [
                '/usr/bin',           // Changed to directory only
                '/usr/local/bin',
                '/opt/mysql/bin',
                '/usr/local/mysql/bin',
                '/usr/mysql/bin'
            ];
            
            foreach ($commonPaths as $path) {
                $fullPath = $path . '/mysqldump';  // Concatenate here
                if (file_exists($fullPath) && is_executable($fullPath)) {
                    return $path;  // Return just the directory
                }
            }

            // If no path is found, try getting it from the system
            if (function_exists('shell_exec')) {
                $path = trim(shell_exec('which mysqldump 2>/dev/null'));
                if (!empty($path)) {
                    // Extract the directory path without 'mysqldump'
                    return dirname($path);
                }
            }
            
            return '';
            
        } catch (\Exception $e) {
            \Log::warning('Could not detect mysqldump path: ' . $e->getMessage());
            return '';
        }
    }
}