<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passes', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique(); // public-facing ID (ULID)
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estate_id')->constrained()->cascadeOnDelete();

            // Visitor info
            $table->string('visitor_name', 100);
            $table->string('visitor_phone', 20)->nullable();
            $table->string('vehicle_plate', 20)->nullable();

            // Pass config
            $table->string('purpose');  // 'Personal Visit' | 'Delivery' | 'Service' | 'Business' | 'Other'
            $table->enum('type', ['One-time', 'Recurring'])->default('One-time');
            $table->json('recurring_days')->nullable(); // ['Mon','Wed','Fri']

            // Status
            $table->enum('status', ['Pending', 'On-site', 'Exited', 'Revoked', 'Expired'])->default('Pending');
            $table->boolean('items_flagged')->default(false);

            // QR & sharing
            $table->string('qr_data', 500); // UUID or signed URL stored as text
            $table->string('share_token', 64)->unique()->nullable();

            // Timestamps
            $table->timestamp('expires_at');
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('exited_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['resident_id', 'status']);
            $table->index(['estate_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passes');
    }
};
