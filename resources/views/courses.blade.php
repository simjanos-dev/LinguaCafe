@extends('layouts.app')

@section('content')
<div id="courses">
    <a href="/create-course"><button id="create-course-button" class="btn btn-primary">Create new course</button></a>
    <br><br>

    @foreach ($courses as $course)
        @php
            $wordCounts = $course->getWordCounts($words);
        @endphp
        <div class="course">
            <div class="image-box">
                @if ($course->cover_image)
                    <img class="course-cover" src="/images/course_covers/{{ $course->cover_image }}">
                @endif
            </div>
            <div class="information-box">
                <div class="name">{{ $course->name }}</div>
                <div class="information">Words: <span>{{ $wordCounts->total }}</span></div>
                <div class="information">Unique words: <span>{{ $wordCounts->unique }}</span></div>
                <div class="information">Known words: <span>{{ $wordCounts->known }}</span></div>
                <div class="information">Highlighted words: <span class="highlighted"><i class="fa fa-book-open"></i> {{ $wordCounts->highlighted }}</span></div>
                <div class="information">New words: <span class="new"><i class="fa fa-eye-slash"></i> {{ $wordCounts->new }}</span></div>

                <div class="buttons">
                    <a href="/lessons/{{ $course->id }}">
                        <button class="btn btn-primary texts-button"><i class="fa fa-book-open"></i> Chapters</button>
                    </a>
                    <a href="{{ url('/vocabulary-practice/random/-1/' . $course->id) }}">
                        <button class="btn btn-primary texts-button"><i class="fa fa-keyboard"></i>  Practice</button>
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
