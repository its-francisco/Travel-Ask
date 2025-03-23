@if ($errors->has($field))
    <em class="input-error-message">
        {{ $errors->first($field) }}
    </em>
@endif