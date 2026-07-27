<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengajuan_izins', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel users (pegawai)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Tipe dan Tanggal
            $table->enum('tipe_pengajuan', ['sakit', 'cuti', 'izin_pribadi', 'dinas_luar']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            
            // Keterangan dan Bukti
            $table->text('alasan');
            $table->string('file_bukti')->nullable(); // nullable = boleh kosong
            
            // Status Approval Admin
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_admin')->nullable(); // Alasan admin jika menolak
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajuan_izins');
    }
};