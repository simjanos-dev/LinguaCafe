@extends('layouts.user')
@section('content')<layout _user-name="{{ $userName }}" :_user-count="{{ $userCount }}" :_is-admin="{{ json_encode($isAdmin) }}" _selected-language="{{ $language }}"></layout>@endsection
