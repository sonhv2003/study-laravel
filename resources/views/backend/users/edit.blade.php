@extends('layouts.default')
@section('title', 'Sửa người dùng')
@section('content')
<body>
<div class="row">
    <div class="col-md-10 mt-3" style="width: 600px;">
        <form action="{{ route('users.update', $user->id) }}" method="post">
            @method('PUT')
            @csrf
            <div class="md-3 mt-3">
                <label for="name">Name:</label>
                <input type="varchar" class="form-control" value="{{ $user->name }}" name="name">
            </div>
            <div class="md-3">
                <label for="email">Email:</label>
                <input type="varchar" class="form-control" value="{{ $user->email }}" name="email">
            </div>        
            <br>
            <div class="md-3">
                <button type="submit" class="btn btn-primary">Hoàn thành</button> 
                <button type="reset" class="btn btn-primary">Nhập lại</button>
            </div>
        </form>
    </div>
</div>
</body>
@endsection