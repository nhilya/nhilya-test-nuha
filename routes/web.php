<?php

use App\Http\Controllers\ListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

Route::get('/register', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * List management routes - protected by auth middleware
 */
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('lists', [ListController::class, 'index'])->name('lists.index');
    Route::post('lists', [ListController::class, 'store'])->name('lists.store');
    Route::put('lists/{list}', [ListController::class, 'update'])->name('lists.update');
    Route::delete('lists/{list}', [ListController::class, 'destroy'])->name('lists.destroy');
});

/**
 * Task management routes - protected by auth middleware
 */
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});

/**
 * Kanban task management routes - protected by auth middleware
 */
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('kanban/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('kanban/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('kanban/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('kanban/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});

require __DIR__.'/auth.php';
