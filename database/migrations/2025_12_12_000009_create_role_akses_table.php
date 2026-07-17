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
        Schema::create('role_akses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_role')->nullable();
            $table->foreign('id_role')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('cascade');
            $table->unsignedBigInteger('id_akses')->nullable();
            $table->foreign('id_akses')
                  ->references('id')
                  ->on('akses')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_akses');
    }
};
