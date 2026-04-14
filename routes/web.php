<?php

use App\Http\Controllers\EvaluationFormPDF;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/evaluation-result', [EvaluationFormPDF::class, 'getEvaluationResult'])->name('queues-call-next');

    // Reports
    Route::get('bot-performance-summary', [ReportsController::class, 'bot_performance_summary'])->name('bot-performance-summary');
    Route::get('download-bot-performance-summary', [ReportsController::class, 'download_bot_performance_summary'])->name('download-bot-performance-summary');
});

Route::redirect('/login', '/admin/login', 301)->name('login');
