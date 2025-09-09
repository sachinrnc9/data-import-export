<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Exporters\CsvExporter;
use App\Services\Exporters\JsonExporter;
use App\Services\Exporters\XmlExporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DataExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $format;

    /**
     * Create a new job instance.
     */
    public function __construct(string $format)
    {
        $this->format = $format;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $data = User::all()->toArray();

        // Select exporter
        switch ($this->format) {
            case 'csv':
                $exporter = new CsvExporter();
                break;
            case 'json':
                $exporter = new JsonExporter();
                break;
            case 'xml':
                $exporter = new XmlExporter();
                break;
            default:
                throw new \Exception("Invalid export format: {$this->format}");
        }


        $filePath = $exporter->export($data);

        // Optional: log or notify user
        \Log::info("Data exported successfully: " . $filePath);
    }
}
