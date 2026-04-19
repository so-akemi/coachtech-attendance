@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
    <div class="login-content">
        @include('components.login-form', [
            'title' => 'ログイン',
            'action' => route('login'),
            'buttonText' => 'ログインする',
            'showRegister' => true,
        ])
    </div>
@endsection
