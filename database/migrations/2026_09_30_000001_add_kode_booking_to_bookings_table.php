<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('kode_booking')->nullable()->index()->after('lapangan_id');
        });

        // Backfill kode untuk booking lama (format sama dengan yang dibuat controller)
        $bookings = DB::table('bookings')->select('id', 'tanggal')->get();
        foreach ($bookings as $b) {
            DB::table('bookings')->where('id', $b->id)->update([
                'kode_booking' => 'GR-'.\Carbon\Carbon::parse($b->tanggal)->format('Ymd').'-'.str_pad((string) $b->id, 4, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['kode_booking']);
            $table->dropColumn('kode_booking');
        });
    }
};
