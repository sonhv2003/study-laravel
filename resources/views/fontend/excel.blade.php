@extends('layouts.default')

@section('title', 'Users with Excel')

@section('content')
<div class="container mt-3">
    <div class="mb-3 mt-3">
        <a href="{{ route('users.exportExcel') }}" class="btn btn-primary" title="Xuất dữ liệu">Export Excel</a>
    </div>
        <form action="{{ route('users.importExcel') }}" enctype="multipart/form-data" method="post">
            @csrf
            <div class="row">
                <div class="col-md-4">            
                    <input type="file" name="file" style="width: 360px;" class="form-control" />
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Import Excel</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('users.create') }}" class="btn btn-primary" title="Nhập thủ công">Manual Import</a>
                </div>
            </div>
        </form>
    <table class="table table-hover">
        <thead>
            <tr> 
                <th scope="col">ID</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users_list as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <div class="row">
                        <div class="col-md-2">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Fix</a>
                        </div>
                        <div class="col-md-2">
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"><a>Delete</a></button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>       
            @endforeach
        </tbody>
    </table>
    <div class="row">
        {{ $users_list->appends(request()->input())->links("pagination::bootstrap-4") }}
    </div>                                                
</div>
@endsection

@extends('layouts.footer')

