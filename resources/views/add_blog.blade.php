@extends('layout/main')
@section('content')
    <form action="" method="post">
        @method('POST')
        @csrf
        <input name="title" type="text">
        <textarea name="desription"></textarea>
    </form>
@endsection
