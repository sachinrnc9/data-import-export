<?php

namespace App\Services\Importers;

use Exception;

class JsonImporter extends Importer
{
    public function import(string $path): array
    {
        if (!is_readable($path)) {
            throw new Exception("File is not readable: $path");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new Exception("Unable to read file: $path");
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new Exception("Invalid JSON format in file: $path");
        }

        $rows = [];
        $lineNumber = 0;

        foreach ($data as $rawRow) {
            $lineNumber++;

            if (!is_array($rawRow) || empty(array_filter($rawRow, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            $this->rowsProcessed++;

            if (!$this->validateRow($rawRow, $lineNumber)) {
                continue;
            }

            $this->rowsImported++;
            $rows[] = $this->mapRow($rawRow);
        }

        return $rows;
    }
}
