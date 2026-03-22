<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reava_pay_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_key')->nullable();
            $table->string('public_key')->nullable();
            $table->text('api_secret_encrypted')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('base_url')->default('https://reavapay.com/api/v1');
            $table->string('environment')->default('production');
            $table->string('default_currency', 10)->default('KES');
            $table->boolean('mpesa_enabled')->default(true);
            $table->boolean('card_enabled')->default(true);
            $table->boolean('bank_transfer_enabled')->default(true);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reava_pay_settings');
    }
};
