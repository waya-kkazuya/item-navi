<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ImageService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function edit(Request $request): Response
    {
        Gate::authorize('user-higher');

        Log::info('ProfileController edit method called');

        $user          = Auth::user();
        $profile_image = $user->profile_image;

        $defaultDisk = Storage::disk();

        if (is_null($profile_image)) {
            $profile_image_path = $defaultDisk->url('profile/profile_default_image.png');
        } else {
            if (Storage::disk()->exists('profile/' . $profile_image)) {
                $profile_image_path = $defaultDisk->url('profile/' . $profile_image);
            } else {
                $profile_image_path = $defaultDisk->url('profile/profile_default_image.png');
            }
        }

        Log::info('ProfileController edit method succeeded');

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail'    => $request->user() instanceof MustVerifyEmail,
            'status'             => session('status'),
            'profile_image_path' => $profile_image_path,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('user-higher');

        Log::info('ProfileController update method called');

        // ロールバックした時のプロフィール画像を元に戻す準備
        if (! Storage::disk()->exists('temp')) {
            Storage::disk()->makeDirectory('temp');
        }

        // 変数の初期化
        $profileImagefileNameToStore = null;
        $fileNameOfOldProfileImage   = null;
        $temporaryBackupPath         = null;

        DB::beginTransaction();

        try {
            $request->user()->fill($request->validated());

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            if (! is_null($request->profile_image_file) && $request->profile_image_file->isValid()) {
                // すでに画像があれば削除
                $fileNameOfOldProfileImage = $request->user()->profile_image;
                if ($fileNameOfOldProfileImage) {
                    $temporaryBackupPath = 'temp/' . $fileNameOfOldProfileImage;
                    Storage::disk()->copy('items/' . $fileNameOfOldProfileImage, $temporaryBackupPath);
                    Storage::disk()->delete('profile/' . $request->user()->profile_image);
                }

                // 画像ファイルのアップロードとDBのimage1のファイル名更新
                $profileImagefileNameToStore    = $this->imageService->profileImageResizeUpload($request->profile_image_file);
                $request->user()->profile_image = $profileImagefileNameToStore;
            }

            $request->user()->save();

            DB::commit();

            Log::info('ProfileController update method succeeded');

            return Redirect::route('profile.edit')
                ->with([
                    'message' => 'プロフィールを更新しました。',
                    'status'  => 'success',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('ProfileController update method Transaction failed', [
                'error'   => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            // アップロードした備品の画像の削除
            if (Storage::disk()->exists('profile/' . $profileImagefileNameToStore)) {
                Storage::disk()->delete('profile/' . $profileImagefileNameToStore);
            }

            // バックアップした変更前のプロフィール画像を元の場所に保存
            if ($temporaryBackupPath) {
                Storage::disk()->move($temporaryBackupPath, 'items/' . $fileNameOfOldProfileImage);
            }

            return redirect()->back()
                ->with([
                    'message' => 'プロフィールの更新中にエラーが発生しました',
                    'status'  => 'danger',
                ]);
        }
    }
}
