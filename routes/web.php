<?php

use App\Http\Middleware\ResolveTenant;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Customer\CustomerDetail;
use App\Livewire\Customer\CustomerIndex;
use App\Livewire\Dashboard\TenantDashboard;
use App\Livewire\Finance\FinanceIndex;
use App\Livewire\Inventory\ProductIndex;
use App\Livewire\Mobile\MobileMenu;
use App\Livewire\Notification\NotificationList;
use App\Livewire\Order\CreateOrder;
use App\Livewire\Order\OrderDetail;
use App\Livewire\Order\OrderIndex;
use App\Livewire\Order\PrintReceipt;
use App\Livewire\Profile\UserProfile;
use App\Livewire\Purchase\PurchaseIndex;
use App\Livewire\Report\ReportDashboard;
use App\Livewire\Service\ServiceCatalog;
use App\Livewire\Store\CreateStore;
use App\Livewire\Store\StoreSettings;
use App\Livewire\Subscription\SubscriptionManager;
use App\Livewire\SuperAdmin\SuperAdminDashboard;
use App\Livewire\Supplier\SupplierIndex;
use App\Livewire\Technician\TechnicianIndex;
use App\Livewire\Tracking\PublicTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

// Public Service Tracking (No authentication required, rate-limited)
Route::get('/track/{token}', PublicTracking::class)
    ->middleware('throttle:60,1')
    ->name('tracking.show');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Store onboarding (accessible before tenant is resolved)
    Route::get('/stores/create', CreateStore::class)->name('stores.create');

    // Logout
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    // Super Admin Platform Routes (No tenant scope required)
    Route::middleware('role:super_admin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/dashboard', SuperAdminDashboard::class)->name('dashboard');
    });

    // Tenant Scoped Routes
    Route::middleware(ResolveTenant::class)->group(function () {
        Route::get('/dashboard', TenantDashboard::class)->name('dashboard');
        Route::get('/menu', MobileMenu::class)->name('menu');

        Route::get('/profile', UserProfile::class)->name('profile');
        Route::get('/settings/store', StoreSettings::class)->middleware('role:owner')->name('settings.store');

        // Customer Management
        Route::get('/customers', CustomerIndex::class)->name('customers.index');
        Route::get('/customers/{id}', CustomerDetail::class)->name('customers.show');

        // Service Catalog
        Route::get('/services', ServiceCatalog::class)->name('services.index');

        // Service Orders
        Route::get('/orders', OrderIndex::class)->name('orders.index');
        Route::get('/orders/create', CreateOrder::class)->name('orders.create');
        Route::get('/orders/{id}', OrderDetail::class)->name('orders.show');
        Route::get('/orders/{id}/print', PrintReceipt::class)->name('orders.print');

        // Inventory & Spareparts
        Route::get('/inventory', ProductIndex::class)->name('inventory.index');

        // Supplier & Purchase
        Route::get('/suppliers', SupplierIndex::class)->name('suppliers.index');
        Route::get('/purchases', PurchaseIndex::class)->name('purchases.index');

        // Finances & Cash Ledger (Owner & Admin)
        Route::get('/finances', FinanceIndex::class)->middleware('role:owner')->name('finances.index');

        // Reports & Analytics (Owner only)
        Route::get('/reports', ReportDashboard::class)->middleware('role:owner')->name('reports.index');

        // Reminders & Notifications
        Route::get('/notifications', NotificationList::class)->name('notifications.index');

        // Subscriptions & Plans (Owner only)
        Route::get('/subscriptions', SubscriptionManager::class)->middleware('role:owner')->name('subscriptions.index');

        // Technician Management (Owner only)
        Route::get('/technicians', TechnicianIndex::class)->middleware('role:owner')->name('technicians.index');
    });
});
