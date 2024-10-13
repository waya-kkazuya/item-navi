<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        Gate::authorize('user-higher');

        $user = Auth::user();
        $profile_image = $user->profile_image;

        // dd($profile_image);
        
        if (is_null($profile_image)) {
            $profile_image_path = asset('storage/profile/profile_default_image.png');
        } else {
            if (Storage::disk('public')->exists('profile/' . $profile_image)) {
                $profile_image_path = asset('storage/profile/' . $profile_image);
            } else {
                $profile_image_path = asset('storage/profile/profile_default_image.png');
            }
        }
        
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'profile_image_path' => $profile_image_path
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('user-higher');

        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Storageへの画像の保存
        $fileNameToStore = null;
        if(!is_null($request->profile_image_file) && $request->profile_image_file->isValid() ){
            // 古い画像があれば削除
            if ($request->user()->profile_image) {
                Storage::disk('public')->delete('profile/' . $request->user()->profile_image);
            }

            // 画像ファイルのアップロードとDBのimage1のファイル名更新
            $fileNameToStore = ImageService::profileImageResizeUpload($request->profile_image_file);
            $request->user()->profile_image = $fileNameToStore;
        }
        
        $request->user()->save();

        return Redirect::route('profile.edit')
        ->with([
            'message' => 'プロフィールを更新しました。',
            'status' => 'success'
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Gate::authorize('staff-higher');

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
