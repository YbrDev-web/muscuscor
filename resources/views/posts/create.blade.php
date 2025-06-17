@extends('layouts.app')

@section('content')
  <form action="{{ route('posts.store') }}" method="POST">
    @include('crud.crud')
  </form>
@endsection
