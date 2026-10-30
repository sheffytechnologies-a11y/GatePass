<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estate_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['Security Incident', 'Fire', 'Medical', 'Intruder']);
            $table->text('notes')->nullable();
            $table->enum('status', ['sent', 'acknowledged', 'resolved'])->default('sent');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['estate_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergencies');
    }
};
