@extends('layouts.default')

@section('title', 'Nhập xuất excel')

@section('content')
<div class="container mt-3">
    <div class="mb-3 mt-3">
        <a href="{{ route('export-user') }}" class="btn btn-primary">Exports</a>
    </div>
    <form action="{{ route('import-user') }}" enctype="multipart/form-data" method="post">
        @csrf
        <div class="row">
            <div class="col-md-4">            
                <input type="file" name="file" style="width: 360px;" class="form-control" />
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </div>
    </form>
</div>
@endsection

@extends('layouts.footer')

