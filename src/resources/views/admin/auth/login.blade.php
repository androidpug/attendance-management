@extends('layouts.app')

@section('content')
<div class="auth-container">
    <form class="auth-form" action="/admin/login" method="POST">
        @csrf
        <h1 class="auth-form__title">管理者ログイン</h1>

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

        <button class="auth-form__button" type="submit">管理者ログインする</button>
    </form>
</div>
@endsection