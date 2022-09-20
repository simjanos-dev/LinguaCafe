@extends('layouts.app')

@section('content')

<form method="post" action="/create-course" id="create-course-form" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="name">Name</label>
        <input id="course-name" class="form-control" type="text" name="name" required>
    </div>
    <div class="form-group">
        <label for="cover_image">Cover image</label>
        <input type="file" class="form-control-file" accept="image/*" name="cover_image">
    </div>
    
    <button type="submit" class="btn btn-primary">Create course</button>
    <a href="#" onclick="window.history.back();"><button class="btn btn-primary">Cancel</button></a>
</form>
@endsection
