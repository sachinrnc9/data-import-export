<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\DataExport;
use App\Jobs\DataImport;
use App\Models\User;
use App\Services\Importers\CsvImporter;
use App\Services\Importers\JsonImporter;
use Illuminate\Support\Facades\Storage;
use App\Services\Importers\BatchImportService;


class DataController extends Controller
{

    //Export Methods

    public function showExportForm()
    {
        return view('export');
    }

    public function showbatchExportForm()
    {
        return view('batchexport');
    }


    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,json,xml',
        ]);


        if (User::count() === 0) {
            return back()->withErrors(['format' => 'No data available for export.']);
        }

        try {
            DataExport::dispatch($request->format);

            return back()->with('success', "Export job dispatched successfully for format: {$request->format}. Check storage/app/exports folder.");
        } catch (\Exception $e) {
            return back()->withErrors(['format' => 'Export failed due to an internal error. Please try again.']);
        }
    }


    public function batchExport(Request $request)
    {
        $request->validate([
            'formats' => 'required|array',
            'formats.*' => 'in:csv,json,xml',
        ]);

        $service = new \App\Services\Exporters\BatchExportService();


        $dataCount = \App\Models\User::count();
        if ($dataCount === 0) {
            return back()->withErrors(['formats' => 'No data available for export.']);
        }


        $files = $service->export($request->formats);

        if (!empty($files)) {
            return back()->with('success', 'Batch export completed successfully: Check storage/app/exports folder.' . implode(', ', array_keys($files)));
        }
        return back()->withErrors(['formats' => 'Export failed due to an internal error. Please try again.']);
    }


    //Trait One for Export

    public function showExportWithtrait()
    {
        return view('export_trait');
    }

    public function showbatchExportWithTrait()
    {
        return view('batchexport_trait');
    }

    public function exportWithTrait(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,json,xml',
        ]);

        if (User::count() === 0) {
            return back()->withErrors(['format' => 'No data available for export.']);
        }

        $path = User::export($request->format);

        if ($path) {
            return back()->with('success', "Export done successfully. File saved at: {$path}");
        }

        return back()->withErrors(['format' => 'Export failed due to an internal error.']);
    }

    public function batchExportWithTrait(Request $request)
    {
        $request->validate([
            'formats'   => 'required|array',
            'formats.*' => 'in:csv,json,xml',
        ]);


        if (\App\Models\User::count() === 0) {
            return back()->withErrors(['formats' => 'No data available for export.']);
        }

        $files = [];
        foreach ($request->formats as $format) {
            // Call the trait method from User model
            $path = \App\Models\User::export($format);

            if ($path) {
                $files[$format] = $path;
            }
        }

        if (!empty($files)) {
            return back()->with(
                'success',
                'Batch export completed successfully. Files saved at: ' . implode(', ', $files)
            );
        }

        // Edge case: Data exists, but no file created
        return back()->withErrors(['formats' => 'Batch export failed. Please try again.']);
    }

    // Import Methods

    public function showImportForm()
    {
        return view('import.import'); // see view below
    }

    public function showbatchImportForm()
    {
        return view('import.batch_import'); // see view below
    }

    public function import(Request $request)
    {

        $request->validate([
            'file' => 'required|file|max:51200',
            'format' => 'nullable|in:csv,json,xml',
            'config' => 'nullable',
        ]);


        $format = $request->input('format');
        if (!$format) {
            $format = strtolower($request->file('file')->getClientOriginalExtension() ?: 'csv');
        }

        if (!in_array($format, ['csv', 'json'])) {
            return back()->withErrors(['format' => 'This endpoint currently supports CSV and JSON only.']);
        }


        $storedPath = $request->file('file')->store('imports'); // storage/app/imports/...
        $fullPath = storage_path('app/' . $storedPath);

        if (filesize($fullPath) === 0) {
            return back()->withErrors(['file' => 'The uploaded file is empty.']);
        }


        $clientConfig = $request->input('config', []);
        if (is_string($clientConfig)) {
            $clientConfig = json_decode($clientConfig, true) ?: [];
        }

        $config = array_merge([
            'has_header' => true,
            'mapping' => [],
            'required' => [],
            'unique_by' => ['email'],
            'chunk_size' => 500,
        ], $clientConfig);

        try {

            if ($format === 'csv') {
                $importer = new CsvImporter($config);
            } elseif($format === 'json') { // JSON
                $importer = new JsonImporter($config);
            }
            else{
                return back()->withErrors(['format' => 'This endpoint currently supports CSV and JSON only.']);
            }

            $rows = $importer->import($fullPath);


            $modelClass = User::class;
            $chunk = $config['chunk_size'] ?? 500;
            $totalInserted = 0;

            if (!empty($rows)) {
                foreach (array_chunk($rows, $chunk) as $batch) {

                    $batch = array_filter($batch, fn($r) => !empty($r) && is_array($r));


                    $batch = array_map(function ($row) {
                        $row['email_verified_at'] = null;
                        $row['created_at'] = now();
                        $row['updated_at'] = now();
                        return $row;
                    }, $batch);


                    if (!empty($config['unique_by'])) {
                        $uniqueBy = (array) $config['unique_by'];
                        $allKeys = array_keys($batch[0]);
                        $updateCols = array_values(array_diff($allKeys, $uniqueBy));

                        $modelClass::upsert($batch, $uniqueBy, $updateCols);
                    } else {
                        $modelClass::insert($batch);
                    }

                    $totalInserted += count($batch);
                }
            }


            $result = [
                'status' => 'ok',
                'file' => $storedPath,
                'rows_processed' => $importer->getRowsProcessed(),
                'rows_imported' => $importer->getRowsImported(),
                'db_rows_attempted' => $totalInserted,
                'errors' => $importer->getErrors(),
                'sample' => array_slice($rows, 0, 10),
            ];

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            return redirect()->route('data.import.form')->with('import_result', 'File Imported Successfully');

        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            $errmsg="Invalid Format of Data";

            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $errmsg], 500);
            }

            return back()->withErrors(['import' => $errmsg]);
        }
    }


    public function batchImport(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:51200',
            'chunk_size' => 'nullable|integer|min:1',
            'unique_by' => 'nullable|string',
        ]);

        $files = $request->file('files');

        if (empty($files)) {
            return back()->withErrors(['files' => 'Please upload at least one file.']);
        }

        $config = [
            'chunk_size' => $request->input('chunk_size', 500),
            'unique_by' => $request->input('unique_by')
                ? array_map('trim', explode(',', $request->input('unique_by')))
                : ['email'],
        ];

        foreach ($files as $file) {
            $fullPath = $file->store('imports');
            $fullPath = storage_path('app/' . $fullPath);

            $format = strtolower($file->getClientOriginalExtension());

            if (!in_array($format, ['csv', 'json'])) {
                continue;
            }

            DataImport::dispatch($fullPath, $format, \App\Models\User::class, $config);
        }

        return back()->with('success', 'All import jobs have been queued successfully!');
    }

    //Import Trait

    public function showImportWithTrait()
    {
        return view('import.import_trait');
    }


    public function importWithTrait(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,json,txt',
        ]);

        $file = $request->file('file');


        if (!$file || $file->getSize() === 0) {
            return back()->withErrors(['file' => 'The uploaded file is empty.']);
        }
        try {
            User::importFromFile($file);
            return back()->with('success', 'Import job has been queued');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    public function showBatchImportWithTrait()
    {
        return view('import.batch_import_with_trait');
    }

    public function batchImportWithTrait(Request $request)
    {
        $request->validate([
            'files'   => 'required|array',
            'files.*' => 'file|mimes:csv,json,txt|max:51200',
        ]);

        $files = $request->file('files');

        if (empty($files)) {
            return back()->withErrors(['files' => 'No files were uploaded.']);
        }

        try {
            foreach ($files as $file) {

                // Validate each file
                if (!$file->isValid() || $file->getSize() === 0) {
                    return back()->withErrors(['files' => 'One or more files are invalid or empty.']);
                }


                $relativePath = $file->store('imports');


                $format = strtolower($file->getClientOriginalExtension());

                $config = [
                    'default_values' => [
                        'email_verified_at' => null,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ],
                ];

                User::importFromFile($relativePath, $format, $config);
            }

            return back()->with('success', 'Batch import job(s) have been queued successfully.');

        } catch (\Throwable $e) {
            return back()->withErrors(['import' => 'Import failed: ' . $e->getMessage()]);
        }
    }


















}
