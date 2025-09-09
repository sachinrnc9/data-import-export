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
    <h2>Import-Upload CSV/JSON</h2>

    {{-- Success Message --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
        </div>
    @endif

    @if (session('import_result'))
        <div class="alert alert-success">
            <pre>{{ json_encode(session('import_result'), JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif

    <form action="{{ route('data.import') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="file">Upload file</label>
            <input type="file" name="file" required class="form-control">
        </div>

        <button class="btn btn-primary">Upload & Import</button>
    </form>



</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
