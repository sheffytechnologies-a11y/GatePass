<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estate_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->enum('billing_cycle', ['monthly', 'yearly', 'none'])->default('none');
            $table->enum('status', ['trialing', 'active', 'past_due', 'canceled'])->default('trialing');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->unsignedInteger('extra_units')->default(0);
            $table->string('paystack_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
