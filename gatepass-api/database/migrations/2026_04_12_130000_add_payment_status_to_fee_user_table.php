<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fee_user', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('due')->after('file_path');
            $table->timestamp('verified_at')->nullable()->after('payment_status');
            $table->foreignId('verified_by')->nullable()->after('verified_at')
                ->constrained('admins')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['payment_status', 'verified_at']);
        });
    }
};