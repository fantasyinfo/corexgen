<?php
namespace App\Jobs;

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

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, array $rules, string $processorClass, ?array $headerValidation = null, array $userContext = [])
    {
        $this->filePath = $filePath;
        $this->rules = $rules;
        $this->processorClass = $processorClass; // Class name of the row processor
        $this->headerValidation = $headerValidation;
        $this->userContext = $userContext;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {

        info('Auth Details', $this->userContext);



        if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
            throw new \Exception("Unable to read the uploaded file: {$this->filePath}");
        }

        $data = array_map('str_getcsv', file($this->filePath));
        $header = array_map('trim', array_shift($data)); // Extract header row

        // Validate the header if validation rules are provided
        if ($this->headerValidation && $header !== $this->headerValidation) {
            throw new \Exception("The CSV header does not match the expected format.");
        }

        $skippedRows = [];
        $successfulCount = 0;

        $processor = app($this->processorClass); // Instantiate the row processor class

        foreach ($data as $index => $row) {
            if (count($row) !== count($header)) {
                $skippedRows[] = [
                    'row' => $index + 2, // Account for header row
                    'errors' => ['Invalid number of columns.'],
                ];
                continue;
            }

            $row = array_combine($header, $row);
            $validator = Validator::make($row, $this->rules);

            if ($validator->fails()) {
                $skippedRows[] = [
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            try {
                // Process the row
                $processor->processRow($row,$this->userContext);
                $successfulCount++;
            } catch (\Exception $e) {
                $skippedRows[] = [
                    'row' => $index + 2,
                    'errors' => ['Processing error: ' . $e->getMessage()],
                ];
            }
        }

        // Log results or send notifications here if needed
        info("CSV Import Completed: {$successfulCount} rows imported, " . count($skippedRows) . " rows skipped.");
    }
}

