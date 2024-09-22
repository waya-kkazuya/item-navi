<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\WishController;
use App\Http\Controllers\ImageTestController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\ConsumableItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UpdateStockController;
use App\Http\Controllers\InventoryPlanController;
use App\Http\Controllers\DisposalController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\InspectionAndDisposalItemController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\PDFController;
use App\Models\Disposal;
use App\Models\ImageTest;
use App\Models\Inspection;
use App\Models\Item;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Notifications\Notification;

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


// 後で必ず削除

// Route::get('/phpinfo', function () {
//     phpinfo();
// })
// ->middleware(['auth', 'verified', 'can:staff-higher']);



// それぞれに適切な権限レベル(admin,staff,user)のmiddlewareをかける


Route::resource('items', ItemController::class)
->middleware(['auth', 'verified', 'can:staff-higher']);

Route::post('/items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore')
->middleware(['auth', 'verified', 'can:staff-higher']);

Route::middleware(['auth', 'verified', 'can:staff-higher'])->group(function () {
    Route::put('/dispose_item/{item}', [DisposalController::class, 'disposeItem'])->name('dispose_item.disposeItem');
    Route::put('/inspect_item/{item}', [InspectionController::class, 'inspectItem'])->name('inspect_item.inspectItem');


    Route::put('decreaseStock/{item}', [UpdateStockController::class, 'decreaseStock'])->name('decreaseStock');
    Route::put('increaseStock/{item}', [UpdateStockController::class, 'increaseStock'])->name('increaseStock');

    Route::get('/notifications', [NotificationController::class, 'index'])
    ->name('notifications.index');
    
    Route::get('inspection-and-disposal-items', [InspectionAndDisposalItemController::class, 'index'])
    ->name('inspection_and_disposal_items');


    // Route::get('/notifications', function () {
    //     return Inertia::render('Notification');
    // });

});

// リクエストはuser権限でもアクセス可能
// ページのステータスは権限がないと変更できない
Route::middleware(['auth', 'verified', 'can:user-higher'])->group(function () {

    // 省略可能なオプションのルートパラメータ{item_id?}を追加する
    Route::get('consumable_items/{item_id?}', [ConsumableItemController::class, 'index'])->name('consumable_items');
    // Route::get('consumable_items/{id}/history', [ConsumableItemController::class, 'history'])->name('consumable_items.history');

    Route::get('item-requests', [ItemRequestController::class, 'index'])
    ->name('item_requests.index');

    Route::get('item-requests/create', [ItemRequestController::class, 'create'])
    ->name('item_requests.create');

    Route::post('item-requests', [ItemRequestController::class, 'store'])
    ->name('item_requests.store');
});


Route::get('/generate-pdf', [PDFController::class, 'generatePDF'])
->middleware(['auth', 'verified', 'can:staff-higher'])->name('generate_pdf');

// グラフテスト用
// Route::get('analysis', [AnalysisController::class, 'index'])->name('analysis');





Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// ダッシュボード
Route::get('/dashboard', [DashboardController::class, 'index'])
->middleware(['auth', 'verified', 'can:staff-higher'])->name('dashboard');

// Route::get('item-requests', [ItemRequestController::class, 'index'])
// ->middleware(['auth', 'verified', 'can:user-higher'])->name('item_requests.index');



// プロフィール編集用ルート
Route::middleware(['auth', 'verified', 'can:user-higher'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




// ウィッシュリスト
Route::resource('wishes', WishController::class)
->middleware(['auth', 'verified', 'can:user-higher']);

// 棚卸計画
Route::resource('inventory_plans', InventoryPlanController::class)
->middleware(['auth', 'verified', 'can:staff-higher']);

// 画像テスト
// Route::resource('image_tests', ImageTestController::class)
// ->middleware(['auth', 'verified', 'can:user-higher']);



require __DIR__.'/auth.php';