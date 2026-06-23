<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\MasterDataController;

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
    Route::get('/document-types',[MasterDataController::class, 'documentTypes']);
    Route::get('/outlets',[MasterDataController::class, 'outlets']);
    Route::get('/parties',[MasterDataController::class, 'parties']);
    Route::get('/dashboard',[MasterDataController::class, 'dashboard']);
    Route::post('/document-types',[MasterDataController::class, 'storeDocumentType']);
    Route::put('/document-types/{documentType}',[MasterDataController::class, 'updateDocumentType']);
    Route::patch('/document-types/{documentType}/toggle',[MasterDataController::class, 'toggleDocumentType']);
    Route::post('/parties',[MasterDataController::class, 'storeParty']);
    Route::put('/parties/{party}',[MasterDataController::class, 'updateParty']);
    Route::patch('/parties/{party}/toggle',[MasterDataController::class, 'toggleParty']);
    Route::post('/outlets',[MasterDataController::class, 'storeOutlet']);
    Route::put('/outlets/{outlet}',[MasterDataController::class, 'updateOutlet']);
    Route::patch('/outlets/{outlet}/toggle',[MasterDataController::class, 'toggleOutlet']);
    Route::get('/users',[MasterDataController::class, 'users']);
    Route::post(
        '/users',
        [MasterDataController::class, 'storeUser']
    );
    Route::patch(
        '/users/{user}/toggle',
        [MasterDataController::class, 'toggleUser']
    );
    Route::put(
        '/users/{user}',
        [MasterDataController::class, 'updateUser']
    );
    Route::post(
        '/users/{user}/reset-password',
        [MasterDataController::class, 'resetPassword']
    );
    Route::delete(
        '/documents/{document}',
        [DocumentController::class, 'destroy']
    );
    Route::delete(
        '/document-files/{file}',
        [DocumentController::class, 'deleteFile']
    );
    Route::post(
        '/documents/{document}/files',
        [DocumentController::class, 'addFiles']
    );
    Route::post(
        '/document-files/{file}/replace',
        [DocumentController::class, 'replaceFile']
    );
    Route::delete(
        '/document-links/{link}',
        [DocumentController::class, 'unlink']
    );
    Route::get(
        '/documents/{document}/pdf',
        [DocumentController::class, 'downloadPdf']
    );
});