@extends('layouts.default')

@section('title', 'Danh sách hình ảnh')
 
@section('content')
  <div class="row">
    <div class="col-md-3">
      <form action="{{ route('images.index') }}" method="GET">
        <input type="text" name="category" class="form-control" placeholder="Tìm kiếm ảnh" />
      </form> 
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary" type="submit">Tìm kiếm</button>
    </div>
  </div>
  <div class="row">
    @foreach($news_list as $news) 
      <div class="col-md-3"> 
        <div class="panel panel-primary">
            <div class="panel-heading"> 
                <h3 class="panel-title">{{ $news->link }}</h3>
            </div>
            <div class="panel-body">
                <img src="{{ $news->link }}" width="120px" height="80" class="center"/>
            </div>
        </div> 
      </div>  
    @endforeach 
  </div>
  <div class="row">
    {{ $news_list->appends(request()->input())->links("pagination::bootstrap-4") }}
  </div>
@endsection

@extends('layouts.footer')
