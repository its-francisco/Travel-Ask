@extends('layouts.app')


@section('content')

    <div class="error-page">
        <div>
            <h1 class="">
                @yield('code')
            </h1>

            <p class="">
                @yield('message')
            </p>
        </div>
        <div>
            <a href="{{route('index')}}">Return home</a>
        </div>
            
    </div>


@endsection
