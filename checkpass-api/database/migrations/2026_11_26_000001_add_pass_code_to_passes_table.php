<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passes', function (Blueprint $table) {
            if (! Schema::hasColumn('passes', 'pass_code')) {
                $table->string('pass_code', 8)->nullable()->after('qr_data');
                $table->index(['estate_id', 'pass_code'], 'passes_estate_pass_code_index');
            }
        });

        // Backfill active passes with unique 6-digit codes per estate
        $activePasses = DB::table('passes')
            ->whereNull('pass_code')
            ->whereIn('status', ['Pending', 'On-site'])
            ->get(['id', 'estate_id']);

        foreach ($activePasses as $row) {
            do {
                $code = (string) random_int(100000, 999999);
                $exists = DB::table('passes')
                    ->where('estate_id', $row->estate_id)
                    ->where('pass_code', $code)
                    ->whereIn('status', ['Pending', 'On-site'])
                    ->exists();
            } while ($exists);

            DB::table('passes')->where('id', $row->id)->update(['pass_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('passes', function (Blueprint $table) {
            if (Schema::hasColumn('passes', 'pass_code')) {
                $table->dropIndex('passes_estate_pass_code_index');
                $table->dropColumn('pass_code');
            }
        });
    }
};
