<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PolicyRequestController;
use App\Http\Controllers\ChatbotController;

// Premium calculation endpoint
Route::post('/calculate-premium', [
    PolicyRequestController::class, 'calculatePremium'
]);

// Policy Requests resource routes
Route::get('/policy-requests', [
    PolicyRequestController::class, 'index'
]);

Route::post('/policy-requests', [
    PolicyRequestController::class, 'store'
]);

Route::get('/policy-requests/{id}', [
    PolicyRequestController::class, 'show'
]);

Route::post('/policy-requests/{id}/submit', [
    PolicyRequestController::class, 'submit'
]);

// Simulated insurer webhook callback
Route::post('/mock-insurer/callback', [
    PolicyRequestController::class, 'mockInsurerCallback'
]);

// Interactive AI Voice & Chatbot Assistant
Route::post('/chatbot', [
    ChatbotController::class, 'message'
]);