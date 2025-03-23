@extends('layouts.app')

@section('title', 'New Question')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js" defer></script>
<script src="{{ url('js/richtextform.min.js') }}" defer></script>
<script src="{{ url('js/contentActions.min.js') }}" defer></script>
<script src="{{ url('js/multiselect.min.js') }}" defer></script>


<form id="create_question_form" class="container" method="POST" action="{{ route('question.store') }}">
<h1>New Question</h1>
    @csrf
    @method('PUT')

    <label for="title">Title <abbr class="requiredField" title="mandatory field">*</abbr>
    <input type="text" name="title" id="title" placeholder="Question title" value="{{ old('title') }}" maxlength="100" required>
    @include('partials.inputError', ['field' => 'title'])
    </label>



    <label for="content">Content <abbr class="requiredField" title="mandatory field">*</abbr></label>
    <textarea id="content" name="content"></textarea>
    <div id="editor"></div>
    @include('partials.inputError', ['field' => 'content'])

    <fieldset>
        <legend>Location</legend>

        <label for="country">Country</label>
        <select name="country" id="country">
            <option value="">Select Country</option>
            @foreach ($countries as $country)
                <option value="{{ $country->id }}">
                    {{ $country->name }}
                </option>
            @endforeach
        </select>

        <label for="city">City</label>
        <select name="city" id="city" class="city" disabled>
            <option value="" id="empty-city">Select City</option>
        </select>

    </fieldset>

    
    <div class="tags-section">
        <label for="tags">Tags</label>
        <select id="tags" name="tags[]" data-placeholder="Select tags" multiple data-multi-select>
            @foreach ($tags as $tag)   
                <option value="{{$tag->id}}">{{$tag->name}}</option>        
            @endforeach
        </select>
    </div>
    
    <button type="submit">
      Add Question
    </button>
</form>

@endsection