<div class="card">
  <div class="card-header">
    <h3>{{ $title }}</h3> 
  </div>
  <div class="card-body">
    <img src="{{ $image }}" width="120px" height="80" class="center"/>
    <div class="h4 text text-primary" style="text-align:center;">{{ $category }}</div>
    <div>{{ $description }}</div>
    <div class="text text-muted" style="text-align:right;">{{ $author}}</div>
  </div>
  <div class="card-footer">
    <div class="row">
      <div class="col-md-3">
        <a href="{{ route('articles.edit', $news->id) }}" class="btn btn-primary">Fix</a>
      </div>
      <div class="col-md-3">
        <form action="{{ route('articles.destroy', $news->id) }}" method="POST">
          @csrf 
          @method('DELETE')
          <button type="submit" class="btn btn-danger" title="Delete"><a>Del</a></button>
        </form>
      </div>
      <div class="col-md-6">
        <small class="text text-muted">{{ $updated_at }}</small>
      </div>
    </div>
  </div>
</div>