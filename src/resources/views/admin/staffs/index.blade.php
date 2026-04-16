@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staffs/index.css') }}">
@endsection

@section('content')
    <div class="staff-list">
        <h1 class="staff-list-title">スタッフ一覧</h1>

        <table class="staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            {{-- スタッフ別勤怠一覧(PG11)へのリンク --}}
                            <a href="{{ route('admin.attendance.staff', $user->id) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
