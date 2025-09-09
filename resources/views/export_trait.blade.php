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
    <h2>Export Users Data Using Traits</h2>

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

    <form method="POST" action="{{ route('data.export') }}">
        @csrf
        <div class="mb-3">
            <label for="format" class="form-label">Select Export Format</label>
            <select name="format" id="format" class="form-select @error('format') is-invalid @enderror" required>
                <option value="">-- Choose --</option>
                <option value="csv" {{ old('format')=='csv'?'selected':'' }}>CSV</option>
                <option value="json" {{ old('format')=='json'?'selected':'' }}>JSON</option>
                <option value="xml" {{ old('format')=='xml'?'selected':'' }}>XML</option>
            </select>
            @error('format')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Start Export</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
