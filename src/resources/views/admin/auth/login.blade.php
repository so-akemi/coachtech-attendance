@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="auth-content">
    @include('components.login-form', [
        'title' => '管理者ログイン',
        'action' => '/admin/login',
        'buttonText' => '管理者ログインする',
        'showRegister' => false
    ])
</div>
@endsection
