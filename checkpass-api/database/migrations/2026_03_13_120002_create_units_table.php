<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estate_id')->constrained()->cascadeOnDelete();
            $table->string('lane');        // e.g. 'FA'
            $table->string('house');       // e.g. 'H7'
            $table->string('flat');        // e.g. 'L3'
            $table->string('flat_address'); // e.g. 'L3, H7, FA'
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['estate_id', 'flat_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
