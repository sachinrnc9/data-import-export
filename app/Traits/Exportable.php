<?php

namespace App\Traits;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Services\Exporters\CsvExporter;
use App\Services\Exporters\JsonExporter;
use App\Services\Exporters\XmlExporter;

trait Exportable
{

    public static function export(string $format, $query = null): ?string
    {
        $files = static::batchExport([$format], $query);
        return $files[$format] ?? null;
    }
    public static function batchExport(array $formats, $query = null): array
    {
        $modelClass = static::class;

        $data = null;

        if ($query instanceof Collection) {
            $data = $query->toArray();
        } elseif ($query instanceof Builder) {
            $data = $query->get()->toArray();
        } elseif ($query instanceof Closure) {
            $builder = $query($modelClass::query());
            if ($builder instanceof Builder) {
                $data = $builder->get()->toArray();
            } elseif ($builder instanceof Collection) {
                $data = $builder->toArray();
            } else {
                $data = $modelClass::all()->toArray();
            }
        } else {
            $data = $modelClass::all()->toArray();
        }


        if (empty($data)) {
            return [];
        }

        $exporterMap = [
            'csv'  => CsvExporter::class,
            'json' => JsonExporter::class,
            'xml'  => XmlExporter::class,
        ];

        $files = [];

        foreach ($formats as $format) {
            $format = strtolower($format);
            if (! isset($exporterMap[$format])) {
                continue;
            }

            try {
                $exporterClass = $exporterMap[$format];
                $exporter = new $exporterClass();
                $path = $exporter->export($data);

                if ($path) {
                    $files[$format] = $path;
                }
            } catch (\Throwable $e) {
                Log::error("Exportable: failed to export {$modelClass} as {$format}: " . $e->getMessage());
            }
        }

        return $files;
    }
}
