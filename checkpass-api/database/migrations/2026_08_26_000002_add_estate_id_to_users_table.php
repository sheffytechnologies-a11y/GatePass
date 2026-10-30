<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('estate_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        // Backfill from each user's resident row where one exists.
        DB::statement('
            UPDATE users
            SET estate_id = (
                SELECT estate_id FROM residents WHERE residents.user_id = users.id LIMIT 1
            )
            WHERE id IN (SELECT user_id FROM residents)
        ');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estate_id');
        });
    }
};
