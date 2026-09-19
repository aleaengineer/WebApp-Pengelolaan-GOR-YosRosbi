<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_sewas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lapangan_id')->constrained('lapangans')->cascadeOnDelete();
            $table->enum('tipe', ['per_jam','harian']);
            $table->enum('hari', ['weekday','weekend','semua'])->default('semua');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->bigInteger('harga');
            $table->bigInteger('harga_member')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_sewas');
    }
};
