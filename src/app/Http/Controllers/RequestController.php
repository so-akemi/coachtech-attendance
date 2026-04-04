<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index() {
    if (Gate::allows('admin')) {
        return view('admin.requests.index'); // 管理者用View
    }
    return view('requests.index'); // 一般用View
    }
}
