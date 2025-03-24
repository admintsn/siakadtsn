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
        Schema::table('pesan_daftars', function (Blueprint $table) {
            $table->foreignId('menginap_tidak_id')->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

};
