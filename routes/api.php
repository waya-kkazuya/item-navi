<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConsumableItemController;
use App\Http\Controllers\Api\EdithistoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VueErrorController;

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


// トグルボタンでの廃棄備品データを取得する
Route::middleware(['auth:sanctum', 'verified', 'can:staff-higher'])
->get('/items', [ItemController::class, 'index']);

// 対象の備品の編集履歴を取得する
Route::middleware(['auth:sanctum', 'verified', 'can:staff-higher'])
->get('/edithistory', [EdithistoryController::class, 'index']);

// 在庫数の入出庫履歴を取得する
Route::middleware('auth:sanctum', 'verified', 'can:user-higher')
->get('/stock_transactions', [StockTransactionController::class, 'stockTransaction'])
->name('stock_transactions');

// 消耗品の入出・出庫モーダルで更新処理をした際、在庫数をリアルタイムに反映する
Route::middleware(['auth:sanctum', 'verified', 'can:staff-higher'])
->get('/consumable_items', [ConsumableItemController::class, 'index']);

// 通知を表示したら既読にする
Route::middleware(['auth:sanctum', 'verified', 'can:staff-higher'])->group(function () {
  Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

// ベルに未読通知数を表示する
Route::middleware('auth:sanctum', 'verified', 'can:staff-higher')
->get('/notifications_count', function () {
  return auth()->user()->unreadNotifications;
});


// リクエストのステータスのプルダウンを変更する
Route::middleware(['auth:sanctum', 'verified', 'can:staff-higher', 'RestrictGuestAccess'])
->post('item-requests/{id}/update-status', [ItemRequestController::class, 'updateStatus'])
->name('item-requests.update-status');

// リクエスト一覧画面でユーザーの権限情報を取得する
Route::middleware(['auth:sanctum', 'verified', 'can:user-higher'])
->get('/user-role', function (Request $request) {
  return auth()->user()->role;
});


// Vue側のエラーをAPIでログに書き込む
Route::middleware(['auth:sanctum', 'verified', 'can:user-higher'])
->post('/log-error', [VueErrorController::class, 'logError']);