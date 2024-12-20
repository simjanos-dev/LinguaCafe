@extends('layouts.user')
@section('content')
    <layout 
        :_user-count="{{ $userCount }}"
        :theme-settings="{}"
        _selected-language="spanish"
    ></layout>
@endsection