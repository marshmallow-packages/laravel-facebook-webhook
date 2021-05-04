<?php

use Illuminate\Support\Facades\Route;
use Marshmallow\LaravelFacebookWebhook\Controllers\FacebookTokenController;

Route::webhooks('webhook/facebook/lead', 'facebook-lead');
Route::get(
    'webhook/facebook/lead',
    [FacebookTokenController::class, 'verifyWebhookInstall']
);
Route::get(
    'auth/facebook',
    [FacebookTokenController::class, 'handleProviderCallback']
);
