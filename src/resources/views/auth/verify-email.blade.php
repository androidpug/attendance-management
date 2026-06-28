@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-form">
        <h2 class="auth-form__title">メール認証</h2>

        <p style="text-align: center; margin-bottom: 20px; font-size: 14px;">
            登録していただいたメールアドレスに認証メールを送信しました。<br>
            メールに記載されているリンクをクリックして認証を完了してください。
        </p>

        @if(session('status') == 'verification-link-sent')
            <p style="text-align: center; color: green; margin-bottom: 20px; font-size: 14px;">
                認証メールを再送しました。
            </p>
        @endif

        <form method="POST" action="/email/verification-notification">
            @csrf
            <button type="submit" class="auth-form__button">
                認証メールを再送する
            </button>
        </form>

        <form method="POST" action="/logout" style="margin-top: 15px;">
            @csrf
            <button type="submit" class="auth-form__button" style="background-color: #666;">
                ログアウト
            </button>
        </form>
    </div>
</div>
@endsection