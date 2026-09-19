<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->enum('metode', ['transfer','midtrans','cash']);
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_status')->nullable();
            $table->string('bukti_transfer_path')->nullable();
            $table->bigInteger('amount');
            $table->enum('status', ['pending','paid','failed','expired'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
