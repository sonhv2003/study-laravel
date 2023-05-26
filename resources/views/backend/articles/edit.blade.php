@extends('layouts.default')
@section('title', 'Update article')
@section('content')
<body>
<div class="row">
    <div class="col-md-10 mt-3">
        <form action="{{ route('articles.update', $news->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <table>
                <tbody>
                    <tr>
                        <td class="pre-label">Title</td>
                        <td>
                            <input type="varchar" class="form-control" name="title" value="{{ $news->title }}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="pre-label">Author</td>
                        <td>
                            <input type="varchar" class="form-control" name="author" value="{{ $news->author }}">
                        </td>
                    </tr>
                    <tr>
                        <td class="pre-label">Category</td>
                        <td>
                            <input type="varchar" class="form-control" name="category" value="{{ $news->category }}">
                        </td>
                    </tr>
                    <tr>
                        <td class="pre-label">Description</td>
                        <td>
                            <textarea name="description" id="content" value="">{{ $news->description }}</textarea>
                        </td>
                    </tr>           
                    <tr>
                        <td class="pre-label">Add image</td>
                        <td>
                            <input type="file" name="image" class="form-control">
                            @if(isset($news->image))
                                <img src="{{ $news->image }}" width="100px" />
                            @endif                        
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <button type="submit" class="btn btn-primary">Update</button>
            <button type="reset" class="btn btn-primary">Retext</button>
        </form>
    </div>
</div>
</body>
@endsection