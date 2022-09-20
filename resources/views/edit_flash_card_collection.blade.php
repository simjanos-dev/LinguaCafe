@extends('layouts.app')

@section('content')
@if (isset($flashCardCollection))
    <edit-flash-card-collection-component
        _language="{{ $language }}"
        :_id="{{ $flashCardCollection->id }}"
        _name="{{ $flashCardCollection->name }}"
        _type="{{ $flashCardCollection->type }}"
        _flash-cards="{{ $flashCards }}"
    ></edit-flash-card-collection-component>
@else
    <edit-flash-card-collection-component></edit-flash-card-collection-component>
@endif
@endsection
