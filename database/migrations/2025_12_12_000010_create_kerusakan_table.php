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
        Schema::create('kerusakan', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('id_project');
            $table->unsignedBigInteger('id_lokasi');
            $table->unsignedBigInteger('id_fasilitas');
            $table->date('tanggal')->nullable();
            $table->date('tanggal_perbaikan')->nullable();
            $table->decimal('lat_posisi', 10, 7)->nullable();
            $table->decimal('lng_posisi', 10, 7)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('foto_kerusakan')->nullable();
            $table->string('foto_perbaikan')->nullable();
            $table->enum('status', ['pending', 'diperbaiki', 'selesai'])->default('pending');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_project')->references('id')->on('project')->onDelete('cascade');
            $table->foreign('id_lokasi')->references('id')->on('lokasi')->onDelete('cascade');
            $table->foreign('id_fasilitas')->references('id')->on('fasilitas')->onDelete('cascade');
            $table->text('deskripsi_perbaikan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kerusakan');
    }
};
