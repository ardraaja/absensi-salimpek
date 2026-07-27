<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('status_masuk')->nullable(); 
            $table->string('status_pulang')->nullable(); 
            $table->string('status')->default('Tanpa Keterangan'); 
            
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};