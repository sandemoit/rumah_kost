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
            $table->integer('id_kamar');
            $table->integer('id_tipe');
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->integer('nominal');
            $table->string('saldo');
            $table->string('created_by');
            $table->timestamps();
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
