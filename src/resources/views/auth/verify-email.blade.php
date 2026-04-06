@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/verify.css') }}">
@endsection

@section('content')
    <div class="verify-email-container">
        <div class="verify-email-card">
            <p class="verify-email-text">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください。
            </p>

            <div class="verify-email-action">
                <a href="http://localhost:8025" target="_blank" class="verify-email-button">認証はこちらから</a>
            </div>

            <form method="POST" action="{{ route('verification.send') }}" class="verify-email-resend-form">
                @csrf
                <button type="submit" class="verify-email-resend-link">
                    認証メールを再送する
                </button>
            </form>

            @if (session('status') == 'verification-link-sent')
                <p class="verify-email-success">
                    新しい認証メールを再送しました。
                </p>
            @endif
        </div>
    </div>
@endsection
