@extends('layouts.default')
@section('title', 'Sửa tin tức')
@section('content')
<body>
<div class="row">
    <div class="col-md-10 mt-3" style="width: 600px;">
        <form action="{{ route('articles.update', $news->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="md-3 mt-3">
                <label for="title">Title:</label>
                <input type="varchar" class="form-control" name="title" value="{{ $news->title }}"/>
            </div>
            <div class="md-3">
                <label for="author">Author:</label>
                <input type="varchar" class="form-control" name="author" value="{{ $news->author }}">
            </div>
            <div class="md-3">
                <label for="category">Category:</label>
                <input type="varchar" class="form-control" name="category" value="{{ $news->category }}">
            </div>
            <div class="md-3">
                <label for="description">Description:</label>
                <input type="varchar" class="form-control" name="description" value="{{ $news->description }}">
            </div>
            <div class="md-3">
                <label for="image">Thêm hình ảnh:</label>
                <input type="file" name="image" class="form-control">
                @if(isset($news->image))
                    <img src="{{ $news->image }}" width="100px" />
                @endif
            </div>
            <br>
            <div class="md-3">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <button type="reset" class="btn btn-primary">Nhập lại</button>
            </div>
        </form>
    </div>
</div>
</body>
@endsection