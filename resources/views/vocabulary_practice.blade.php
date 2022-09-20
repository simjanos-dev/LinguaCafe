@extends('layouts.app')

@section('content')
<vocabulary-practice-component
    _reviews="{{ $reviews }}"
    _language="{{ $language }}"
></vocabulary-practice-component>

@endsection
