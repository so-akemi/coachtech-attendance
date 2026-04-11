@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="register-form-content">
        <div class="register-form-heading">
            <h1>会員登録</h1>
        </div>

        <form class="form" method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <div class="form-group">
                <div class="form-group-title">
                    <span class="form-label-item">名前</span>
                </div>
                <div class="form-group-content">
                    <div class="form-input-text">
                        <input type="text" name="name" value="{{ old('name') }}" />
                    </div>
                    <div class="error-massage">
                        @error('name')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-group-title">
                    <span class="form-label-item">メールアドレス</span>
                </div>
                <div class="form-group-content">
                    <div class="form-input-text">
                        <input type="email" name="email" value="{{ old('email') }}" />
                    </div>
                    <div class="error-massage">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-group-title">
                    <span class="form-label-item">パスワード</span>
                </div>
                <div class="form-group-content">
                    <div class="form-input-text">
                        <input type="password" name="password" />
                    </div>
                    <div class="error-massage">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-group-title">
                    <span class="form-label-item">確認用パスワード</span>
                </div>
                <div class="form-group-content">
                    <div class="form-input-text">
                        <input type="password" name="password_confirmation" />
                    </div>
                    <div class="error-massage">
                        @error('password_confirmation')
                            {{ $message }}
                        @enderror
                    </div>
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
