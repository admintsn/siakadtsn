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
        Schema::create('status_dokumens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('santri_id')->nullable();
            $table->foreignId('jenis_dokumen_id')->nullable();
            $table->foreignId('status_lokasi_dokumen_id')->nullable();
            $table->string('tanggal')->nullable();

            $table->boolean('is_active')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_dokumens');
    }
};
