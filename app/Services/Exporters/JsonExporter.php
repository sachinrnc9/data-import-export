<?php

namespace App\Services\Exporters;

class JsonExporter extends Exporter
{
    public function export(array $data): string
    {
        $filename = $this->getFileName('json');
        $filePath = storage_path('app/exports/' . $filename);

        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0777, true);
        }

        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

        return $filePath;
    }
}
