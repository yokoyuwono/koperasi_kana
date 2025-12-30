<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('komisi_defaults', function (Blueprint $table) {
            $table->id();

            // Pengaju deposit: berdasarkan jabatan Agent Penanggung Jawab
            $table->enum('pengaju_jabatan', ['RM', 'BDP'])->index();

            // Range nominal deposit (pakai integer rupiah)
            $table->unsignedBigInteger('nominal_min')->default(0);
            $table->unsignedBigInteger('nominal_max')->nullable(); // NULL = tak terbatas

            // Jenis komisi
            $table->enum('jenis_komisi', ['RM', 'BDP', 'BDP_REF'])->index();

            // Annual rate (basis tenor 12), nanti prorata di controller
            $table->decimal('annual_rate', 6, 3)->nullable();

            // Mode untuk BDP Referral: optional / mandatory / none
            $table->enum('ref_mode', ['none', 'optional', 'mandatory'])->nullable();

            $table->boolean('aktif')->default(true);

            $table->timestamps();

            // Index gabungan biar query cepat
            $table->index(['pengaju_jabatan', 'jenis_komisi', 'aktif']);
            $table->index(['nominal_min', 'nominal_max']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komisi_defaults');
    }
};
