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
        Schema::create('promosi_agent_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_promosi_agent');
            $table->unsignedBigInteger('id_admin'); // user yang melakukan perubahan (admin/coa)
            $table->string('aksi', 50); // created|updated|approved|rejected
            $table->text('perubahan_data')->nullable();
            $table->timestamp('waktu_perubahan')->useCurrent();
            $table->timestamps();

            $table->foreign('id_promosi_agent')->references('id')->on('promosi_agent');
            $table->foreign('id_admin')->references('id')->on('users');
        });
    }

};
