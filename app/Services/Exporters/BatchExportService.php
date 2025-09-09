<?php

namespace App\Services\Exporters;

use App\Models\User;

class BatchExportService
{
    protected $exporters = [
        'csv' => CsvExporter::class,
        'json' => JsonExporter::class,
        'xml' => XmlExporter::class,
    ];


    public function export(array $formats): array
    {
        $data = User::all()->toArray();
        if (empty($data)) {
            return [];
        }
        $files = [];

        foreach ($formats as $format) {
            if (!isset($this->exporters[$format])) {
                continue;
            }

            $exporterClass = $this->exporters[$format];
            $exporter = new $exporterClass();
            $files[$format] = $exporter->export($data);
        }

        return $files;
    }
}
