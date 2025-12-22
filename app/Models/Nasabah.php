<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nasabah extends Model
{
    use HasFactory;

    protected $table = 'nasabah';

    protected $fillable = [
        'id_admin',
        'id_agent',
        'kode_nasabah',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'NIK',
        'NPWP',
        'alamat_KTP',
        'telepon',
        'alamat_sekarang',
        'nama_ibu',
        'agama',
        'nama_wali',
        'NIK_wali',
        'email',
        'jenis_pekerjaan',
        'bidang_usaha',
        'alamat_perusahaan',
        'penghasilan',
        'sumber_dana',
        'aktivitas_transaksi',
        'rekening_bank',
        'nomor_rekening',
        'nama_rekening',
        'tujuan_rekening',
        'tanggal_daftar',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'id_agent');
    }
}
