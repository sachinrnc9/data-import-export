<!DOCTYPE html>
<html>
<head>
    <title>Data Export</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #6a9fd3;
        }
        .export-card {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 25px;
            text-align: center;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="export-card">
    <h2>Export Users Data</h2>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('data.batch.export') }}" id="exportForm">
        @csrf
        <div class="mb-3">
            <label class="form-label">Select Export Formats</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="formats[]" value="csv" id="csv" {{ (is_array(old('formats')) && in_array('csv', old('formats'))) ? 'checked' : '' }}>
                <label class="form-check-label" for="csv">CSV</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="formats[]" value="json" id="json" {{ (is_array(old('formats')) && in_array('json', old('formats'))) ? 'checked' : '' }}>
                <label class="form-check-label" for="json">JSON</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="formats[]" value="xml" id="xml" {{ (is_array(old('formats')) && in_array('xml', old('formats'))) ? 'checked' : '' }}>
                <label class="form-check-label" for="xml">XML</label>
            </div>
        </div>

        <button type="submit" class="btn btn-success w-100">Start Batch Export</button>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
