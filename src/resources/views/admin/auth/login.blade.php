@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
    <div class="auth-content">
        @include('components.login-form', [
            'title' => '管理者ログイン',
            'action' => route('login'),
            'buttonText' => '管理者ログインする',
            'showRegister' => false,
        ])
    </div>
@endsection
