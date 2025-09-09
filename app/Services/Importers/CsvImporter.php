<?php
namespace App\Services\Importers;

use Exception;

class CsvImporter extends Importer
{
    public function import(string $path): array
    {
        if (!is_readable($path)) {
            throw new Exception("File is not readable: $path");
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new Exception("Unable to open file for reading");
        }

        $rows = [];
        $lineNumber = 0;
        $headers = null;

        while (($data = fgetcsv($handle)) !== false) {
            $lineNumber++;


            $isEmpty = true;
            foreach ($data as $cell) {
                if (trim((string)$cell) !== '') { $isEmpty = false; break; }
            }
            if ($isEmpty) continue;


            if ($headers === null && ($this->config['has_header'] ?? true)) {
                $headers = $this->normalizeHeaders($data);
                continue;
            }


            if ($headers === null) {
                $headers = [];
                foreach ($data as $i => $_) $headers[] = "col_{$i}";
            }

            $this->rowsProcessed++;
            $assoc = $this->rowToAssoc($headers, $data);


            if (!$this->validateRow($assoc, $lineNumber)) {
                continue;
            }

            $this->rowsImported++;
            $rows[] = $this->mapRow($assoc);
        }

        fclose($handle);
        return $rows;
    }

    protected function normalizeHeaders(array $cols): array
    {
        return array_map(fn($c) => strtolower(trim((string)$c)), $cols);
    }

    protected function rowToAssoc(array $headers, array $data): array
    {
        $assoc = [];
        foreach ($headers as $i => $h) {
            $assoc[$h] = $data[$i] ?? null;
        }
        return $assoc;
    }
}
