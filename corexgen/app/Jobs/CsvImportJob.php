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


    /**
     * csv file import handle job
     */
    public function handle()
    {
        if (ImportHistory::where('file_name', basename($this->filePath))->exists()) {
            return;
        }
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
            'import_id' => $this->importHistory->id,
        ]);

        $successfulCount = 0;
        try {
            if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
                throw new \Exception("Unable to read the uploaded file: {$this->filePath}");
            }

            $fileContents = file_get_contents($this->filePath);
            $rows = explode("\n", str_replace(["\r\n", "\r"], "\n", $fileContents));

            $data = array_map(fn($row) => str_getcsv($row, ',', '"', '\\'), array_filter($rows));
            $header = array_map('trim', array_shift($data));

            if (empty($data)) {
                throw new \Exception("CSV file is empty");
            }

            $this->importHistory->update(['total_rows' => count($data)]);

            if ($this->headerValidation) {
                $expectedHeaders = array_map('trim', $this->headerValidation);
                if (array_values($header) !== array_values($expectedHeaders)) {
                    throw new \Exception("The CSV header does not match the expected format.");
                }
            }

            $skippedRows = [];

            $processor = app($this->processorClass);

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2;

                $row = array_pad($row, count($header), null);
                $rowData = array_combine($header, $row);

                $rowData = array_map(fn($value) => trim($value) ?: null, $rowData);

                $validator = Validator::make($rowData, $this->rules);

                if ($validator->fails()) {
                    $errors = $validator->errors()->all();
                    info('Row skipped due to validation errors', [
                        'row_number' => $rowNumber,
                        'errors' => $errors,
                    ]);

                    $skippedRows[] = [
                        'row' => $rowNumber,
                        'errors' => $errors,
                    ];
                    continue;
                }

                try {
                    if ($processor->processRow($rowData, $this->userContext)) {
                        $successfulCount++;
                    } else {
                        $skippedRows[] = [
                            'row' => $rowNumber,
                            'errors' => ['Row processing failed'],
                        ];
                    }
                } catch (\Exception $e) {
                    info('Row skipped due to processing error', [
                        'row_number' => $rowNumber,
                        'error' => $e->getMessage(),
                    ]);

                    $skippedRows[] = [
                        'row' => $rowNumber,
                        'errors' => ['Processing error: ' . $e->getMessage()],
                    ];
                }
            }

            $this->importHistory->update([
                'status' => 'completed',
                'successful_rows' => $successfulCount,
                'failed_rows' => count($skippedRows),
                'failed_rows_details' => json_encode($skippedRows),
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->importHistory->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        } finally {
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
                info('Cleaned up CSV file', ['file' => $this->filePath]);
            }

            info('CSV Import Completed', [
                'import_id' => $this->importHistory->id,
                'successful' => $successfulCount,
                'skipped' => count($skippedRows),
            ]);
        }
    }
}
