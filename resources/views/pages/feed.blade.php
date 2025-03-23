@extends('layouts.app')
@section('title', 'Feed')
@section('content')


<section class="feed container">
    <h2>Feed</h2>
    @if ($feed->isEmpty())
        <h4>Follow a question or tag to start building your feed!</h4>
    @endif
    <ul id="results" class="container">
        @each('partials.questionList', $feed,'question')
    </ul>
    
</section>



@endsection