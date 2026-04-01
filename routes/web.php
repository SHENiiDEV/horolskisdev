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

// Public API Endpoints
Route::get('/api/plugins/download', [\App\Http\Controllers\PluginApiController::class, 'download'])->name('api.plugins.download');

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
    Route::post('/projects/{project}/vaults/import', [\App\Http\Controllers\ProjectController::class, 'importVault'])->name('projects.vaults.import');
    Route::get('/projects/{project}/logs/stream', [\App\Http\Controllers\ProjectLogController::class, 'stream'])->name('projects.logs.stream');
    Route::post('/projects/{project}/terminal', [\App\Http\Controllers\ProjectTerminalController::class, 'execute'])->name('projects.terminal.execute');
    Route::post('/projects/{project}/terminal/autocomplete', [\App\Http\Controllers\ProjectTerminalController::class, 'autocomplete'])->name('projects.terminal.autocomplete');

    // File Manager
    Route::get('/projects/{project}/files', [\App\Http\Controllers\ProjectFileController::class, 'index'])->name('projects.files.index');
    Route::get('/projects/{project}/files/content', [\App\Http\Controllers\ProjectFileController::class, 'show'])->name('projects.files.show');
    Route::post('/projects/{project}/files/update', [\App\Http\Controllers\ProjectFileController::class, 'update'])->name('projects.files.update');

    Route::post('/vaults/share', [\App\Http\Controllers\VaultShareController::class, 'store'])->name('vault-shares.store');
    Route::resource('time-entries', \App\Http\Controllers\TimeEntryController::class)->only(['index', 'update', 'destroy']);

    Route::post('/tasks/{task}/start', [\App\Http\Controllers\TimeEntryController::class, 'start'])->name('tasks.start');
    Route::post('/timer/stop', [\App\Http\Controllers\TimeEntryController::class, 'stop'])->name('timer.stop');

    Route::post('/invoices/{id}/restore', [\App\Http\Controllers\InvoiceController::class, 'restore'])->name('invoices.restore');

    // Global Search
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');

    // Plugins
    Route::post('plugins/process-queue', [\App\Http\Controllers\PluginController::class, 'processQueue'])->name('plugins.processQueue');
    Route::resource('plugins', \App\Http\Controllers\PluginController::class);
    Route::post('plugins/{plugin}/version', [\App\Http\Controllers\PluginController::class, 'uploadVersion'])->name('plugins.uploadVersion');
    Route::post('plugins/{plugin}/update-all', [\App\Http\Controllers\PluginController::class, 'updateAllProjects'])->name('plugins.updateAllProjects');
    Route::post('plugins/{plugin}/revert', [\App\Http\Controllers\PluginController::class, 'revertAll'])->name('plugins.revertAll');
    Route::post('plugins/{plugin}/trigger-project/{project}', [\App\Http\Controllers\PluginController::class, 'triggerSingleProject'])->name('plugins.triggerProject');
    Route::post('plugins/{plugin}/assign', [\App\Http\Controllers\PluginController::class, 'assignToProject'])->name('plugins.assign');
    Route::post('projects/{project}/plugins/{plugin}/upgrade', [\App\Http\Controllers\PluginController::class, 'upgradeProjectPlugin'])->name('plugins.upgrade');
    Route::delete('projects/{project}/plugins/{plugin}', [\App\Http\Controllers\PluginController::class, 'removeProjectPlugin'])->name('plugins.remove');
    Route::get('/plugin-versions/{pluginVersion}/download', [\App\Http\Controllers\PluginController::class, 'downloadVersion'])->name('plugin-versions.download');

    // Reports / Analytics
    Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [\App\Http\Controllers\ReportsController::class, 'data'])->name('reports.data');
    Route::get('/reports/task-progress', [\App\Http\Controllers\ReportsController::class, 'taskProgress'])->name('reports.task-progress');

    // Server Status
    Route::get('/monitors', [\App\Http\Controllers\ServerStatusController::class, 'index'])->name('server-status.index');

    // Global Logs
    Route::get('/logs', [\App\Http\Controllers\GlobalLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/stream', [\App\Http\Controllers\GlobalLogController::class, 'stream'])->name('logs.stream');
});
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramController::class, 'handleWebhook'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
// Public Share Routes
Route::get('/s/{share}', [\App\Http\Controllers\VaultShareController::class, 'show'])->name('shares.show');
Route::post('/s/{share}/reveal', [\App\Http\Controllers\VaultShareController::class, 'reveal'])->name('shares.reveal');

// require __DIR__ . '/auth.php';
