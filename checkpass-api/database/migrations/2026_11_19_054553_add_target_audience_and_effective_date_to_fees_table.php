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
        Schema::table('fees', function (Blueprint $table) {
            if (!Schema::hasColumn('fees', 'target_audience')) {
                $table->string('target_audience')->default('both')->after('type');
            }
            if (!Schema::hasColumn('fees', 'effective_date')) {
                $table->date('effective_date')->nullable()->after('frequency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            if (Schema::hasColumn('fees', 'target_audience')) {
                $table->dropColumn('target_audience');
            }
            if (Schema::hasColumn('fees', 'effective_date')) {
                $table->dropColumn('effective_date');
            }
        });
    }
};
