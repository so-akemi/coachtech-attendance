<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // ログアウト処理の直後に実行されるため、
        // セッションやユーザー判定を使って戻り先を分ける

        $referer = $request->headers->get('referer');

        if ($request->is('admin/*') || (is_string($referer) && str_contains($referer, '/admin'))) {
            return redirect('/admin/login');
        }

        // それ以外（一般ユーザー）
        return redirect('/login');

        // 管理者用ログイン画面からログアウトした、
        // またはURLに 'admin' が含まれている場合などの判定
        //return $request->is('admin/*') || $request->is('admin')
            //? redirect('/admin/login')
            //: redirect('/login');
        //if ($request->is('admin/*')) {
            //return redirect('/admin/login');
        //}

        //// 一般ユーザーの場合
        //return redirect('/login');
    }
}
