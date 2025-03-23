@extends('layouts.app')

@section('title', 'Recover Password')


@section('content')


<div class="container-big">
<h1>Password Recovery</h1>
<div>
<form class="form_login" method="POST">
    @csrf

    <label for="email">E-mail <abbr class="requiredField" title="mandatory field">*</abbr>
    <input id="email" type="email" placeholder="Enter your email here..." name="email" value="{{ old('email') }}" required autofocus>
    @include('partials.inputError', ['field' => 'email'])
    </label>

    <button type="submit">
        Recover password
    </button>
</form>


@endsection