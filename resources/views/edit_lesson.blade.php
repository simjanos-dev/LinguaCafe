@extends('layouts.app')

@section('content')
<form method="post" action="/save-lesson" id="edit-lesson-form" enctype="multipart/form-data">
    @if (isset($lesson))
        <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
    @endif
    @csrf
    <div class="form-group">
        <label class="form-check-label" for="name">Name</label>
        <input id="lesson-name" class="form-control" type="text" name="name" value="{{ isset($lesson) ? $lesson->name : ''}}" required>
    </div>
    <div class="form-group">
        <label class="form-check-label" for="course">Course</label>
        <select id="course-course" class="form-control" type="text" name="course">
            <option value="{{ $course->id }}">{{ $course->name }}</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-check-label" for="raw_text">Text</label>
        <textarea class="form-control" name="raw_text" rows="12">{{ isset($lesson) ? str_replace(" NEWLINE \r\n", "\r\n", $lesson->raw_text) : ''}}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">Save lesson</button>
    <a href="#" onclick="window.history.back();"><button class="btn btn-primary" type="button">Cancel</button></a>
</form>
@endsection
