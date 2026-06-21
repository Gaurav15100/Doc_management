<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}/validation',[DocumentController::class, 'validateLinks']);
    Route::get('/documents/missing-links',[DocumentController::class, 'missingLinks']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::put('/documents/{document}', [DocumentController::class, 'update']);
    Route::post('/documents/{document}', [DocumentController::class, 'update']);
    Route::get('/documents/{document}/download',[DocumentController::class, 'download']);
    Route::post('/documents/{document}/link',[DocumentController::class, 'link']);
    Route::post('/document-types/{documentType}/requirements',[DocumentController::class, 'addRequirement']);
    Route::post('/documents/{document}/process',[DocumentController::class, 'process']);
    Route::post('/documents/{document}/queries',[DocumentController::class, 'createQuery']);
    Route::get('/documents/{document}/queries',[DocumentController::class, 'listQueries']);
    Route::post('/queries/{query}/resolve',[DocumentController::class, 'resolveQuery']);
});