@extends('layouts.app')
@section('title', 'Tag Administration')
@section('content')

    <section class="admin">
        <h1>Administration</h1>
        <nav class="admin-navigation">
            <a href="{{route('admin.main')}}" class="admin-navigation">Users</a>
            <a href="{{route('admin.tags')}}" class="admin-navigation is-selected">Tags</a>
        </nav>
    </section>

    <div class="add-tag">
        <button class="button-icon material-symbols-outlined" id="add-tag">new_label</button>
        <form id="add-tag-form" action="{{url('/tags') }}" method="POST" style="display:none">
            @csrf
            <label for="tag-name">Name: <abbr class="requiredField" title="mandatory field">*</abbr></label>
            <input type="text" id="tag-name" placeholder="Tag name" name="name" required>
            <button type="submit" id="submit-tag">Create</button>
        </form>
    </div>

    <section id="admin-list">
        <p id="tags-count">Total tags: {{$tags->count()}}</p>
        <script src="{{ url('js/tag_search_admin.min.js') }}" defer></script>
        <div class="tag-search">
            <label> Search tags
                <input type="text" id="search-tags" placeholder="Tag Name">
            </label>
            <ul class="pagination-result">
            </ul>
            <div class="loader"></div>
        </div>
        <div class="control">
            <button class="button-icon material-symbols-outlined" id="previous" title="Previous Page"> chevron_left</button>
            <nav class="pagination"></nav>
            <button class="button-icon material-symbols-outlined" id="next" title="Next Page">chevron_right</button>
        </div>
    </section>
@endsection