<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add phone to users table and remove email unique constraint
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->unique()->after('name');
            $table->boolean('is_active')->default(true)->after('remember_token');
        });

        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estate_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['primary', 'member'])->default('primary');
            $table->boolean('push_enabled')->default(false);
            $table->string('push_token')->nullable();
            $table->boolean('arrival_alerts')->default(true);
            $table->boolean('expiry_alerts')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'is_active']);
        });
    }
};
