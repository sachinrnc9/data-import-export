<?php
namespace App\Services\Importers;
use Exception;
use App\Services\Importer\CsvImporter;
use App\Services\Importer\JsonImporter;

abstract class Importer
{
    protected array $errors = [];
    protected int $rowsProcessed = 0;
    protected int $rowsImported = 0;
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    abstract public function import(string $path): array;

    protected function addError(int $line, string $message): void
    {
        $this->errors[] = ['line' => $line, 'message' => $message];
    }


    protected function validateRow(array $row, int $line): bool
    {
        $required = $this->config['required'] ?? [];
        foreach ($required as $field) {
            if (!isset($row[$field]) || $row[$field] === '' || $row[$field] === null) {
                $this->addError($line, "Missing required field: {$field}");
                return false;
            }
        }
        return true;
    }

    protected function mapRow(array $row): array
    {
        $mapping = $this->config['mapping'] ?? [];
        if (empty($mapping)) {
            return $row;
        }
        $out = [];
        foreach ($mapping as $dest => $src) {
            $out[$dest] = $row[$src] ?? null;
        }
        return $out;
    }

    public function getErrors(): array { return $this->errors; }
    public function getRowsProcessed(): int { return $this->rowsProcessed; }
    public function getRowsImported(): int { return $this->rowsImported; }
}
