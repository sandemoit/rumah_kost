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
        Schema::table('kontrakan', function (Blueprint $table) {
            $table->string('code_kontrakan')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontrakan', function (Blueprint $table) {
            $table->dropColumn('code_kontrakan');
        });
    }
};
