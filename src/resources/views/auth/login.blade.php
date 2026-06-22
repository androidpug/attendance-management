@extends('layouts.app')

@section('content')
<div class="auth-container">
    <form class="auth-form" action="/login" method="POST">
        @csrf
        <h1 class="auth-form__title">ログイン</h1>

        <div class="auth-form__group">
            <label class="auth-form__label" for="email">メールアドレス</label>
            <input class="auth-form__input" type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form__group">
            <label class="auth-form__label" for="password">パスワード</label>
            <input class="auth-form__input" type="password" id="password" name="password">
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        @if($errors->has('email') && !$errors->has('name'))
            <p class="error-message">{{ $errors->first('email') }}</p>
        @endif

        <button class="auth-form__button" type="submit">ログインする</button>
        <a class="auth-form__link" href="/register">会員登録はこちら</a>
    </form>
</div>
@endsection