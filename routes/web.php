<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin panel (session auth + active check)
Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/people', \App\Livewire\People\PeopleIndex::class)->name('people.index');

    Route::get('/membership', \App\Livewire\Membership\MembershipIndex::class)->name('membership.index');

    Route::get('/discipleships', \App\Livewire\Discipleship\DiscipleshipIndex::class)->name('discipleships.index');
    Route::get('/discipleships/{discipleship}/assignments', \App\Livewire\Discipleship\AssignmentManager::class)->name('discipleships.assignments');

    Route::get('/library', \App\Livewire\Library\MaterialsIndex::class)->name('library.index');
    Route::get('/library/{studyMaterial}/loans', \App\Livewire\Library\LoanManager::class)->name('library.loans');

    Route::get('/events', \App\Livewire\Events\EventsIndex::class)->name('events.index');
    Route::get('/events/{event}/attendance', \App\Livewire\Events\AttendanceSheet::class)->name('events.attendance');
});

// Root redirect
Route::get('/', fn () => redirect()->route('admin.dashboard'));
