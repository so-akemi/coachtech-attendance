<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // ログアウト処理の直後に実行されるため、
        // セッションやユーザー判定を使って戻り先を分ける

        // 管理者用ログイン画面からログアウトした、
        // またはURLに 'admin' が含まれている場合などの判定
        return $request->is('admin/*') || $request->is('admin')
            ? redirect('/admin/login')
            : redirect('/login');
    }
}
