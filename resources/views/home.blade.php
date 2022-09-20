@extends('layouts.app')

@section('content')
<table class="table table-bordered table-sm col-md-6 offset-md-3">
    <thead></thead>
    <tbody>
        <tr>
            <td>Days of reading:</td>
            <td>{{ $languageStatistics->days_of_learning }}</td>
        </tr>
        <tr>
            <td>Known words:</td>
            <td>{{ $languageStatistics->learned }}</td>
        </tr>
        <tr>
            <td>Words to review:</td>
            <td>{{ $languageStatistics->words_to_review }} / {{ $languageStatistics->words_to_review_total }}</td>
        </tr>
        <tr>
            <td>Read words:</td>
            <td>{{ $languageStatistics->readWordCount }} ({{ $languageStatistics->readWordCountToday }})</td>
        </tr>
        @if (Auth::user()->selected_language == 'japanese') 
            <tr>
                <td>Kanji:</td>
                <td>{{ $languageStatistics->kanjiCount }}</td>
            </tr>
        @endif
    </tbody>
</table>
@endsection
