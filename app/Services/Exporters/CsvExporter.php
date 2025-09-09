<?php

namespace App\Services\Exporters;

class CsvExporter extends Exporter
{
    public function export(array $data): string
    {

        $filename = $this->getFileName('csv');
        $filePath = storage_path('app/exports/' . $filename);


        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0777, true);
        }

        $handle = fopen($filePath, 'w');
        if (!empty($data)) {
            fputcsv($handle, array_keys($data[0]));
        }
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        return $filePath;
    }
}
