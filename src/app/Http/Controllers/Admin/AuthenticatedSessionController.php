<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    // ログイン処理
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // 管理者かどうかチェック（roleカラムなどで判定）
            if (Auth::user()->is_admin) {
                return redirect()->intended('/admin/attendance/list');
            }

            // 管理者でなければログアウトさせてエラーにする
            Auth::logout();
            return back()->withErrors(['email' => '管理者権限がありません。']);
        }

        return back()->withErrors(['email' => 'ログイン情報が正しくありません。']);
    }
}
