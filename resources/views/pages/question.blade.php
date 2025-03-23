@php
    use App\Models\Answer;
    $answers = $question->answers()->get()->sortByDesc(function (Answer $a1) {
        return $a1->correct * 100000 + $a1->post->upvotesCount() - $a1->post->downvotesCount();
    });
    $count = $answers->count();
@endphp

@extends('layouts.app')

@section('title', $question->title)

@section('skipLinks')
    <a href="#answer-thread" class="skip-link">Skip to answer thread</a>
@endsection

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js" defer></script>
    <script src="{{ url('js/richtextform.js') }}" defer></script>
    <script src="{{ url('js/multiselect.js') }}" defer></script>
    <script src="{{ url('js/contentActions.js') }}" defer></script>

    <article class="question" id="{{$question->post_id}}">
        @auth
            @can('update', $question)
                <button class="material-symbols-outlined button-icon" id="edit_question" title="Edit Question">edit</button>
                @can('delete', $question)
                    <div class="confirmation">
                        <button class="confirm-action delete-question">
                            <span class="material-symbols-outlined" title="Delete Question">delete</span>
                        </button>
                    </div>
                @else
                    <div class="confirmation">
                        <button class="confirm-action remove-authorship-question"><span
                                    class="material-symbols-outlined" title="Remove Authorship">person_remove</span>
                        </button>
                    </div>
                @endcan

                <form id="edit_question_form" class="hide">
                    <label for="title">Title <abbr class="requiredField" title="mandatory field">*</abbr>
                        <input type="text" id="title" name="title" maxlength="100" value="{{$question->title}}" required>
                    </label>
                    <label for="content">Content <abbr class="requiredField" title="mandatory field">*</abbr></label>
                    <textarea id="content" name="content">{{ strip_tags($question->post->content) }}</textarea>
                    <div id="editor">{!! $question->post->content !!}</div>
                    <fieldset>
                        <legend>Location</legend>
                        <label for="country">Country</label>
                        <select name="country" id="country">
                            <option value="">Select Country</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}"
                                        @if ($question->country_id === $country->id) selected @endif >
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="city" id="city" class="city" {{ $question->country_id ? 'enabled' : 'disabled' }}>
                            <option value="" id="empty-city">Select City</option>
                            @if ($question->country_id !== null)
                                @foreach ($question->country->cities as $city)
                                    <option value="{{ $city->id }}" @if ($question->city_id === $city->id) selected @endif >
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </fieldset>
                    <div class="tags-section">
                        <label for="tags">Tags</label>
                        <select id="tags" name="tags" data-placeholder="Select tags" multiple data-multi-select>
                            @foreach ($tags as $tag)
                                <option value="{{$tag->id}}" @if ($question->tags->contains('id', $tag->id)) selected @endif>{{$tag->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" id="save-question-edit">Save</button>
                </form>
            @endcan

        @endauth
        <div id="question_content">
            <h2 id="question-title">{{$question->title}}</h2>
            <div id="question-content">{!!$question->post->content!!}</div>
            @if($question->post->user && !$question->post->user->isDeleted())
                <a href="/users/{{ $question->post->user_id }}" class="author" rel="author">
                    <div class="image-container">
                        <img class="profile_photo {{$question->post->user->travelling ? "travelling" : ''}}" src="{{ $question->post->user->getProfileImage() }}"
                             alt="{{$question->post->user->username}}'s profile photo">
                        @if ($question->post->user->isVerified())
                            <span class="material-symbols-outlined">verified</span>
                        @endif
                    </div>
                    
                    {{$question->post->user->name}}
                </a>
            @else
                <p class="author">
                    <img class="profile_photo" src="{{ \App\Models\User::getDefaultImage() }}"
                         alt="unknown author profile photo">
                    Unknown
                </p>
            @endif
            <p class="date">{{\Carbon\Carbon::parse($question->post->date)->diffForHumans()}} </p>
            @if ($question->country)
                <div class="location">
                    <span class="material-symbols-outlined location-icon" title="Location">location_on</span>
                    <a href="{{route('country', ['id' => $question->country->id])}}" class="country"
                       id="question-country"
                       data-country-id="{{$question->country->id}}"> {{$question->country->name}} </a>
                    @if ($question->city)
                        <a href="{{route('city', ['id' => $question->city->id])}}" class="city" id="question-city"
                           data-city-id="{{$question->city->id}}"> {{$question->city->name}} </a>
                    @endif
                </div>
            @endif
            <ul class="tags">
                @foreach ($question->tags as $tag)
                    <li><a href="{{ url('search?query=' . urlencode('[' . $tag->name . ']')) }}"
                           data-tag-id="{{$tag->id}}">{{ $tag->name }}</a></li>
                @endforeach
            </ul>
            <div class="votesFollow">
                @include('partials.votes', ['post' => $question->post, 'vote' => json_decode(\App\Http\Controllers\QuestionController::hasUserVoted($question->post_id)->content())])
                <span id="followButton" data-id="{{ $question->post_id }}"><button
                            class="button-icon material-symbols-outlined follow"
                            title="Follow Question">add_circle</button>Follow Question</span>
            </div>
            <div class="views"><span class="material-symbols-outlined"
                                     title="View Count">visibility</span>{{$question->view_count}} views
            </div>
            @if ($question->post->edit)
                <p class="edited">[Edited]</p>
            @endif
        </div>
        @include('partials.commentSection', ['post' => $question->post])

        <section class="answers" id="answer-thread">
            <h4>{{$count}} answers</h4>
            @if ($count !== 0)
                <ul class="answers container" id="answers">
                    @each('partials.answer', $answers, 'answer')
                </ul>
            @endif
        </section>
        @auth
            <section>
                <form id="create_answer" action="{{route('answer.store', $question->post_id)}}" method="POST">
                    @csrf
                    @method('PUT')
                    <label for="answer_content">Write your answer here: <abbr class="requiredField"
                                                                              title="mandatory field">*</abbr></label>
                    <textarea id="answer_content" name="content"></textarea>
                    <div id="new-answer"></div>
                    <button type="submit">Create</button>
                </form>
            </section>
        @endauth
    </article>

@endsection
