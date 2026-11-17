<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix SQLite residents table CHECK constraint on 'role'.
     * The original migration defined enum('primary', 'member'), and a later migration
     * updated MySQL enum, but skipped SQLite.
     * This migration updates SQLite table definition to allow 'owner', 'tenant', 'primary', 'member'
     * or without a restrictive check constraint.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');

            DB::statement('
                CREATE TABLE "residents_new" (
                    "id" integer primary key autoincrement not null,
                    "user_id" integer not null,
                    "unit_id" integer not null,
                    "estate_id" integer not null,
                    "role" varchar not null default \'tenant\',
                    "push_enabled" tinyint(1) not null default \'0\',
                    "push_token" varchar,
                    "arrival_alerts" tinyint(1) not null default \'1\',
                    "expiry_alerts" tinyint(1) not null default \'1\',
                    "is_active" tinyint(1) not null default \'1\',
                    "created_at" datetime,
                    "updated_at" datetime,
                    foreign key("user_id") references "users"("id") on delete cascade,
                    foreign key("unit_id") references "units"("id") on delete cascade,
                    foreign key("estate_id") references "estates"("id") on delete cascade
                );
            ');

            DB::statement('INSERT INTO residents_new SELECT * FROM residents;');
            DB::statement('DROP TABLE residents;');
            DB::statement('ALTER TABLE residents_new RENAME TO residents;');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS residents_user_id_unit_id_unique ON residents (user_id, unit_id);');

            DB::statement('PRAGMA foreign_keys=ON;');
        }
    }

    public function down(): void
    {
        // Reversible if needed
    }
};
