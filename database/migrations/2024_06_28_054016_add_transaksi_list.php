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
        Schema::create('transaksi_list', function (Blueprint $table) {
            $table->id();
            $table->integer('code_transaksi');
            $table->integer('code_kontrakan');
            $table->json('id_kamar');
            $table->integer('id_tipe');
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->integer('nominal');
            $table->string('saldo');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_list');
    }
};
