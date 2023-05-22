@extends('layouts.default')

@section('title', 'Articles list')
 
@section('content')
  <div class="row">
    <div class="col-md-1">
      <a href="{{ route('articles.create') }}" class="btn btn-primary">Add</a>
    </div>
    <div class="col-md-9">
      <form action="{{ route('articles.index') }}" method="GET">
        <div class="row input-group">
          <div class="col-md-3">
            @if (isset($_REQUEST['title']))
              <input type="text" name="title" value="{{ $_REQUEST['title']; }}" class="form-control" placeholder="Enter title" />
            @else
              <input type="text" name="title" class="form-control" placeholder="Enter title" />
            @endif
          </div>
          <div class="col-md-3">
            @if (isset($_REQUEST['author']))
              <input type="text" name="author" value="{{ $_REQUEST['author']; }}" class="form-control" placeholder="Enter author" />
            @else
              <input type="text" name="author" class="form-control" placeholder="Enter author" />
            @endif
          </div>
          <div class="col-md-3">
            @if (isset($_REQUEST['category']))
              <input type="text" name="category" value="{{ $_REQUEST['category']; }}" class="form-control" placeholder="Enter category" />
            @else
              <input type="text" name="category" class="form-control" placeholder="Enter category" />
            @endif
          </div>
          <div class="col-md-3">
            <a href="/articles"><i class="fa-solid fa-x" style="color:red;"></i></a>
            &nbsp&nbsp
            <button class="btn btn-primary" type="submit">Search</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="row">
    @foreach($news_list as $news) 
      <div class="col-md-3"> 
        @include('layouts.article-card', [
          'title'       => $news->title, 
          'author'      => $news->author,
          'category'    => $news->category,
          'description' => $news->description, 
          'image'       => $news->image,
          'updated_at'  => $news->updated_at, 
        ])
      </div>  
    @endforeach 
  </div>
  <br>
  <div class="row">
    {{ $news_list->appends(request()->input())->links("pagination::bootstrap-4") }}
  </div>
@endsection

@extends('layouts.footer')
