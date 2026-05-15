<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Import Page Components
use App\Livewire\Login;
use App\Livewire\ForgotPassword;
use App\Livewire\ResetPassword;

// Admin Components
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\DailyReports;
use App\Livewire\Admin\MenuManagement;
use App\Livewire\Admin\DiscountManagement;
use App\Livewire\Admin\UserManagement;

// Cashier Components
use App\Livewire\Cashier\CreateOrder;
use App\Livewire\Cashier\SelfOrder;
use App\Livewire\Cashier\OrderList;

// Kitchen Components
use App\Livewire\Kitchen\OrderList as KitchenOrderList;

// Accounting Components
use App\Livewire\Accounting\OrderItems;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Logika Redirect Utama berdasarkan Role ---
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    
    // Menggunakan strtolower agar pengecekan tidak sensitif terhadap huruf besar/kecil di DB
    switch (strtolower($user->role)) {
        case 'admin':
            return redirect()->route('admin.dashboard');
        case 'accounting':
            return redirect()->route('accounting.dashboard');
        case 'cashier':
            return redirect()->route('cashier.create-order');
        case 'kitchen':
            return redirect()->route('kitchen.order-list');
        case 'it':
            return redirect()->route('it.user-management');
        default:
            Auth::logout();
            return redirect()->route('login')->with('error', 'Role tidak memiliki akses ke sistem.');
    }
});

// --- Authentication Routes ---
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::get('/forgot-password', ForgotPassword::class)->middleware('guest')->name('forgot-password');
Route::get('/reset-password/{token}', ResetPassword::class)->middleware('guest')->name('password.reset');

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');


// --- Group: ADMIN (Hanya Role Admin) ---
Route::prefix('/admin')->middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('admin.dashboard');
    Route::get('/daily-reports', DailyReports::class)->name('admin.daily-reports');
    Route::get('/menu-management', MenuManagement::class)->name('admin.menu-management');
    Route::get('/discount-management', DiscountManagement::class)->name('admin.discount-management');
});


// --- Group: ACCOUNTING / FINANCE (Hanya Role Accounting) ---
Route::prefix('/accounting')->middleware(['auth', 'role:Accounting'])->group(function () {
    // Finance diarahkan ke URL ini, BUKAN ke /admin/dashboard
    Route::get('/dashboard', Dashboard::class)->name('accounting.dashboard');
    Route::get('/daily-reports', DailyReports::class)->name('accounting.daily-reports');
    Route::get('/order-items', OrderItems::class)->name('accounting.order-items');
});


// --- Group: CASHIER ---
Route::prefix('/cashier')->middleware(['auth', 'role:Cashier'])->group(function () {
    Route::get('/create-order', CreateOrder::class)->name('cashier.create-order');
    Route::get('/self-order', SelfOrder::class)->name('cashier.self-order');
    Route::get('/order-list', OrderList::class)->name('cashier.order-list');
});


// --- Group: KITCHEN ---
Route::prefix('/kitchen')->middleware(['auth', 'role:Kitchen'])->group(function () {
    Route::get('/order-list', KitchenOrderList::class)->name('kitchen.order-list');
});


// --- Group: IT ---
Route::prefix('/it')->middleware(['auth', 'role:IT'])->group(function () {
    Route::get('/user-management', UserManagement::class)->name('it.user-management');
});

// ================= EASTER EGG START =================
Route::get('/kopi-rahasia', function () {
    // Menjalankan mesin pembuat kopi di belakang layar
    \Illuminate\Support\Facades\Artisan::call('app:seduh-kopi');
    
    // Menampilkan hasil ASCII kopi ke layar browser dengan gaya Hacker
    return '<body style="background-color: #111; color: #0f0; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; font-family: monospace; font-size: 20px; font-weight: bold;">
        <pre>
               (  )   (   )  )
                ) (   )  (  (
                ( )  (    ) )
                _____________
               <_____________> ___
               |             |/ _ \
               |               | | |
               |               |_| |
            ___|             |\___/
           /    \___________/    \
           \_____________________/
        </pre>
        <p style="margin-top: 20px;">☕ Kopi virtual siap! Mesin POS berjalan lancar. Tetap semangat bosku! 🚀</p>
    </body>';
});
// ================= EASTER EGG END =================