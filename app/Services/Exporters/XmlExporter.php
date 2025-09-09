<?php

namespace App\Services\Exporters;

class XmlExporter extends Exporter
{
    public function export(array $data): string
    {
        $xml = new \SimpleXMLElement('<root/>');

        foreach ($data as $row) {
            $item = $xml->addChild('item');
            foreach ($row as $key => $value) {
                $item->addChild($key, htmlspecialchars((string) $value));
            }
        }

        $filePath = storage_path('app/exports/data_' . time() . '.xml');
        $xml->asXML($filePath);

        return $filePath;
    }
}
