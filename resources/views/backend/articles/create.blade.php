@extends('layouts.default')
@section('title', 'Add article')
@section('content')
<body>
<div class="row">
    <div class="col-md-10 mt-3" style="width: 600px;">
        <form action="{{ route('articles.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="md-3 mt-3">
                <label for="title">Title:</label>
                <input type="varchar" class="form-control" placeholder="Enter title" name="title">
            </div>
            <div class="md-3">
                <label for="author">Author:</label>
                <input type="varchar" class="form-control" placeholder="Enter author" name="author">
            </div>
            <div class="md-3">
                <label for="category">Category:</label>
                <input type="varchar" class="form-control" placeholder="Enter category" name="category">
            </div>
            <div class="md-3">
                <label for="description">Description:</label>
                <input type="varchar" class="form-control" placeholder="Enter description" name="description">
            </div>           
            <div class="md-3">
                <label for="image">Add image:</label>
                <input type="file" name="image" class="form-control">
            </div>
            <br>
            <div class="md-3">
                <button type="submit" class="btn btn-primary">Add</button> 
                <button type="reset" class="btn btn-primary">Retext</button>
            </div>
        </form>
    </div>
</div>
</body>
@endsection