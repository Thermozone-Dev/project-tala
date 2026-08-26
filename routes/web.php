<?php

use App\Http\Controllers\EvaluationFormPDF;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DocumentHighlightController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/evaluation-result', [EvaluationFormPDF::class, 'getEvaluationResult'])->name('queues-call-next');

    // Reports
    Route::get('preview-report', [ReportsController::class, 'preview_report'])->name('preview-report');

    // Documents
    Route::post('documents/attach-pdf', [DocumentHighlightController::class, 'attachPdf'])->name('documents.attach-pdf');
    Route::post('documents/upload-image', [DocumentHighlightController::class, 'uploadImage'])->name('documents.upload-image');
    Route::get('documents/image/{filename}', [DocumentHighlightController::class, 'getImage'])->name('documents.get-image');
    Route::delete('documents/delete-attachment', [DocumentHighlightController::class, 'deleteAttachment'])->name('documents.delete-attachment');
    Route::get('documents/{document}/highlight/{highlight}/pdf', [DocumentHighlightController::class, 'openPdf'])->name('documents.open-pdf');
});

Route::redirect('/login', '/admin/login', 301)->name('login');
