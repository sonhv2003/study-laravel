@extends('layouts.default')

@section('title', 'Ví dụ thứ hai về Blade template')

@section('content')
    <p>
      <h3>Comment đã được chuyển đổi ký tự đặc biệt chống tấn công XSS</h3>
      {{ $comment }}
    </p>
    <p>
      <h3>Comment chưa được chuyển đổi ký tự đặc biệt</h3>
      {!! $comment !!}
    </p>
@endsection