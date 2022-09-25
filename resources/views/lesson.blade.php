@extends('layouts.app')

@section('header')
    <link href="{{ asset('css/reader.css') }}" rel="stylesheet">
@endsection

@section('content')
<reader-component
    _course-name="{{ $course->name }}"
    _lesson-name="{{ $lesson->name }}"
    _lesson-id="{{ $lesson->id }}"
    :_lessons="{{ $lessons }}"
    _phrases="{{ json_encode($phrases) }}"
    :_word-count="{{ $lesson->word_count }}"
    _text="{{ $lesson->processed_text }}"
    _unique-words="{{ json_encode($uniqueWords) }}"
    _course-id="{{ $course->id }}"
    _language="{{ $lesson->language }}"
></reader-component>

@endsection
