<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estates', function (Blueprint $table) {
            $table->json('property_structure')->nullable()->after('account_name');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->string('unit_type', 50)->default('Apartment')->after('is_active');
            $table->json('hierarchy_data')->nullable()->after('unit_type');
            $table->text('notes')->nullable()->after('hierarchy_data');
            $table->string('lane')->nullable()->change();
            $table->string('house')->nullable()->change();
            $table->string('flat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('estates', function (Blueprint $table) {
            $table->dropColumn('property_structure');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['unit_type', 'hierarchy_data', 'notes']);
            $table->string('lane')->nullable(false)->change();
            $table->string('house')->nullable(false)->change();
            $table->string('flat')->nullable(false)->change();
        });
    }
};
