@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/requests/index.css') }}">
@endsection

@section('content')
    <div class="request-container">
        <h1 class="page-title">申請一覧</h1>

        <div class="tabs">
            <a href="{{ route('request.index', ['status' => 'pending']) }}"
                class="tab-item {{ $statusParam === 'pending' ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('request.index', ['status' => 'approved']) }}"
                class="tab-item {{ $statusParam === 'approved' ? 'active' : '' }}">承認済み</a>
        </div>

        <table class="request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ $request->status == 0 ? '承認待ち' : '承認済み' }}</td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</td>
                        <td>{{ $request->reason }}</td>
                        <td>{{ $request->created_at->format('Y/m/d') }}</td>
                        <td>
                            <!-- ここで詳細画面（PG04）へ飛びます -->
                            <a href="{{ route('attendance.show', $request->attendance_id) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">申請はありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
