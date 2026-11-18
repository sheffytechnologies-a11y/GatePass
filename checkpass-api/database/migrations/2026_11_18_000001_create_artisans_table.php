<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artisans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estate_id')->nullable()->constrained('estates')->nullOnDelete();
            $table->string('name');
            $table->string('trade');
            $table->string('category')->index();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->string('avatar_bg')->nullable();
            $table->string('initials')->nullable();
            $table->text('image_url')->nullable();
            $table->string('status')->default('Active'); // Active, Under Review, Inactive
            $table->string('address')->nullable();
            $table->string('verified_since')->nullable();
            $table->unsignedInteger('experience_years')->default(1);
            $table->boolean('gate_pass_cleared')->default(true);
            $table->boolean('police_cleared')->default(true);
            $table->boolean('id_verified')->default(true);
            $table->string('response_time')->nullable();
            $table->unsignedInteger('jobs_completed')->default(0);
            $table->json('skills')->nullable();
            $table->json('reviews')->nullable();
            $table->json('recent_gate_logs')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artisans');
    }
};
