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
use App\Http\Controllers\UpdateStockController;
use App\Http\Controllers\InventoryPlanController;
use App\Http\Controllers\DisposalController;
use App\Http\Controllers\InspectionController;
use App\Models\Disposal;
use App\Models\ImageTest;
use App\Models\Inspection;
use App\Models\Item;

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





// それぞれに適切な権限レベル(admin,staff,user)のmiddlewareをかける


Route::resource('items', ItemController::class)
->middleware(['auth', 'verified', 'can:staff-higher']);

Route::post('/items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore')
->middleware(['auth', 'verified', 'can:staff-higher']);

// 廃棄された備品
// Route::prefix('disposed-items')
// ->middleware(['auth', 'verified', 'can:staff-higher'])->group(function(){
//     Route::get('index', [ItemController::class, 'disposedItemIndex'])
//     ->name('disposeditems.index');

// });




Route::middleware('can:user-higher')->group(function () {
    Route::put('/dispose_item/{item}', [DisposalController::class, 'disposeItem'])->name('dispose_item.disposeItem');
    Route::put('/inspect_item/{item}', [InspectionController::class, 'inspectItem'])->name('inspect_item.inspectItem');

    Route::get('consumable_items', [ConsumableItemController::class, 'index'])->name('consumable_items');
    
    Route::get('consumable_items/{id}/history', [ConsumableItemsController::class, 'history'])->name('consumable_items.history');

    Route::put('decreaseStock/{item}', [UpdateStockController::class, 'decreaseStock'])->name('decreaseStock');
    Route::put('increaseStock/{item}', [UpdateStockController::class, 'increaseStock'])->name('increaseStock');
});





// グラフテスト用
// Route::get('analysis', [AnalysisController::class, 'index'])->name('analysis');

// ウィッシュリスト
Route::resource('wishes', WishController::class)
->middleware(['auth', 'verified', 'can:user-higher']);

// // 棚卸計画
Route::resource('inventory_plans', InventoryPlanController::class)
->middleware(['auth', 'verified', 'can:staff-higher']);

// 画像テスト
// Route::resource('image_tests', ImageTestController::class)
// ->middleware(['auth', 'verified', 'can:user-higher']);




Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


// ダッシュボード
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// プロフィール編集用ルート
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';