<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lapangan_id')->constrained('lapangans')->cascadeOnDelete();
            $table->foreignId('paket_member_id')->nullable()->constrained('paket_members')->nullOnDelete();
            $table->enum('jenis_kegiatan', ['badminton','voly','basket','event_lain']);
            $table->enum('tipe_sewa', ['per_jam','harian']);
            $table->date('tanggal');
            $table->date('tanggal_selesai')->nullable();
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('durasi_jam')->default(1);
            $table->bigInteger('total_harga');
            $table->enum('status', ['pending','pending_verification','paid','confirmed','completed','cancelled','expired'])->default('pending');
            $table->text('catatan')->nullable();
            $table->boolean('is_override_buffer')->default(false);
            $table->text('override_reason')->nullable();
            $table->timestamps();

            $table->index(['tanggal','jam_mulai','jam_selesai','status']);
            $table->index(['lapangan_id','tanggal','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
