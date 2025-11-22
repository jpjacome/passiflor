<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\TherapyController;

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('home');
});

Route::get('/therapy/{slug?}', [TherapyController::class, 'show'])->name('therapy.show');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/test', function () {
    return 'Laravel is working!';
});

// Public consultation request route
Route::post('/consultations', [ConsultationController::class, 'store'])->name('consultations.store');

// Protected routes for admin and therapists
Route::middleware(['auth'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    // Therapies Management (Admin) - placeholder view
    Route::get('/admin/therapies', function () {
        return view('admin.therapies');
    })->name('admin.therapies');
    
    // Consultation Management (Admin)
    Route::get('/admin/consultations', [AdminConsultationController::class, 'index'])->name('admin.consultations.index');
    Route::patch('/admin/consultations/{consultation}/status', [AdminConsultationController::class, 'updateStatus'])->name('admin.consultations.updateStatus');
    Route::delete('/admin/consultations/{consultation}', [AdminConsultationController::class, 'destroy'])->name('admin.consultations.destroy');
    
    // Therapies Management (Admin) - resourceful controller
    Route::get('/admin/therapies', [App\Http\Controllers\Admin\TherapyController::class, 'index'])->name('admin.therapies.index');
    Route::get('/admin/therapies/create', [App\Http\Controllers\Admin\TherapyController::class, 'create'])->name('admin.therapies.create');
    Route::post('/admin/therapies', [App\Http\Controllers\Admin\TherapyController::class, 'store'])->name('admin.therapies.store');
    Route::get('/admin/therapies/{therapy}/edit', [App\Http\Controllers\Admin\TherapyController::class, 'edit'])->name('admin.therapies.edit');
    Route::patch('/admin/therapies/{therapy}', [App\Http\Controllers\Admin\TherapyController::class, 'update'])->name('admin.therapies.update');
    Route::delete('/admin/therapies/{therapy}', [App\Http\Controllers\Admin\TherapyController::class, 'destroy'])->name('admin.therapies.destroy');
    
    // User Management (Admin only)
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::patch('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});