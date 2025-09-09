<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Importers\BatchImportService;
use Exception;

class DataImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected string $filePath;
    protected string $format;
    protected string $modelClass;
    protected array $config;

    public function __construct(string $filePath, string $format, string $modelClass, array $config = [])
    {
        $this->filePath = $filePath;
        $this->format = strtolower($format);
        $this->modelClass = $modelClass;
        $this->config = $config;
    }

    /**
     * Execute the job.
     */

    public function handle(): void
    {
        try {
            if (!file_exists($this->filePath)) {
                throw new \Exception("File does not exist or is empty: {$this->filePath}");
            }

            $service = new BatchImportService(
                $this->filePath,
                $this->format,
                $this->modelClass,
                $this->config
            );

            $result = $service->handle();

            \Log::info("Data import completed for {$this->modelClass}", $result);

        } catch (Exception $e) {
            \Log::error("Data import failed: " . $e->getMessage(), [
                'file' => $this->filePath,
                'model' => $this->modelClass,
            ]);
            throw $e;
        }
    }


}
