<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('estate_name');
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('city')->default('Lagos');
            $table->unsignedInteger('units')->default(50);
            $table->decimal('deal_value', 14, 2)->default(0);
            $table->string('stage', 30)->default('new'); // new, discovery, demo, proposal, pilot, won
            $table->string('source', 50)->default('AI Concierge');
            $table->longText('chat_transcript')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('estate_id')->nullable()->constrained('estates')->nullOnDelete();
            $table->timestamps();

            $table->index(['stage', 'city']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};

