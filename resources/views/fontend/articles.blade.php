@extends('layouts.default')

@section('title', 'Danh sách tin tức')
 
@section('content')
  <div class="row">
    <div class="col-md-3">
      <a href="{{ route('articles.create') }}" class="btn btn-primary">Thêm tin tức</a>
    </div>
    <div class="col-md-9">
      <form action="{{ route('articles.index') }}" method="GET">
        <div class="row input-group">
          <div class="col-md-3">
            @if (isset($_REQUEST['title']))
              <input type="text" name="title" value="{{ $_REQUEST['title']; }}" class="form-control" placeholder="Tiêu đề" />
            @else
              <input type="text" name="title" class="form-control" placeholder="Tiêu đề" />
            @endif
          </div>
          <div class="col-md-3">
            @if (isset($_REQUEST['author']))
              <input type="text" name="author" value="{{ $_REQUEST['author']; }}" class="form-control" placeholder="Tác giả" />
            @else
              <input type="text" name="author" class="form-control" placeholder="Tác giả" />
            @endif
          </div>
          <div class="col-md-3">
            @if (isset($_REQUEST['category']))
              <input type="text" name="category" value="{{ $_REQUEST['category']; }}" class="form-control" placeholder="Thể loại" />
            @else
              <input type="text" name="category" class="form-control" placeholder="Thể loại" />
            @endif
          </div>
          <div class="col-md-3">
            <a href="/articles"><i class="fa-solid fa-x" style="color:red;"></i></a>
            &nbsp&nbsp
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
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
  <div class="row">
    {{ $news_list->appends(request()->input())->links("pagination::bootstrap-4") }}
  </div>
@endsection

@extends('layouts.footer')
