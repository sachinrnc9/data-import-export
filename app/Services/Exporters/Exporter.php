<?php
namespace App\Services\Exporters;

abstract class Exporter
{
    abstract public function export(array $data): string;

    protected function getFileName(string $extension): string
    {
        return 'export_' . time() . '.' . $extension;
    }
}

