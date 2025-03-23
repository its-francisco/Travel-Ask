@extends('layouts.app')

@section('title', 'Register')


@section('content')
<form class="form_login" id="register-form" method="POST" action="{{ route('register') }}">
    @csrf

    <label for="name">Name <abbr class="requiredField" title="mandatory field">*</abbr>
    <input id="name" type="text" placeholder="Enter you name here..." name="name" value="{{ old('name') }}" required autofocus>
    @include('partials.inputError', ['field' => 'name'])
    </label>

    <label for="username">Username <abbr class="requiredField" title="mandatory field">*</abbr>
    <input id="username" type="text" placeholder="Enter you username here..." name="username" value="{{ old('username') }}" required>
    @include('partials.inputError', ['field' => 'username'])
    </label>


    <label for="email">E-Mail Address <abbr class="requiredField" title="mandatory field">*</abbr>
    <input id="email" type="email" placeholder="Enter you email here..." name="email" value="{{ old('email') }}" required>
    @include('partials.inputError', ['field' => 'email'])
    </label>



    <label for="password">Password <abbr class="requiredField" title="mandatory field">*</abbr>
    <input id="password" type="password" placeholder="Enter you password here..." name="password" required>
    @include('partials.inputError', ['field' => 'password'])
    </label>



    <div id="password-meter">
        <span>Password Strength</span>
        <meter max="8" low="2" high="5" optimum="8" id="password-strength" value="0"></meter>
        <button type="button" class="button-icon material-symbols-outlined infohelper" title="Help" data-html="<ul class='password-guidelines'>
      <li>Password must be <strong>at least 10 characters long</strong>.</li>
      <li>Longer passwords (16+ characters) are recommended.</li>
      <li>Should include at least <strong>one lowercase letter</strong>.</li>
      <li>Should include at least <strong>one uppercase letter</strong>.</li>
      <li>Should include at least <strong>one number</strong>.</li>
      <li>Should include at least <strong>one special character</strong> (e.g., @, #, $, etc.).</li>
    </ul>">help</button>
        <span id="password-strength-description"></span>
    </div>
    <div>
      <label for="password-confirm">Confirm Password <abbr class="requiredField" title="mandatory field">*</abbr></label>
      <input id="password-confirm" type="password" placeholder="Confirm you password" name="password_confirmation" required>
      <span id="password-confirmation"></span>
    </div>


    <button type="submit">
      Register
    </button>
    <div>
      <a class="button button-outline" href="{{ route('login') }}">Login</a>
      @include('partials.google')

    </div>
</form>
@endsection