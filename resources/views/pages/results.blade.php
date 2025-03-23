@extends('layouts.app')

@section('title', 'Questions')

@section('content')
<script src="{{ asset('js/followTag.min.js') }}" defer></script>
<script src="{{ asset('js/autocomplete.min.js') }}" defer></script>

<div class="container">
    <div class="searchbar-results">
    <form action="{{ route('search') }}" method="GET">
        <label class="inline">
        <input type="text" name="query" id="search-input" placeholder="Search here..." autocomplete="off" value="{{ request('query') ? request('query') : ''}}">
        </label>
            <button type="button" class="button-icon material-symbols-outlined infohelper" title="Help" data-html="<div><strong>[tag]</strong> Search within a tag</div><div><strong>{country:Portugal}</strong> Search by country</div><div><strong>{city:Porto}</strong> Search by city</div>">help</button>
        <ul id="suggestions" class="suggestions suggestionResults"></ul>
        <button type="submit">Search</button>    
    </form>
    @if (count($questions) != 0)
    @include('partials.sortResults', ['hasQuery' => (!empty(request('query')) && (!empty($queryText))) ])
        @endif
    </div>
    <div class="search-filters">
        @if (!empty(trim($queryText))) 
            <p>Results for <strong>{{$queryText}}</strong></p>
        @endif
        @if (!empty($country)) 
            <p>Searching in country <strong>{{$country}}</strong></p>
        @endif
        @if (!empty($city)) 
            <p>Searching in city <strong>{{$city}}</strong></p>
        @endif
        @if (!empty($tags)) 
        <section class="tag-filters">
            <h5>Searching questions with tags:</h5>
            <ul class="tags">
                @foreach($tags as $tag)
                    <li><a href="{{ url('search?query=%5B' . $tag->name . '%5D')}}">{{ $tag->name }}</a></li>
                    <span class="followTagButton" id="followButton" data-id="{{ $tag->id }}"><span class="material-symbols-outlined follow" title="Follow Tag">add_circle</span>Follow {{$tag->name}}</span>
                @endforeach
            </ul>
        </section>
        @endif
    </div>
</div>

<ul id="results" class="container">
        <!-- Questions Section -->
        @if (count($questions) == 0)
            <h4>No questions found.</h4>
        @else
            @each('partials.questionList', $questions, 'question')
        @endif
</ul>
<div class="pages">
    {{$questions->links()}}    
</div>

@endsection
