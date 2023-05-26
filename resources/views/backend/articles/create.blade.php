@extends('layouts.default')
@section('title', 'Add article')
@section('content')
<body>
<div class="row">
    <div class="col-md-10 mt-3" >
        <form action="{{ route('articles.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <table>
                <tbody>
                    <tr>
                        <td class="pre-label">Title</td>
                        <td>
                            <input type="varchar" class="form-control" placeholder="Enter title" name="title">
                        </td>
                    </tr>
                    <tr>
                        <td class="pre-label">Author</td>
                        <td>
                            <input type="varchar" class="form-control" placeholder="Enter author" name="author">
                        </td>
                    </tr>
                    <tr>
                        <td class="pre-label">Category</td>
                        <td>
                            <input type="varchar" class="form-control" placeholder="Enter category" name="category">
                        </td>
                    </tr>
                    <tr>
                        <td class="pre-label">Description</td>
                        <td>
                            <textarea name="description" id="content"></textarea>
                        </td>
                    </tr>           
                    <tr>
                        <td class="pre-label">Add image</td>
                        <td>
                            <input type="file" name="image" class="form-control">
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <button type="submit" class="btn btn-primary">Add</button> 
            <button type="reset" class="btn btn-primary">Retext</button>
        </form>
    </div>
</div>
</body>
@endsection