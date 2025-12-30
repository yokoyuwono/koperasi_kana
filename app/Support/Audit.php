<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Audit
{
    /**
     * Log perubahan deposit ke tabel deposits_log.
     *
     * deposits_log:
     * id, deposit_id, action, old_data, new_data, note, created_by, created_at, updated_at
     */
    public static function deposit(
        int $depositId,
        string $action,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $note = null,
        ?int $createdBy = null
    ): void {
        DB::table('deposits_log')->insert([
            'deposit_id' => $depositId,
            'action'     => $action,
            'old_data'   => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data'   => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'note'       => $note,
            'created_by' => $createdBy ?? auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    public static function komisi(
        int $komisiId,
        string $action,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $note = null,
        ?int $createdBy = null
    ): void {
        DB::table('komisi_log')->insert([
            'komisi_id'  => $komisiId, // kalau kolomnya id_komisi, ganti di sini
            'action'     => $action,
            'old_data'   => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data'   => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'note'       => $note,
            'created_by' => $createdBy ?? auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


        public static function agent(
        int $agentId,
        string $action,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $note = null,
        ?int $createdBy = null
    ): void {
        DB::table('agents_log')->insert([
            'agent_id'   => $agentId, // kalau di tabel kamu namanya "id_agent", ganti di sini
            'action'     => $action,
            'old_data'   => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data'   => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'note'       => $note,
            'created_by' => $createdBy ?? auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function nasabah(
        int $nasabahId,
        string $action,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $note = null,
        ?int $createdBy = null
    ): void {
        DB::table('nasabah_log')->insert([
            'nasabah_id' => $nasabahId, // kalau kolomnya "id_nasabah" ganti di sini
            'action'     => $action,
            'old_data'   => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data'   => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'note'       => $note,
            'created_by' => $createdBy ?? auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    public static function promosiAgent(
        int $promosiAgentId,
        string $action,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $note = null,
        ?int $createdBy = null
    ): void {
        DB::table('promosi_agent_log')->insert([
            'promosi_agent_id' => $promosiAgentId, // sesuaikan jika nama kolom beda
            'action'           => $action,
            'old_data'         => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data'         => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'note'             => $note,
            'created_by'       => $createdBy ?? auth()->id(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}
