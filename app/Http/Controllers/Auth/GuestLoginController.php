<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GuestLoginController extends Controller
{
    public function guestLogin()
    {
        $guestUser = User::where('email', 'guest@guest.com')->first();

        if ($guestUser) {
            Auth::login($guestUser);
            return redirect('/items');  // ログイン後のリダイレクト先
        }

        return redirect('/login')->with([
                'message' => 'ゲストユーザーが見つかりません',
                'status' => 'danger'
            ]);
    }
}
