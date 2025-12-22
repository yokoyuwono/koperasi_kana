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
        Schema::create('promosi_agent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_admin');   // admin yang mengajukan
            $table->unsignedBigInteger('id_agent');   // RM yang diajukan naik
            $table->string('jabatan_lama', 10)->default('RM');
            $table->string('jabatan_baru', 10)->default('BDP');

            // atasan BDP saat masih RM (dipakai untuk isi refferred_by_agent_id setelah approve)
            $table->unsignedBigInteger('atasan_bdp_id')->nullable();

            $table->date('tanggal_apply')->nullable();
            $table->string('status', 20)->default('pending'); // pending|approved|rejected
            $table->text('catatan_admin')->nullable();
            $table->text('alasan_reject')->nullable();
            $table->date('tanggal_approval')->nullable();

            $table->timestamps();

            $table->foreign('id_admin')->references('id')->on('users');
            $table->foreign('id_agent')->references('id')->on('agents');
            $table->foreign('atasan_bdp_id')->references('id')->on('agents');
        });
    }

};
