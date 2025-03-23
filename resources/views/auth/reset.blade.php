@extends('layouts.app')

@section('title', 'Reset Password')


@section('content')

<form method="POST" id="register-form" class="form_login">
    @csrf

    <label for="email">E-Mail Address <abbr class="requiredField" title="mandatory field">*</abbr>
    <input id="email" type="email" placeholder="Enter you email here..." name="email" required autofocus>
    @include('partials.inputError', ['field' => 'email'])
    </label>

    <label for="password">Choose your Password <abbr class="requiredField" title="mandatory field">*</abbr>
    <input id="password" type="password" placeholder="Enter you password here..." name="password" required>
    @include('partials.inputError', ['field' => 'password'])
    </label>

    <div>
      <span>Password Strength</span>
        <meter max="8" id="password-strength" value="0"></meter>
        <span id="password-strength-description"></span>
    </div>

    <div>
      <label for="password-confirm">Confirm Password
      <input id="password-confirm" type="password" placeholder="Confirm you password" name="password_confirmation" required>
      </label>
      <span id="password-confirmation"></span>
    </div>

    <button disabled type="submit">
        Reset Password
    </button>
    
</form>
@endsection