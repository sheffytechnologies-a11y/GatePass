<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The residents.role column was created as enum('primary','member'), but
     * validation, the frontend, and the admin panel have always used
     * 'owner'/'tenant'. Align the DB with the value everyone actually sends.
     */
    public function up(): void
    {
        DB::table('residents')->where('role', 'primary')->update(['role' => 'owner']);
        DB::table('residents')->where('role', 'member')->update(['role' => 'tenant']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE residents MODIFY role ENUM('owner','tenant') NOT NULL DEFAULT 'tenant'");
        }
    }

    public function down(): void
    {
        DB::table('residents')->where('role', 'owner')->update(['role' => 'primary']);
        DB::table('residents')->where('role', 'tenant')->update(['role' => 'member']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE residents MODIFY role ENUM('primary','member') NOT NULL DEFAULT 'primary'");
        }
    }
};
