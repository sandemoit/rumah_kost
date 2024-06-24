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
        Schema::table('kamar', function (Blueprint $table) {
            $table->integer('harga_kamar')->after('nama_kamar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->dropColumn('harga_kamar');
        });
    }
};
