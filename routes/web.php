<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController; // We'll create this next
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'store']);
});

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::resource('clients', ClientController::class);
        Route::resource('users', \App\Http\Controllers\UserController::class);
    });

    Route::get('clients/{client}/kanban', [\App\Http\Controllers\TaskController::class, 'clientKanban'])->name('clients.kanban');
    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
    Route::get('/invoices/create-from-tasks', [\App\Http\Controllers\InvoiceController::class, 'createFromTasks'])->name('invoices.create-from-tasks');
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    Route::post('/integrations', [\App\Http\Controllers\IntegrationController::class, 'store'])->name('integrations.store');
    Route::delete('/integrations/{integration}', [\App\Http\Controllers\IntegrationController::class, 'destroy'])->name('integrations.destroy');
    Route::get('/integrations/{integration}/download', [\App\Http\Controllers\IntegrationController::class, 'download'])->name('integrations.download');

    // Telegram
    Route::get('/telegram', [\App\Http\Controllers\TelegramController::class, 'index'])->name('telegram.index');

    Route::get('/kanban', [\App\Http\Controllers\TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::resource('tasks', \App\Http\Controllers\TaskController::class);
    Route::resource('vaults', \App\Http\Controllers\VaultController::class);
    Route::resource('time-entries', \App\Http\Controllers\TimeEntryController::class)->only(['index', 'update', 'destroy']);

    Route::post('/tasks/{task}/start', [\App\Http\Controllers\TimeEntryController::class, 'start'])->name('tasks.start');
    Route::post('/timer/stop', [\App\Http\Controllers\TimeEntryController::class, 'stop'])->name('timer.stop');

    Route::post('/invoices/{id}/restore', [\App\Http\Controllers\InvoiceController::class, 'restore'])->name('invoices.restore');

    // Global Search
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');

    // Reports / Analytics
    Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [\App\Http\Controllers\ReportsController::class, 'data'])->name('reports.data');
});
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramController::class, 'handleWebhook'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
// require __DIR__ . '/auth.php';
