<?php

namespace App\Services\Importers;

use Exception;
use Illuminate\Support\Facades\DB;

use App\Services\Importers\CsvImporter;
use App\Services\Importers\JsonImporter;

class BatchImportService
{
    protected string $filePath;
    protected string $format;
    protected string $modelClass;
    protected array $config;

    protected int $rowsProcessed = 0;
    protected int $rowsImported = 0;

    public function __construct(string $filePath, string $format, string $modelClass, array $config = [])
    {
        $this->filePath = $filePath;
        $this->format = strtolower($format);
        $this->modelClass = $modelClass;
        $this->config = $config;
    }

    public function handle(): array
    {
        if (!file_exists($this->filePath) || filesize($this->filePath) === 0) {
            throw new Exception("File does not exist or is empty: {$this->filePath}");
        }


        $importer = match ($this->format) {
            'csv' => new CsvImporter($this->config),
            'json' => new JsonImporter($this->config),
            default => throw new Exception("Format {$this->format} not supported"),
        };


        $rows = $importer->import($this->filePath);

        if (empty($rows)) {
            return [
                'rows_processed' => 0,
                'rows_imported' => 0,
                'message' => 'No rows found in file'
            ];
        }

        $this->rowsProcessed = $importer->getRowsProcessed();


        $chunkSize = $this->config['chunk_size'] ?? 500;
        $totalInserted = 0;

        foreach (array_chunk($rows, $chunkSize) as $batch) {

            $batch = array_filter($batch, fn($r) => !empty($r) && is_array($r));


            $batch = array_map(function ($row) {
                $row['email_verified_at'] = null;
                $row['created_at'] = now();
                $row['updated_at'] = now();
                return $row;
            }, $batch);

            if (!empty($this->config['unique_by'])) {
                $uniqueBy = (array) $this->config['unique_by'];
                $allKeys = array_keys($batch[0]);
                $updateCols = array_values(array_diff($allKeys, $uniqueBy));

                $this->modelClass::upsert($batch, $uniqueBy, $updateCols);
            } else {
                $this->modelClass::insert($batch);
            }

            $totalInserted += count($batch);
        }

        $this->rowsImported = $totalInserted;

        return [
            'rows_processed' => $this->rowsProcessed,
            'rows_imported' => $this->rowsImported,
        ];
    }
}
