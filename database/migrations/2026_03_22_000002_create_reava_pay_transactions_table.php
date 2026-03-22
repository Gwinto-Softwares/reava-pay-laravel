<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reava_pay_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->nullableMorphs('payer');
            $table->string('type', 30)->index();
            $table->string('channel', 30)->index();
            $table->decimal('amount', 12, 2);
            $table->decimal('charge_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->string('currency', 10)->default('KES');
            $table->string('status', 20)->default('pending')->index();
            $table->string('reava_reference')->nullable()->index();
            $table->string('provider_reference')->nullable();
            $table->string('local_reference')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('account_reference')->nullable();
            $table->text('description')->nullable();
            $table->string('authorization_url', 500)->nullable();
            $table->string('callback_url', 500)->nullable();
            $table->string('payable_type')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->json('reava_response')->nullable();
            $table->json('webhook_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->string('failure_reason')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            // nullableMorphs already creates indexes for payer
            $table->index(['payable_type', 'payable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reava_pay_transactions');
    }
};
