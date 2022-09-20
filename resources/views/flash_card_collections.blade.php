@extends('layouts.app')

@section('content')
<div id="flash-card-collection">
    <a href="/create-flash-card-collection"><button id="create-flash-card-collection-button" class="btn btn-primary">New flash card collection</button></a>
    @foreach ($flashCardCollections as $collection)
    <div class="flash-card-collection">
        <div class="flash-card-collection-name">{{ $collection->name }}</div>
        <div class="flash-card-collection-buttons">
            <button class="btn btn-primary" onClick="if(confirm('Are you sure you want to delete this collection?')) window.location.href='/delete-flash-card-collection/{{ $collection->id }}'">Delete</button>
            <a href="/edit-flash-card-collection/{{ $collection->id }}"><button class="btn btn-primary">Edit</button></a>
            <a href="/flash-card-practice/{{ $collection->id }}"><button class="btn btn-primary">Practice</button></a>
        </div>
    </div>
    @endforeach
</div>
@endsection
