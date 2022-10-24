@extends('layouts.app')

@section('content')
        <vocabulary-component :_words="{{ $words }}" :_books="{{ $books }}" :_page-count="20" :_current-page="15"></vocabulary-component>
@endsection
