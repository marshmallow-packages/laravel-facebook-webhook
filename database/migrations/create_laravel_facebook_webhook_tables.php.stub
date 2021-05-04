<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaravelFacebookWebhookTables extends Migration
{
    public function up()
    {
        Schema::create('facebook_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('access_type');
            $table->bigInteger('facebook_id');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->text('long_lived_token')->nullable();
            $table->text('token_type');
            $table->datetime('expires_at');
            $table->timestamps();
        });
        Schema::create('webhook_lead_responses', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->bigInteger('page_id');
            $table->bigInteger('adgroup_id');
            $table->bigInteger('ad_id');
            $table->bigInteger('form_id');
            $table->bigInteger('leadgen_id');
            $table->datetime('received_at');
            $table->text('payload')->nullable();
            $table->boolean('processed')->default(false);
            $table->datetime('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhook_calls');
        Schema::dropIfExists('facebook_tokens');
        Schema::dropIfExists('webhook_lead_responses');
    }
}
