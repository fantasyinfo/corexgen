<?php

namespace App\Http\Controllers;


use App\Jobs\CreateBackupJob;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class BackupController extends Controller
{
    //
    use TenantFilter;
    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'dashboard.backup.';

    /**
     * Generate full view file path
     * 
     * @param string $filename
     * @return string
     */
    private function getViewFilePath($filename)
    {
        return $this->viewDir . $filename;
    }

    /**
     * load all backups
     */
    public function index()
    {
        if (getModule() == 'saas' && panelAccess() == PANEL_TYPES['COMPANY_PANEL']) {
            abort(403);
        }
        $backupDestinations = BackupDestinationFactory::createFromArray(config('backup.backup'));

        $backups = collect();

        foreach ($backupDestinations as $destination) {
            $destinationBackups = collect($destination->backups())
                ->map(function ($backup) {
                    // Get the backup file path
                    $path = $backup->path();

                    return [
                        'path' => encrypt($path), // Encrypt the path for security
                        'filename' => basename($path), // Add filename
                        'date' => $backup->date(),
                        // Use PHP's file functions to get size
                        'size' => file_exists($path) ? $this->formatFileSize(filesize($path)) : 'N/A',
                    ];
                })
                // Sort backups by date, most recent first
                ->sortByDesc('date');

            $backups = $backups->merge($destinationBackups);
        }

        // Limit to most recent backups if needed
        $backups = $backups->take(5);

        return view(
            $this->getViewFilePath('index'),
            [
                'title' => 'System Backups',
                'backupFiles' => $backups,
                'module' => PANEL_MODULES[$this->getPanelModule()]['backup'],
            ]
        );
    }

    // Helper method to format file size
    protected function formatFileSize($bytes)
    {
        if ($bytes === false)
            return 'Unknown';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max(0, (int) $bytes);
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    // Secure download method
    public function downloadBackup(Request $request)
    {
        try {
            // Decrypt and validate the path
            $encryptedPath = $request->input('path');
            $relativePath = decrypt($encryptedPath);

            // Determine the backup disk from configuration
            $backupDisk = config('backup.backup.destination.disks')[0] ?? 'local';

            // Check if file exists on the specified disk
            if (!Storage::disk($backupDisk)->exists($relativePath)) {
                abort(404, 'Backup file not found: ' . $relativePath);
            }

            // Ensure user has proper permissions
            if (!hasPermission('DOWNLOAD_BACKUP.DOWNLOAD')) {
                abort(403, 'Unauthorized download');
            }

            // Log the backup download
            \Log::info('Backup downloaded', [
                'file' => $relativePath,
                'disk' => $backupDisk,
                'user' => auth()->id()
            ]);

            // Get the full path from the storage disk
            $fullPath = Storage::disk($backupDisk)->path($relativePath);

            // Stream the download to prevent memory issues with large files
            return response()->download($fullPath, basename($relativePath));
        } catch (\Exception $e) {
            // Log detailed error for admins
            \Log::error('Backup download failed: ' . $e->getMessage(), [
                'path' => $relativePath ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            // User-friendly error
            return back()->with('error', 'Download failed. Please try again or contact support.');
        }
    }


    /**
     * create backup
     */
    public function createBackup()
    {

        try {


            CreateBackupJob::dispatch();

            return redirect()->back()->with([
                'success' => 'Backup is running in the background, will be done shortly, & listed below.',
                'message' => 'Backup is running in the background, will be done shortly, & listed below.'
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Backup creation failed'
            ]);
        }
    }
}

