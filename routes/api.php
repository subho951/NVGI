<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RazorpayWebhookController;

Route::post('/razorpay/webhook', [RazorpayWebhookController::class, 'handle']);
