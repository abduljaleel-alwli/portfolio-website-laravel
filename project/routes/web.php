<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// ---> Public Landing Page
// -- Home
Route::get('/', fn() => view('welcome'))->name('home');

// --> products
Volt::route('/products', 'products.index')->name('products.index');
Volt::route('/about', 'about.index')->name('about.index');
Volt::route('/contact', 'contact.index')->name('contact.index');


// ---> Super-Admin & Admin
Route::middleware(['auth', 'verified', 'role:admin|super-admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        // Dashboard
        Volt::route('/dashboard', 'admin.dashboard.dashboard')
            ->name('dashboard');

        // Products Management
        Volt::route('/products', 'admin.products.products-manager')
            ->name('products');

        // Categories Management
        Volt::route('/categories', 'admin.categories.categories-manager')
            ->name('categories');


        // Settings Management (CMS Settings)
        Volt::route('/settings', 'admin.settings')
            ->name('settings');

        // About Management
        Volt::route('/about', 'admin.about.about-manager')->name('about');

        // Contact Management
        Volt::route('/contact', 'admin.contact.contact-manager')->name('contact');

        // Contact messages Management
        Volt::route('/contact-messages', 'admin.contact.contact-messages')
            ->name('contact-messages');

        // Notifications Management
        Volt::route('/notifications', 'admin.notifications.notifications-manager')
            ->name('notifications');

    });

// ---> Super-Admin only
Route::middleware(['auth', 'verified', 'role:super-admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        Volt::route('/users', 'admin.users.users-manager')->name('users');
        Volt::route('/audit-logs', 'admin.logs.audit-logs')->name('audit-logs');
    });


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
