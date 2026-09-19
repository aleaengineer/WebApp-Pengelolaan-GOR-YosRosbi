<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lapangans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->default('Lapangan Utama Yos Rosbi');
            $table->text('deskripsi')->nullable();
            $table->json('jenis')->nullable(); // ["badminton","voly","basket","event"]
            $table->string('foto')->nullable();
            $table->enum('status', ['active','inactive','maintenance'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lapangans');
    }
};
