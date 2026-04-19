@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="register-form-content">
        <div class="register-form-heading">
            <h1>会員登録</h1>
        </div>

        <!-- novalidateを外してテスト -->
        <form class="form" method="POST" action="{{ route('register') }}">
            @csrf

            <!-- 名前 -->
            <div class="form-group">
                <div class="form-group-title">
                    <span class="form-label-item">名前</span>
                </div>
                <div class="form-group-content">
                    <div class="form-input-text">
                        <input type="text" name="name" value="{{ old('name') }}">
                    </div>
                    @error('name')
                        <div class="error-massage">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- メール -->
            <div class="form-group">
                <div class="form-group-title">
                    <span class="form-label-item">メールアドレス</span>
                </div>
                <div class="form-group-content">
                    <div class="form-input-text">
                        <input type="email" name="email" value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <div class="error-massage">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- パスワード -->
            <div class="form-group">
                <div class="form-group-title">
                    <span class="form-label-item">パスワード</span>
                </div>
                <div class="form-group-content">
                    <div class="form-input-text">
                        <input type="password" name="password">
                    </div>
                    @error('password')
                        <div class="error-massage">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- 確認用パスワード -->
            <div class="form-group">
                <div class="form-group-title">
                    <span class="form-label-item">確認用パスワード</span>
                </div>
                <div class="form-group-content">
                    <div class="form-input-text">
                        <input type="password" name="password_confirmation">
                    </div>
                    @error('password_confirmation')
                        <div class="error-massage">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-button">
                <button class="form-button-submit" type="submit">登録する</button>
            </div>
        </form>

        <div class="login-link">
            <a class="login-button-submit" href="{{ route('login') }}">ログインはこちら</a>
        </div>
    </div>
@endsection
