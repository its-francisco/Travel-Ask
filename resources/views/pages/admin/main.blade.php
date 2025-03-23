@extends('layouts.app')
@section('title', 'User Administration')
@section('content')

<section class="admin">
    <h1>Administration</h1>
    <nav class="admin-navigation">
        <a href="{{route('admin.main')}}" class="admin-navigation is-selected">Users</a>
        <a href="{{route('admin.tags')}}" class="admin-navigation ">Tags</a>
    </nav>
</section>


@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
        @if (session('password'))
            <p>Your generated password is: {{ session('password') }}</p>
        @endif
    </div>
@endif
<div class="add-user">
    <button class="button-icon material-symbols-outlined" id="add-user" title="Add a user">person_add</button>
    <form id="add-user-form" action="{{url('/users') }}" method="POST" style="display:none">
        @csrf
            <label for="name">Name: <abbr class="requiredField" title="mandatory field">*</abbr>
            <input type="text" id="name" placeholder="User's name" name="name" required>
            @include('partials.inputError', ['field' => 'name'])
            </label>

            <label for="username">Username: <abbr class="requiredField" title="mandatory field">*</abbr>
            <input type="text" id="username" placeholder="User's username" name="username" required>
                @include('partials.inputError', ['field' => 'username'])
            </label>

            <label for="email">Email: <abbr class="requiredField" title="mandatory field">*</abbr>
            <input type="email" id="email" placeholder="User's email" name="email" required>
                @include('partials.inputError', ['field' => 'email'])
            </label>

            <label for="account">Account Type: <abbr class="requiredField" title="mandatory field">*</abbr>
            <select id="account" name="account" required>
                <option value="">Select Account Type</option>
                <option value="admin">Admin</option>
                <option value="verified">Verified</option>
                <option value="normal">Normal</option>
                <option value="moderator">Moderator</option>
            </select>
                @include('partials.inputError', ['field' => 'account'])
            </label>
        <div>
            <button type="submit">Add user</button>
        </div>
    </form>
</div>

<section id="admin-list">
    <p id="users-count">Total users: {{$users}}</p>
    <script src="{{ url('js/user_search.min.js') }}" defer></script>
    <div class="user-search">
        <label> Search user
            <input type="text" id="search-user" name="search" placeholder="Username or Email">
        </label>
        @include('partials.inputError', ['field' => 'search'])

        <header class="user-table-header">
            <p>Username</p>
            <p>Email</p>
            <p>Questions</p>
            <p>Answers</p>
        </header>
        <div class="user-result">
            <div class="loader"></div>
        </div>
    </div>
    <div class="control">
        <button class="button-icon material-symbols-outlined" id="previous" title="Previous Page">chevron_left</button>
        <nav class="pagination"></nav>
        <button class="button-icon material-symbols-outlined" id="next" title="Next Page"> chevron_right</button>
    </div>

</section>


@endsection