<?php
namespace App\Jobs;

use App\Models\ImportHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class CsvImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $rules;
    protected $processorClass;
    protected $headerValidation;
    protected $userContext;
    protected $importHistory;

    public function __construct(string $filePath, array $rules, string $processorClass, ?array $headerValidation = null, array $userContext = [])
    {
        $this->filePath = $filePath;
        $this->rules = $rules;
        $this->processorClass = $processorClass;
        $this->headerValidation = $headerValidation;
        $this->userContext = $userContext;
    }

    public function handle()
    {
        // Create import history record
        $this->importHistory = ImportHistory::create([
            'company_id' => $this->userContext['company_id'] ?? null,
            'user_id' => $this->userContext['user_id'] ?? null,
            'is_tenant' => $this->userContext['is_tenant'] ?? false,
            'file_name' => basename($this->filePath),
            'import_type' => $this->userContext['import_type'],
            'status' => 'processing',
            'started_at' => now(),
        ]);

        info('Starting CSV Import Job', [
            'file' => $this->filePath,
            'processor' => $this->processorClass,
            'context' => $this->userContext,
            'import_id' => $this->importHistory->id
        ]);

        try {
            if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
                throw new \Exception("Unable to read the uploaded file: {$this->filePath}");
            }

            // Read and process file
            $fileContents = file_get_contents($this->filePath);
            $fileContents = str_replace(["\r\n", "\r"], "\n", $fileContents);
            $rows = explode("\n", $fileContents);

            $data = array_map(function ($row) {
                return str_getcsv($row, ',', '"', '\\');
            }, array_filter($rows));

            if (empty($data)) {
                throw new \Exception("CSV file is empty");
            }

            $header = array_map('trim', array_shift($data));

            // Update total rows count
            $this->importHistory->update([
                'total_rows' => count($data)
            ]);

            if ($this->headerValidation) {
                $expectedHeaders = array_map('trim', $this->headerValidation);
                if (array_values($header) !== array_values($expectedHeaders)) {
                    throw new \Exception("The CSV header does not match the expected format.");
                }
            }

            $skippedRows = [];
            $successfulCount = 0;
            $processor = app($this->processorClass);

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2;

                if (empty(array_filter($row))) {
                    continue;
                }

                $row = array_pad($row, count($header), null);
                $rowData = array_combine($header, $row);

                $rowData = array_map(function ($value) {
                    $trimmed = trim($value ?? '');
                    return $trimmed === '' ? null : $trimmed;
                }, $rowData);

                $validator = Validator::make($rowData, $this->rules);

                if ($validator->fails()) {
                    $skippedRows[] = [
                        'row' => $rowNumber,
                        'errors' => $validator->errors()->all(),
                    ];
                    continue;
                }

                try {
                    $result = $processor->processRow($rowData, $this->userContext);
                    if ($result) {
                        $successfulCount++;
                    } else {
                        $skippedRows[] = [
                            'row' => $rowNumber,
                            'errors' => ['Row processing failed'],
                        ];
                    }
                } catch (\Exception $e) {
                    $skippedRows[] = [
                        'row' => $rowNumber,
                        'errors' => ['Processing error: ' . $e->getMessage()],
                    ];
                }

                // Update progress
                $this->importHistory->update([
                    'processed_rows' => $index + 1,
                    'successful_rows' => $successfulCount,
                    'failed_rows' => count($skippedRows),
                    'failed_rows_details' => $skippedRows
                ]);
            }

            // Mark as completed
            $this->importHistory->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

        } catch (\Exception $e) {
            // Update import history with error
            $this->importHistory->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);

            throw $e;
        } finally {
            // Clean up the file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
                info('Cleaned up CSV file', ['file' => $this->filePath]);
            }
        }

        info('CSV Import Completed', [
            'import_id' => $this->importHistory->id,
            'successful' => $successfulCount,
            'skipped' => count($skippedRows)
        ]);
    }

}