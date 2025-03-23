@extends('layouts.app')

@section('title', 'Home')

@section('skipLinks')

@endsection
@section('content')


<script src="{{url('js/map.min.js')}}" defer></script>
<script src="{{ asset('js/autocomplete.min.js') }}" defer></script>
<div class="main-section">
    <img width="600" src="/images/banner-clean.png" alt="website banner">
    <div class="searchbar">
        <form action="{{ route('search') }}" method="GET">
            <label for="search-input"></label>
                <input type="text" name="query" id="search-input" placeholder="Search here..." autocomplete="off" value="{{ isset($query) ? $query : ''}}">
            <button type="button" class="button-icon material-symbols-outlined infohelper" title="Help" data-html="<div><strong>[tag]</strong> Search within a tag</div><div><strong>{country:Portugal}</strong> Search by country</div><div><strong>{city:Porto}</strong> Search by city</div>">help</button>
            <ul id="suggestions" class="suggestions" role="listbox">
            </ul>
            <button class="button-inverted" type="submit">Search</button>
        </form>
    </div>
</div>

<section class="hot-topics container">
    <h2>Hot topics</h2>
    <ul>
        @each('partials.questionList', $hot_topics,'question')
    </ul>
</section>

<section class="tags-home container">
    <h2>Tags</h2>
    <ul class="tags">
        @foreach ($tags as $tag)
            <li><a href="{{ url('search?query=%5B' . $tag->name . '%5D')}}">{{ $tag->name }}</a></li>
        @endforeach
    </ul>
</section>

<div id="map"></div>

@endsection

