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
    <h2>Batch Import with Trait</h2>

    {{-- Success Message --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('data.batchImportWithTrait') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Select Files (CSV / JSON)</label>
            <input type="file" name="files[]" multiple class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
    </form>



</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
