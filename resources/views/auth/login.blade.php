@extends('layouts.app')

@section('title', 'Login')


@section('content')
<form class="form_login" method="POST" action="{{ route('login') }}">
    @csrf

    <label for="email">E-mail <abbr class="requiredField" title="mandatory field">*</abbr>
    <input id="email" type="email" placeholder="Enter your email here..." name="email" value="{{ old('email') }}" required autofocus>
    @include('partials.inputError', ['field' => 'email'])
    </label>


    <label for="password" >Password <abbr class="requiredField" title="mandatory field">*</abbr>
    <input id="password" placeholder="Enter your password here..." type="password" name="password" required>
    @include('partials.inputError', ['field' => 'password'])
    </label>


    <label>
        <input class="custom-checkbox" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
    Remember Me </label>
  

    <button type="submit">
        Login
    </button>
    <div>
        <a class="button button-outline" href="{{ route('register') }}">Register</a>
        <a href="{{ route('pass.recover') }}">Forgot your password?</a>
    </div>
    @include('partials.google')

</form>
@endsection