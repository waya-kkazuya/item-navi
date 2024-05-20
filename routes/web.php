<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\WishController;
use App\Http\Controllers\ImageTestController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\ConsumableItemsController;
use App\Http\Controllers\UpdateStockController;
use App\Models\ImageTest;

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

// Route::middleware('can:user-higher')
// ->group(function(){

// });
Route::put('updateStock/{id}', [UpdateStockController::class, 'updateStock'])->name('updateStock');

Route::get('analysis', [AnalysisController::class, 'index'])->name('analysis');

Route::get('consumable_items', [ConsumableItemsController::class, 'index'])->name('consumable_items');


Route::resource('items', ItemController::class)
->middleware(['auth', 'verified', 'can:staff-higher']);

Route::resource('wishes', WishController::class)
->middleware(['auth', 'verified', 'can:user-higher']);

Route::resource('image_tests', ImageTestController::class)
->middleware(['auth', 'verified', 'can:user-higher']);


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
