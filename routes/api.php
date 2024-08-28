<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Api\ConsumableItemsController;
use App\Http\Controllers\ConsumableItemController;
use App\Models\Edithistory;
use App\Models\StockTransaction;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')
->get('/user', function (Request $request) {
  return $request->user();
});

// 廃棄備品のトグルボタンでの通信用API
// apiはprefixでURLに付いているはず
Route::middleware(['auth:sanctum', 'verified', 'can:staff-higher'])
->get('/items', [ItemController::class, 'index']);

// 以前作成したConsumableItemsController Itemsの複数形があるので後で処理する
Route::middleware(['auth:sanctum', 'verified', 'can:staff-higher'])
->get('/consumable_items', [ConsumableItemController::class, 'index']);


Route::middleware('auth:sanctum')
->get('/edithistory', function (Request $request) {
  return Edithistory::where('item_id', $request->item_id)
  ->orderBy('created_at', 'desc')
  ->take(10)
  ->get();
});

// Route::middleware('auth:sanctum')
// ->get('/stock_transactions', function (Request $request) {
//   return StockTransaction::where('item_id', $request->item_id)
//   ->orderBy('created_at', 'desc')
//   ->take(10)
//   ->get();
// });
Route::middleware('auth:sanctum', 'verified', 'can:staff-higher')
->get('/stock_transactions', [StockTransactionController::class, 'stockTransaction'])
->name('stock_transactions');


// 通知画面用
Route::middleware('auth:sanctum', 'verified', 'can:staff-higher')
->get('/notifications', [NotificationController::class, 'index'])
->name('notifications.index');

// ベルに表示する新着件数用
Route::middleware('auth:sanctum', 'verified', 'can:staff-higher')
->get('/notifications_count', function () {
  return auth()->user()->unreadNotifications;
});


Route::middleware(['auth:sanctum', 'verified', 'can:staff-higher'])->group(function () {
  Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

Route::middleware(['auth:sanctum', 'verified', 'can:staff-higher'])
->post('item-requests/{id}/update-status', [ItemRequestController::class, 'updateStatus']);


Route::middleware('auth:sanctum')
->get('/analysis', [AnalysisController::class, 'index' ])
->name('api.analysis');

// Route::middleware('auth:sanctum')
// ->get('/history', [ConsumableItemsController::class, 'history' ])
// ->name('api.history');


