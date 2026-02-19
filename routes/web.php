<?php

use App\Http\Controllers\EvaluationFormPDF;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/admin/evaluation-result', [EvaluationFormPDF::class,'getEvaluationResult'])->name('queues-call-next');

