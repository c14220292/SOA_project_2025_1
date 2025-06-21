<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\TableController as AdminTableController;
use App\Http\Controllers\Admin\SlotTimeController as AdminSlotTimeController;
use App\Http\Controllers\Member\ReservationController as MemberReservationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::get('/', function () {
    return view('pages.homepage');
})->name('home');

// Authentication Routes
// Route::middleware('guest')->group(function () {
//     Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
//     Route::post('/login', [AuthenticatedSessionController::class, 'store']);
//     Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
//     Route::post('/register', [RegisteredUserController::class, 'store']);
// });

// Route::middleware('auth')->group(function () {
//     Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
// });
//->middleware(['auth', 'role:member'])

// Member Routes
Route::prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', fn() => view('pages.member.dashboard'))->name('dashboard');

    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [MemberReservationController::class, 'index'])->name('index');
        Route::get('/create', [MemberReservationController::class, 'create'])->name('create');
        Route::post('/select-time', [MemberReservationController::class, 'selectTime'])->name('select-time');
        Route::post('/confirm', [MemberReservationController::class, 'confirm'])->name('confirm');
        Route::post('/', [MemberReservationController::class, 'store'])->name('store');
        Route::get('/{reservation}/status', [MemberReservationController::class, 'status'])->name('status');
        Route::get('/{reservation}/confirmed', [MemberReservationController::class, 'confirmed'])->name('confirmed');
        Route::post('/{reservation}/cancel', [MemberReservationController::class, 'cancel'])->name('cancel');
        Route::post('/{reservation}/pay', [MemberReservationController::class, 'pay'])->name('pay');
    });
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('pages.admin.dashboard'))->name('dashboard');

    // Reservations
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [AdminReservationController::class, 'index'])->name('index');
        Route::post('/{reservation}/approve', [AdminReservationController::class, 'approve'])->name('approve');
        Route::post('/{reservation}/reject', [AdminReservationController::class, 'reject'])->name('reject');
        Route::post('/{reservation}/reprocess', [AdminReservationController::class, 'reprocess'])->name('reprocess');
        Route::get('/{reservation}/table-selection', [AdminReservationController::class, 'showTableSelection'])->name('table-selection');
    });

    // Tables
    Route::prefix('tables')->name('tables.')->group(function () {
        Route::get('/', [AdminTableController::class, 'index'])->name('index');
        Route::post('/', [AdminTableController::class, 'store'])->name('store');
        Route::put('/{table}', [AdminTableController::class, 'update'])->name('update');
        Route::delete('/{table}', [AdminTableController::class, 'destroy'])->name('destroy');
    });

    // Slot Times
    Route::prefix('slots')->name('slots.')->group(function () {
        Route::get('/', [AdminSlotTimeController::class, 'index'])->name('index');
        Route::post('/', [AdminSlotTimeController::class, 'store'])->name('store');
        Route::delete('/{slotTime}', [AdminSlotTimeController::class, 'destroy'])->name('destroy');
    });
});
