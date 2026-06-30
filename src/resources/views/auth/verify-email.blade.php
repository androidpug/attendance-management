@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-form" style="max-width: 1000px;">
        <p style="text-align: center; margin-bottom: 40px; font-size: 24px;">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a href="http://localhost:8025" target="_blank" class="auth-form__button--inline">
            認証はこちらから
        </a>

        @if(session('status') == 'verification-link-sent')
            <p style="text-align: center; color: green; margin-top: 20px; font-size: 20px;">
                認証メールを再送しました。
            </p>
        @else
            <form method="POST" action="/email/verification-notification" style="margin-top: 20px; text-align: center;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #0073CC; cursor: pointer; font-size: 20px; text-decoration: none;">
                    認証メールを再送する
                </button>
            </form>
        @endif
    </div>
</div>
@endsection