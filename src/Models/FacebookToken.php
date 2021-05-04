<?php

namespace Marshmallow\LaravelFacebookWebhook\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Marshmallow\LaravelFacebookWebhook\Controllers\FacebookTokenController;

class FacebookToken extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function refresh()
    {
        $tokenController    = new FacebookTokenController();
        $tokenController->setPageAccessToken($this->long_lived_token);
    }
}
