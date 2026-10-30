<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_access_phrases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estate_id')->constrained()->cascadeOnDelete();
            $table->string('phrase_date', 10); // 'Y-m-d' in config('access.phrase_timezone')
            $table->string('phrase', 64);      // canonical form, e.g. 'AMBER LION 27'
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestamps();

            // One phrase per unit per day, and no two units share a phrase on the same day.
            $table->unique(['unit_id', 'phrase_date']);
            $table->unique(['phrase_date', 'phrase']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_access_phrases');
    }
};
