<?php

namespace App\Traits;

use App\Jobs\DataImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;
trait Importable
{

    public static function importFromFile($file, ?string $format = null, array $config = []): void
    {

        if ($file instanceof UploadedFile) {
            $ext = strtolower($file->getClientOriginalExtension());
            $format = $format ?? $ext;

            $filename = Str::uuid() . '.' . $ext;
            $path = $file->storeAs('imports', $filename);
        } else {

            $path = $file;
            $format = $format ?? pathinfo($file, PATHINFO_EXTENSION);
        }

        DataImport::dispatch(
            storage_path('app/' . $path),
            $format,
            static::class,
            $config
        );
    }

    public static function batchImportFromFiles(array $files, string $format = null, array $config = [])
    {
        foreach ($files as $file) {

            if ($file instanceof \Illuminate\Http\UploadedFile) {

                $filePath = $file->getRealPath();
                $format = $format ?? strtolower($file->getClientOriginalExtension());
            } elseif (is_string($file) && file_exists($file)) {

                $filePath = $file;
                $format = $format ?? strtolower(pathinfo($file, PATHINFO_EXTENSION));
            } else {
                throw new \Exception("Invalid file instance or file does not exist: " . (is_string($file) ? $file : gettype($file)));
            }

            \App\Jobs\DataImport::dispatch($filePath, $format, self::class, $config);
        }
    }








}
