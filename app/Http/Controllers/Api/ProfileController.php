<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function getProfileImage()
    {
        $user = Auth::user();
        return response()->json([
            'profile_image_url' => $user->profile_image
                ? Storage::disk('s3')->url('profile/' . $user->profile_image)
                : Storage::disk('s3')->url('profile/profile_default_image.png'),
        ]);
    }
}
