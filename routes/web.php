<?php

use Illuminate\Support\Facades\Route;
use App\Services\Exporters\CsvExporter;
use App\Services\Exporters\JsonExporter;
use App\Services\Exporters\XmlExporter;
use App\Http\Controllers\DataController;

Route::get('/', function () {
      abort(404);
});

Route::get('/data/export', [DataController::class, 'showExportForm']);
Route::post('/data/export', [DataController::class, 'export'])->name('data.export');

Route::get('/data/batch-export', [DataController::class, 'showbatchExportForm']);
Route::post('/data/batch-export', [DataController::class, 'batchExport'])->name('data.batch.export');

// Trait One

Route::get('/data/export-trait', [DataController::class, 'showExportWithtrait']);
Route::post('/data/export-trait', [DataController::class, 'exportWithTrait'])->name('data.exportWithTrait');

Route::get('/data/batch-export-trait', [DataController::class, 'showbatchExportWithTrait']);
Route::post('/data/batch-export-trait', [DataController::class, 'batchExport'])->name('data.batch.batchExportWithTrait');

// Import Routes

Route::get('/data/import', [DataController::class, 'showImportForm'])->name('data.import.form'); // optional
Route::post('/data/import', [DataController::class, 'import'])->name('data.import');


Route::get('/data/batch-import', [DataController::class, 'showbatchImportForm']);
Route::post('/data/batch-import', [DataController::class, 'batchImport'])->name('data.batch.import');

// Trait Import Routes

Route::get('/data/import-trait', [DataController::class, 'showImportWithTrait']);
Route::post('/data/import-trait', [DataController::class, 'importWithTrait'])->name('data.importWithTrait');

Route::get('/data/batch-import-trait', [DataController::class, 'showBatchImportWithTrait']);
Route::post('/data/batch-import-trait', [DataController::class, 'batchImportWithTrait'])->name('data.batchImportWithTrait');










