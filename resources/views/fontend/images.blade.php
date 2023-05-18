@extends('layouts.default')

@section('title', 'Danh sách hình ảnh')
 
@section('content')
  <form action="{{ route('images.index') }}" method="GET">
    <div class="row">
      <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm ảnh" />
      </div>
      <div class="col-md-3"> 
        <a href="/images"><i class="fa-solid fa-x" style="color:red;"></i></a>
        &nbsp&nbsp
        <button class="btn btn-primary" type="submit">Tìm kiếm</button>
      </div>
    </div>
  </form> 
  <form action="{{ route('images.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row">
      <div class="col-md-6">
        <input type="file" name="image" class="form-control">
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary">Upload</button> 
      </div>
    </div>
  </form>
  <div class="row">
    @foreach($news_list as $image) 
      <div class="col-md-3"> 
        <div class="card">
          <div class="card-header">
            <label>Link: </label>
            <input value="{{ $image->link }}" readonly/>
          </div>
          <div class="card-body">
            <img src="{{ $image->link }}" width="120px" height="80" class="center"/>
          </div>
          <div class="card-footer">
            <div class="row">
              <div class="col-md-3">
                <button class="btn btn-primary" data-clipboard-text="{{ $image->link }}">Copy</button>            
              </div>
              <div class="col-md-3">
                <form action="{{ route('images.destroy', $image->id) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
              </div>
            </div>
          </div>
        </div> 
      </div>
    @endforeach 
  </div>
  <br>
  <div class="row">
    {{ $news_list->appends(request()->input())->links("pagination::bootstrap-4") }}
  </div>
@endsection

@extends('layouts.footer')

