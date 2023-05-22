@extends('layouts.default')
@section('title', 'Thêm người dùng')
@section('content')
<body>
<div class="row">
    <div class="col-md-10 mt-3" style="width: 600px;">
        <form action="{{ route('users.store') }}" method="post">
            @csrf
            <div class="md-3 mt-3">
                <label for="name">Name:</label>
                <input type="varchar" class="form-control" placeholder="Nhập họ tên" name="name">
            </div>
            <div class="md-3">
                <label for="email">Email:</label>
                <input type="varchar" class="form-control" placeholder="Nhập email" name="email">
            </div>        
            <br>
            <div class="md-3">
                <button type="submit" class="btn btn-primary">Thêm</button> 
                <button type="reset" class="btn btn-primary">Nhập lại</button>
            </div>
        </form>
    </div>
</div>
</body>
@endsection