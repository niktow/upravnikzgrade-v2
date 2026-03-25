<?php

use App\Http\Controllers\Auth\PortalLoginController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [PortalLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [PortalLoginController::class, 'login']);
});

Route::post('/logout', [PortalLoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Portal Routes (Stanarski Portal)
|--------------------------------------------------------------------------
*/
Route::prefix('portal')->name('portal.')->middleware(['auth', 'tenant'])->group(function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/racuni', [PortalController::class, 'statements'])->name('statements');
    Route::get('/racuni/{statement}/download', [PortalController::class, 'downloadStatement'])->name('statements.download');
    Route::get('/racuni/ledger/{ledgerId}/download', [PortalController::class, 'downloadLedgerStatement'])->name('statements.ledger.download');
    Route::get('/oglasi', [PortalController::class, 'announcements'])->name('announcements');
});
