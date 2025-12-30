<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KomisiDefaultSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            /**
             * PENGAJU = RM
             * < 200jt
             */
            [
                'pengaju_jabatan' => 'RM',
                'nominal_min'     => 0,
                'nominal_max'     => 199999999,
                'jenis_komisi'    => 'RM',
                'annual_rate'     => 3.000,
                'ref_mode'        => null,
                'aktif'           => 1,
            ],
            [
                'pengaju_jabatan' => 'RM',
                'nominal_min'     => 0,
                'nominal_max'     => 199999999,
                'jenis_komisi'    => 'BDP',
                'annual_rate'     => 1.000,
                'ref_mode'        => null,
                'aktif'           => 1,
            ],
            [
                'pengaju_jabatan' => 'RM',
                'nominal_min'     => 0,
                'nominal_max'     => 199999999,
                'jenis_komisi'    => 'BDP_REF',
                'annual_rate'     => 0.500,
                'ref_mode'        => 'optional',
                'aktif'           => 1,
            ],

            /**
             * PENGAJU = RM
             * >= 200jt
             */
            [
                'pengaju_jabatan' => 'RM',
                'nominal_min'     => 200000000,
                'nominal_max'     => null,
                'jenis_komisi'    => 'RM',
                'annual_rate'     => 4.000,
                'ref_mode'        => null,
                'aktif'           => 1,
            ],
            [
                'pengaju_jabatan' => 'RM',
                'nominal_min'     => 200000000,
                'nominal_max'     => null,
                'jenis_komisi'    => 'BDP',
                'annual_rate'     => 1.000,
                'ref_mode'        => null,
                'aktif'           => 1,
            ],
            [
                'pengaju_jabatan' => 'RM',
                'nominal_min'     => 200000000,
                'nominal_max'     => null,
                'jenis_komisi'    => 'BDP_REF',
                'annual_rate'     => 0.500,
                'ref_mode'        => 'optional',
                'aktif'           => 1,
            ],

            /**
             * PENGAJU = BDP
             * < 200jt
             * (RM tidak ada -> sengaja tidak dibuat row jenis_komisi=RM)
             */
            [
                'pengaju_jabatan' => 'BDP',
                'nominal_min'     => 0,
                'nominal_max'     => 199999999,
                'jenis_komisi'    => 'BDP',
                'annual_rate'     => 4.000,
                'ref_mode'        => null,
                'aktif'           => 1,
            ],
            [
                'pengaju_jabatan' => 'BDP',
                'nominal_min'     => 0,
                'nominal_max'     => 199999999,
                'jenis_komisi'    => 'BDP_REF',
                'annual_rate'     => 0.500,
                'ref_mode'        => 'mandatory',
                'aktif'           => 1,
            ],

            /**
             * PENGAJU = BDP
             * >= 200jt
             */
            [
                'pengaju_jabatan' => 'BDP',
                'nominal_min'     => 200000000,
                'nominal_max'     => null,
                'jenis_komisi'    => 'BDP',
                'annual_rate'     => 5.000,
                'ref_mode'        => null,
                'aktif'           => 1,
            ],
            [
                'pengaju_jabatan' => 'BDP',
                'nominal_min'     => 200000000,
                'nominal_max'     => null,
                'jenis_komisi'    => 'BDP_REF',
                'annual_rate'     => 0.500,
                'ref_mode'        => 'mandatory',
                'aktif'           => 1,
            ],
        ];

        $now = now();

        // Tambahkan timestamp
        $rows = array_map(function ($r) use ($now) {
            $r['created_at'] = $now;
            $r['updated_at'] = $now;
            return $r;
        }, $rows);

        // Optional: bersihkan dulu biar idempotent saat seeding ulang
        DB::table('komisi_defaults')->truncate();

        DB::table('komisi_defaults')->insert($rows);
    }
}
