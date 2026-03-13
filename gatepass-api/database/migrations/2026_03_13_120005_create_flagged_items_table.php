<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flagged_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pass_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->string('photo_url')->nullable(); // S3 / storage path
            $table->string('photo_path')->nullable();
            $table->timestamp('flagged_at');
            $table->timestamps();

            $table->index('pass_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flagged_items');
    }
};
