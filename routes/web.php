<?php

use App\Http\Controllers\Auth\GuestLoginController;
use App\Http\Controllers\ConsumableItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposalController;
use App\Http\Controllers\InspectionAndDisposalItemController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UpdateStockController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// それぞれ権限レベルadmin、staff、user、guestが存在する
// staff-higherはadmin、staff、guestが実行可能
// user-higherはadmin、staff、user、guestが実行可能

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
        'images' => [
            'heroBackground' => Storage::disk()->url('images/hero_background.png'),
            'heroImage' => Storage::disk()->url('images/hero_image.png'),
            'item' => Storage::disk()->url('images/item.png'),
            'graph' => Storage::disk()->url('images/graph.png'),
            'checklist' => Storage::disk()->url('images/checklist.png'),
            'lightBulb' => Storage::disk()->url('images/light_bulb.png'),
            'bell' => Storage::disk()->url('images/bell.png'),
            'users' => Storage::disk()->url('images/users.png'),
            'qrcode' => Storage::disk()->url('images/qrcode.png'),
            'pdf' => Storage::disk()->url('images/pdf.png'),
        ],
    ]);
});

// ゲストログイン
Route::post('/guest-login', [GuestLoginController::class, 'guestLogin'])->name('guest.login');

// ダッシュボード
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'CheckRoleUser', 'can:staff-higher'])->name('dashboard');

// 備品のItemControllerはリソースコントローラなので、
// ゲストログインのミドルウェアはコンストラクタ内で適用
Route::resource('items', ItemController::class)->middleware(['auth', 'verified', 'can:staff-higher']);
Route::post('/items/{id}/restore', [ItemController::class, 'restore'])
    ->middleware(['auth', 'verified', 'can:staff-higher'])->name('items.restore');

// 備品詳細画面の廃棄処理と点検処理
Route::middleware(['auth', 'verified', 'can:staff-higher'])->group(function () {
    Route::middleware('RestrictGuestAccess')->put('/dispose_item/{item}', [DisposalController::class, 'disposeItem'])->name('dispose_item.disposeItem');
    Route::middleware('RestrictGuestAccess')->put('/inspect_item/{item}', [InspectionController::class, 'inspectItem'])->name('inspect_item.inspectItem');
});

// 消耗品管理画面
// 省略可能なオプションのルートパラメータ{item_id?}は、指定された場合該当の備品のモーダルを開く
Route::get('consumable_items/{item_id?}', [ConsumableItemController::class, 'index'])
    ->middleware(['auth', 'verified', 'can:user-higher'])->name('consumable_items');
// 入庫・出庫処理
Route::middleware(['auth', 'verified', 'can:user-higher'])->group(function () {
    Route::middleware('RestrictGuestAccess')->put('decreaseStock/{item}', [UpdateStockController::class, 'decreaseStock'])->name('decreaseStock');
    Route::middleware('RestrictGuestAccess')->put('increaseStock/{item}', [UpdateStockController::class, 'increaseStock'])->name('increaseStock');
});
// 消耗品管理画面でPDFのダウンロード
Route::get('/generate-pdf', [PDFController::class, 'generatePDF'])
    ->middleware(['auth', 'verified', 'can:user-higher'])->name('generate_pdf');

// 点検と廃棄
Route::get('inspection-and-disposal-items', [InspectionAndDisposalItemController::class, 'index'])
    ->middleware(['auth', 'verified', 'can:staff-higher'])->name('inspection_and_disposal_items');

// 通知画面
Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware(['auth', 'verified', 'can:staff-higher'])->name('notifications.index');

// リクエスト一覧画面のステータス変更はAPIで行う
Route::middleware(['auth', 'verified', 'can:user-higher'])->group(function () {
    Route::get('item-requests', [ItemRequestController::class, 'index'])->name('item_requests.index');
    Route::get('item-requests/create', [ItemRequestController::class, 'create'])->name('item_requests.create');
    Route::middleware('RestrictGuestAccess')->post('item-requests', [ItemRequestController::class, 'store'])->name('item_requests.store');
});
// リクエストの削除はstaff以上の権限が必要
Route::delete('item-requests/{item_request}', [ItemRequestController::class, 'destroy'])
    ->middleware(['auth', 'verified', 'can:staff-higher', 'RestrictGuestAccess'])->name('item_requests.destroy');

// プロフィール編集用ルート
Route::middleware(['auth', 'verified', 'can:user-higher'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::middleware('RestrictGuestAccess')->patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// AWSのターゲットグループのヘルスチェック用
Route::get('/health-check', function () {
    return response()->json(['status' => 'OK']);
})->withoutMiddleware([]);

require __DIR__ . '/auth.php';
