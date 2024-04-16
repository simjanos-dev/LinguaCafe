@extends('layouts.user')
@section('content')<layout _user-name="{{ $userName }}" :_user-count="{{ $userCount }}" _selected-language="{{ $language }}"></layout>@endsection