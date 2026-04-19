<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // 管理者権限を持っている場合、または管理者URLからのログイン
        if (Auth::user()->is_admin || $request->is('admin/*')) {
            return redirect()->intended('/admin/attendance/list');
        }

        // 一般ユーザーの場合
        return redirect()->intended('/attendance');
    }
}
