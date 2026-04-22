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
    Route::get('preview-report', [ReportsController::class, 'preview_report'])->name('preview-report');
});

Route::redirect('/login', '/admin/login', 301)->name('login');
