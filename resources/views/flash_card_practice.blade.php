@extends('layouts.app')

@section('content')
<flash-card-practice-component
    _type="{{ $type }}"
    _flash-cards="{{ $flashCards }}"
    _unique-words="{{ $uniqueWords }}"
></flash-card-practice-component>

@endsection
