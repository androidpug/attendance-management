@extends('layouts.app')

@section('content')
<div class="auth-container">
    <form class="auth-form" action="/register" method="POST">
        @csrf
        <h1 class="auth-form__title">会員登録</h1>

        <div class="auth-form__group">
            <label class="auth-form__label" for="name">名前</label>
            <input class="auth-form__input" type="text" id="name" name="name" value="{{ old('name') }}">
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

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

        <div class="auth-form__group">
            <label class="auth-form__label" for="password_confirmation">パスワード確認</label>
            <input class="auth-form__input" type="password" id="password_confirmation" name="password_confirmation">
        </div>

        <button class="auth-form__button" type="submit">登録する</button>
        <a class="auth-form__link" href="/login">ログインはこちら</a>
    </form>
</div>
@endsection