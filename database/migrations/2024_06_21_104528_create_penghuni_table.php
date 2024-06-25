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
        Schema::create('penyewa', function (Blueprint $table) {
            $table->id();
            $table->string('nama_penyewa');
            $table->string('nomor_wa', 15);
            $table->enum('status', ['aktif', 'tidak_aktif', 'putus_kontrak'])->default('aktif');
            $table->date('tanggal_masuk');
            $table->unsignedBigInteger('id_kontrakan');
            $table->unsignedBigInteger('id_kamar');
            $table->timestamps();

            $table->foreign('id_kontrakan')->references('id')->on('kontrakan')->onDelete('cascade');
            $table->foreign('id_kamar')->references('id')->on('kamar')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyewa');
    }
};
